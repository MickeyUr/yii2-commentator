<?php
namespace mickey\commentator\models;
use mickey\commentator\models\NewComments as NewComments;
use mickey\commentator\models\query\CommentQuery;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $user_id
 * @property string $url
 * @property string $author
 * @property string $email
 * @property string $content
 * @property string $ip
 * @property integer $likes
 * @property integer $status
 * @property integer $notify
 * @property integer $created
 * @property integer $updated
 */
class Comment extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0; // статус "на рассмотрении"
    const STATUS_APPROVED = 1; // статус "подтверждён"
    const STATUS_REJECTED = 2; // статус "отклонён"

    const NOT_NOTIFY = 0; // не уведомлять о новых комментах
    const NOTIFY = 1; // уведомлять о новых комментах

    /**
     * @var int предыдущее количество лайков
     */
    private $oldLikes;

    /**
     * @var boolean поставили модели лайк - true, минус - false
     */
    private $isLiked;

    /**
     * @var string своё значение поля created
     */
    public $custom_created;

	/**
	 * @return string the associated database table name
	 */
    public static function tableName()
    {
        return '{{%comment}}';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        return array(
            // Сценарий "гость"
            [['author', 'email', 'content'], 'required', 'on' => 'guest', 'message' => 'Это поле должно быть заполнено'],
            // Сценарий "авторизованный пользователь"
            [['content'], 'required', 'on' => 'authorized', 'message' => 'Это поле должно быть заполнено'],
            // Сценарий "update"
            [['custom_created'], 'string', 'length' => [4, 128], 'on' => 'update'],
            // Всё остальное
            [['url', 'ip', 'content'], 'required'],
            [['parent_id', 'user_id', 'likes', 'status', 'notify', 'created', 'updated'], 'integer'],
            [['email'], 'string',  'length' => [4, 128]],
            [['email'], 'email', 'message' => 'Введите корректный e-mail адрес'],
            [['author'], 'string', 'length'=>['max' => \Yii::$app->getModule('comments')->maxLengthAuthor]],
            [['content'], 'string', 'length'=>['max' => \Yii::$app->getModule('comments')->maxLengthContent]],
            [['ip'], 'string', 'length' => [4, 15]],
//            array('id, parent_id, user_id, url, author, email, content, ip, likes, status, notify, created, updated', 'safe', 'on'=>'search'),
        );
	}

    /**
     * @return array связи
     */
    public function getUser()
    {
        if (!$userModelClass = \Yii::$app->getModule('comments')->userModelClass)
            return array();

        return $this->hasOne($userModelClass::className(), ['id' => 'user_id']);
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id' => 'Родитель',
			'user_id' => 'Пользователь',
			'url' => 'URL',
			'author' => 'Автор',
			'email' => 'Email',
			'content' => 'Комментарий',
			'ip' => 'IP',
			'likes' => 'Лайки',
			'status' => 'Статус',
			'notify' => 'Уведомлять автора о новых комментариях?',
			'created' => 'Создан',
			'updated' => 'Обновлён',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new \CDbCriteria;

        $criteria->join = 'LEFT JOIN new_comments ON new_comments.comment_id = t.id AND new_comments.user_id = "' . \Yii::$app->getModule('comments')->getUserID() . '"';
		$criteria->distinct = true;
		$criteria->compare('id',$this->id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('author',$this->author,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('likes',$this->likes);
		$criteria->compare('status',$this->status);
		$criteria->compare('notify',$this->notify);
		$criteria->compare('created',$this->created);
		$criteria->compare('updated',$this->updated);

		return new \CActiveDataProvider($this, array(
			'criteria' => $criteria,
            'sort' => array(
                'defaultOrder' => 'new_comments.user_id DESC, IF (status = ' . self::STATUS_PENDING . ', status, "") DESC, t.id DESC'
            ),
            'pagination' => array(
                'pageSize' => \Yii::$app->getModule('comments')->managePageSize,
            ),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Comment the static model class
	 */
	public static function model($className=__CLASS__)
	{
        return parent::className();
	}

    /**
     * Поведение перед сохранением
     * @return boolean
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->isNewRecord)
                $this->created = time();
            else
                $this->updated = time();

            if ( !empty($this->custom_created) )
                $this->created = strtotime($this->custom_created);

            $this->content = trim( strip_tags($this->content) );
            $this->author = trim( strip_tags($this->author) );
            $this->url = strip_tags($this->url);

            return true;
        }
        return false;
    }

    /**
     * Поведение после сохранения
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
//        Yii::$app->end;
//        parent::afterSave($insert, $changedAttributes);
//
//        if ( !$this->isNewRecord )
//            return true;
//
        if (!$userModelClass = \Yii::$app->getModule('comments')->userModelClass)
            return true;

        $userModel = new $userModelClass();
        $userPK = $userModel->tableSchema->primaryKey;
        $userID = \Yii::$app->getModule('comments')->getUserID();

//        $criteria = new \CDbCriteria(); //TODO разобраться что за херня

        if (!empty($userID) )
            $query = $userModel::find(['not in ','id',array($userID)]);
        else $query = $userModel::find();

        foreach ($query->all() as $user)
        {
            $newComments = new NewComments();
            $newComments->user_id = $user->$userPK[0];
            $newComments->comment_id = $this->id;

            if ( !$newComments->save() )
                return false;
        }

        return true;
    }

    /**
     * Поведение после удаления
     * @return boolean
     */
    public function afterDelete()
    {
        parent::afterDelete();

        if ( !NewComments::deleteAll(['comment_id'=>$this->id]))
            return false;

        return true;
    }

    /**
     * Метка последнего изменения (создания или обновления)
     * @return int unix timestamp
     */
    public function getLastModified()
    {
        return !empty($this->updated) ? $this->updated : $this->created;
    }

    /**
     * Может ли быть отредактирован этот комментарий?
     * @return bool
     */
    public function canUpdated()
    {
        // Если перед нами супер пользователь, то сразу возвращаем true
        if ( \Yii::$app->getModule('comments')->isSuperuser() )
            return true;

        // Если разрешённое время ещё не прошло, хэш из сессии совпадает с хэшем модели, то мы разрешаем удаление
        if ( $this->isAllowedToModifyTime() && (\Yii::$app->session["commentHash_{$this->id}"] === $this->getHash()))
            return true;
        return false;
    }

    /**
     * Может ли быть удалён этот комментарий?
     * @return bool
     */
    public function canDeleted()
    {
        // Если перед нами супер пользователь, и у модели нет детей то сразу возвращаем true
        if ( \Yii::$app->getModule('comments')->isSuperuser() && !$this->hasChilds() )
            return true;

        // Если разрешённое время ещё не прошло, хэш из сессии совпадает с хэшем модели, и комментарий не имеет детей
        // то мы разрешаем удаление
        if ( $this->isAllowedToModifyTime() && (\Yii::$app->session["commentHash_{$this->id}"] === $this->getHash())
            && !$this->hasChilds() )
            return true;
        return false;
    }

    /**
     * Используется в методах canDeleted() и canUpdate() для определения времени, разрешенного для редактирования
     * @return bool допускается ли обновлять/удалять запись с учётом разрешенного времени, прошедшего с момента её создания
     */
    private function isAllowedToModifyTime()
    {
        $editTime = \Yii::$app->getModule('comments')->editTime;
        $diff = time() - $this->created;
        return $diff < $editTime ? true : false;
    }

    /**
     * @return string хэш комментария
     */
    public function getHash()
    {
        return md5("{$this->id} this is secret string! =)");
    }

    /**
     * Выставляет like по атрибуту $like (true|false),
     * а также выставляет значения для других атрибутов
     * @param $like boolean true +1, false -1
     */
    public function setLike($like)
    {
        $this->oldLikes = $this->likes;
        $this->isLiked = $like;
        $this->likes = $like ? $this->likes + 1 : $this->likes - 1;
    }

    /**
     * Возвращает лайки с плюсом или с минусом
     * @return int|string
     */
    public function getLikes()
    {
        return $this->likes > 0 ? "+{$this->likes}" : $this->likes;
    }

    /**
     * Может ли поставить лайк комментарию?
     * @return bool
     */
    public function canLiked()
    {
        // Есле перед нами супер пользователь, то сразу возвращаем true
        if ( \Yii::$app->getModule('comments')->isSuperuser() && \Yii::$app->getModule('comments')->likesControl )
            return true;

        $commentsLikes = self::getCommentsLikesFromSession();
        // Если нет в сессии данных о голосе за этот комментарий,
        // значит за него пользователь не голосовал, поэтому разрешаем голосование
        if ( !isset($commentsLikes[$this->id]) )
            return true;

        $defaultLikes = $commentsLikes[$this->id]['defaultLikes'];

        // Если проголосовали "вверх"
        if ( $commentsLikes[$this->id]['like'] )
            return ($this->likes < ($defaultLikes + 2)) ? true : false;
        else
            return ($this->likes > ($defaultLikes - 2)) ? true : false;
    }

    /**
     * Кладёт массив лайков в сессию
     */
    public function setLikesToSession()
    {
        $commentsLikes = Comment::getCommentsLikesFromSession();

        if ( isset($commentsLikes[$this->id]) )
            $commentsLikes[$this->id] = array(
                'defaultLikes' => $commentsLikes[$this->id]['defaultLikes'],
                'like' => $this->isLiked,
            );
        else
            $commentsLikes[$this->id] = array(
                'defaultLikes' => $this->oldLikes,
                'like' => $this->isLiked,
            );

        // Кладём в сессию массив id моделей, для которой провелось голосование
        \Yii::$app->session['commentsLikes'] = $commentsLikes;
    }

    /**
     * @return array массив лайков из сессии
     */
    public static function getCommentsLikesFromSession()
    {
        return \Yii::$app->session['commentsLikes'] ? \Yii::$app->session['commentsLikes'] : array();
    }

    /**
     * Ищет экземпляр модели по хэшу и урлу. Если урл не указывать, то урл не будет учитываться в поиске
     * @param $hash string хэш меодели
     * @param $url string урл модели
     * @return object|boolean
     */
    public static function findByHashUrl($hash, $url='')
    {
        $models = empty($url) ? self::find()->all() : self::find()->page($url)->all();
        foreach ($models as $model)
            if ($model->getHash() === $hash)
                return $model;
        return false;
    }

    /**
     * Проверяет, есть ли у модели дети
     * @return boolean
     */
    public function hasChilds()
    {
        return self::find()->where(['parent_id' => $this->id])->all() ? true : false;
    }

    /**
     * Cтатус
     * @param bool $translate переводить или нет
     * @return string
     */
    public function getStatus($translate=true)
    {
        switch ($this->status)
        {
            case self::STATUS_PENDING :
                return $translate ? \Yii::t('mickeyur\commentator\Module.main', 'pending') : 'pending';
            case self::STATUS_APPROVED :
                return $translate ? \Yii::t('mickeyur\commentator\Module.main', 'approved') : 'approved';
            case self::STATUS_REJECTED :
                return $translate ? \Yii::t('mickeyur\commentator\Module.main', 'rejected') : 'rejected';
        }
    }

    /**
     * @return array массив статусов
     */
    public static function getStatusArray()
    {
        return array(
            self::STATUS_PENDING => \Yii::t('mickeyur\commentator\Module.main', 'pending'),
            self::STATUS_APPROVED => \Yii::t('mickeyur\commentator\Module.main', 'approved'),
            self::STATUS_REJECTED => \Yii::t('mickeyur\commentator\Module.main', 'rejected'),
        );
    }

    /**
     * @return array массив статусов "уведомлять пользователя" или "нет"
     */
    public static function getNotifyStatusArray()
    {
        return array(
            self::NOTIFY => \Yii::t('mickeyur\commentator\Module.main', 'yes'),
            self::NOT_NOTIFY => \Yii::t('mickeyur\commentator\Module.main', 'no'),
        );
    }

    /**
     * @return string абсолютный адрес страницы комментария
     */
    public function getAbsolutePageUrl()
    {
//        return \Yii::app()->getBaseUrl(true) . $this->url;
        return \Yii::$app->homeUrl . $this->url;
    }

    /**
     * @return string абсолютный адрес комментария
     */
    public function getAbsoluteUrl()
    {
        return $this->getAbsolutePageUrl() . "#comment_{$this->id}";
    }

    /**
     * @return string css класс для строки таблицы
     */
    public function getRowCssClass()
    {
        $cssClasses = $this->isNew() ? ' new' : '';
        $cssClasses .= " {$this->getStatus(false)}";
        return $cssClasses;
    }

    /**
     * @return string уведомлять автора о новых комментариях (да|нет)
     */
    public function getNotifyStatus()
    {
        return $this->notify ? \Yii::t('mickeyur\commentator\Module.main', 'yes') : \Yii::t('mickeyur\commentator\Module.main', 'no');
    }

    /**
     * Установка статус комментария. Если не передать статус, то поставится статус исходя из настроек модуля
     * @param int $status
     */
    public function setStatus($status=false)
    {
        if ($status)
            $this->status = $status;

        $this->status = !\Yii::$app->getModule('comments')->getPremoderateStatus()
            ? Comment::STATUS_APPROVED : Comment::STATUS_PENDING;
    }

    /**
     * @return string имя автора (зарегистрированного или анонимного пользователя)
     */
    public function getAuthor()
    {
        if ( !empty($this->author) )
            return $this->author;

        return $this->user->{\Yii::$app->getModule('comments')->usernameField};
    }

    /**
     * @return string email автора (зарегистрированного или анонимного пользователя)
     */
    public function getEmail()
    {
        if ( !empty($this->email) )
            return $this->email;

        return $this->user->{\Yii::$app->getModule('comments')->userEmailField};
    }

    public function isNew()
    {
        $userID = \Yii::$app->getModule('comments')->getUserID();
        if ( empty($userID) )
            return false;

        return NewComments::find()->user($userID)->where(['comment_id'=>$this->id]) ? true : false;
    }

    /**
     * Загружает название страницы для текущего комментария. Сначала идёт попытка достать название из сессии,
     * если его там нет, то оно загружается через file_get_contents. Если на странице не найден тег title,
     * то вернётся урл страницы
     * @return mixed
     */
    public function loadPageTitle()
    {
        $pageTitles = !empty(\Yii::$app->session->get("pageTitles")) ? \Yii::$app->session->get("pageTitles") : array();

        if ( !empty($pageTitles[$this->url]) )
            return $pageTitles[$this->url];

        $absoluteUrl = \Yii::$app->homeUrl . $this->url;
//        $page = file_get_contents($absoluteUrl);
        $page='';
        preg_match('~<title[^>]*>(.*?)<\/title>~i', $page, $matches);

        $pageTitles[$this->url] = !empty($matches[1]) ? $matches[1] : $absoluteUrl;
        \Yii::$app->session->set("pageTitles",$pageTitles);

        return $pageTitles[$this->url];
    }

    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
