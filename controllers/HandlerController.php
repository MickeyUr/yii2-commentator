<?php
namespace mickey\commentator\controllers;
use Yii;
use yii\web\Controller;
use yii\web\Session;
use yii\widgets\ActiveForm;
use mickey\commentator\models\Comment as Comment;
use mickey\commentator\helpers\CHelper as CHelper;
use mickey\commentator\extensions\comments_widget\CommentsWidget as CommentsWidget;
use yii\web\Response;

class HandlerController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'ajaxOnly - Unsubscribe',
        );
    }

    /**
     * Создаёт новый комментарий
     * @return bool
     */
    public function actionCreate()
    {
        $model = new Comment(['scenario' => 'guest']);
        if ( $user = \Yii::$app->getModule('comments')->loadUser() )
        {
//            echo 0;
            $model->scenario='authorized';
            $model->user_id = $user->{$user->tableSchema->primaryKey[0]};
        }
//echo 1;
        $this->performAjaxValidation($model);

        if ( !isset($_POST['Comment']) )
        return false;

        $model->attributes = $_POST['Comment'];
        $model->ip = CHelper::getRealIP();
//        dump($model->ip);
        $model->setStatus();

        if ( !$model->save() )
//            return false;
            return($model->getErrors());
//        echo 1;

        \Yii::$app->session->set("commentHash_{$model->id}",$model->getHash());

        $widget = new CommentsWidget();
        $widget->models = Comment::find()->page($model->url)->approved()->all();
        $widget->init();

        if (\Yii::$app->getModule('comments')->notifyAdmin)
            $this->sendAdminNotify($model);

        $this->sendUserNotifies($model);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return (array(
            'id' => $model->id,
            'premoderate' => \Yii::$app->getModule('comments')->getPremoderateStatus(),
            'tree' => $widget->getTree(),
            'count' => count($widget->models),
            'modal' => $this->getModal(array(
                'title' => '<i class="fa fa-comments"></i> Комментарий успешно отправлен!',
                'content' => '<strong>Спасибо за комментарий!</strong> Он появится после проверки модератором.'
            )),
        ));
    }

    /**
     * Обновление комментария
     * @return bool
     */
    public function actionUpdate()
    {
        $model = Comment::find()->where(['id'=>$_POST['Comment']['id']])->one();

        if ( !$model->canUpdated() )
            return false;

        $model->scenario='guest';

        if ( $user = \Yii::$app->getModule('comments')->loadUser() )
            $model->scenario='authorized';

        $model->attributes = $_POST['Comment'];
        $this->performAjaxValidation($model);

        if ( !$model->save() )
            return false;

        $widget = new CommentsWidget();
        $widget->models = Comment::find()->page($model->url)->approved()->all();
        $widget->init();

        return json_encode(array(
            'id' => $model->id,
            'tree' => $widget->getTree(),
        ));
    }

    /**
     * Удаляет комментарий
     * @return bool
     */
    public function actionDelete()
    {
        $model = Comment::find()->where(['id'=>$_POST['id']])->one();
        $url = $model->url;

        if ( !$model->canDeleted() )
            return false;

        if ( $model->delete() )
        {
            \Yii::$app->session->set("commentHash_{$model->id}",null);
            $widget = new CommentsWidget();
            $widget->models = Comment::find()->page($url)->approved()->all();
            $widget->init();

            return json_encode(array(
                'tree' => $widget->getTree(),
                'count' => count($widget->models),
                'modal' => $this->getModal(array(
                        'title' => '<i class="fa fa-comments"></i> Комментарий успешно удалён!',
                        'content' => 'Вместо удалённого комментария вы можете написать новый.'
                    )),
            ));
        }
    }

    /**
     * Создаёт форму ответа на комментарий
     * @return bool
     */
    public function actionReplyForm()
    {
        $model = new Comment(['scenario' => 'guest']);

        return $this->renderPartial('../../extensions/comments_widget/views/form', array(
            'model' => $model,
            'parent_id' => (int) $_POST['id'],
            'cancelButton' => true,
            'url' => $_POST['url'],
        ), false, true);
    }

    /**
     * Создаёт форму редактирования комментария
     * @return bool
     */
    public function actionUpdateForm()
    {
        $model = Comment::find()->where(['id'=>$_POST['id']])->one();
        $model->scenario='guest';

        return $this->renderPartial('../../extensions/comments_widget/views/form', array(
            'model' => $model,
            'cancelButton' => true,
            'url' => $_POST['url'],
        ), false, true);
    }

    /**
     * Выставляет лайки комментариям
     * @return bool
     */
    public function actionLikes()
    {
//        echo 1;
//        exit();
        $model = Comment::find()->where(['id'=>$_POST['id']])->one();
//        dump($model->likes);
        $model->setLike($_POST['like']);


        if ( !$model->canLiked() )
            return;
//        dump($model->likes);
        if ( $model->save() )
        {
//            $model->setLikesToSession();

            return json_encode(array(
                'likes' => $model->getLikes()
            ));
        }
    }

    /**
     * Отписка от рассылки комментариев
     * @param $hash
     * @param string $url
     */
    public function actionUnsubscribe($hash, $url='')
    {
        $subscriber = Comment::findByHashUrl($hash, $url);
        $comments = empty($url)
            ? Comment::find()->where(['email' => $subscriber->email])->all()
            : Comment::find()->page($url)->where(['email' => $subscriber->email])->all();


        foreach ($comments as $comment)
        {
            $comment->notify = Comment::NOT_NOTIFY;
            $comment->save();
        }

        return $this->renderPartial('unsubscribe');
    }

    /**
     * Валидация модели по ajax-запросу
     * @param $model
     */
    protected function performAjaxValidation($model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
//            return json_encode(ActiveForm::validate($model));
        }
    }

    /**
     * Возвращает модальное окно, принимает массив $options
     * с двумя ключами - 'title' и 'content'
     * @param $options array
     * @return string
     */
    protected function getModal($options)
    {
        return $this->renderPartial('../../extensions/comments_widget/views/modal', [
            'title' => $options['title'],
            'content' => $options['content'],
        ], true);
    }

    /**
     * Отправляет уведомление админу о новых комментариях
     * @param $newComment
     */
	protected function sendAdminNotify($newComment)
    {
	    // Если email комментария совпадает с email'ом админа, то сообщение не отправляем,
	    // потому что это сообщение написал админ.
	    if ($newComment->getEmail() === $this->module->adminEmail) {
		    return;
	    }

        $message = $this->renderPartial('../../extensions/comments_widget/views/email/notifyAdmin', ['newComment' => $newComment], true);

        $this->module->sendMail($this->module->adminEmail, 'Новый комментарий на сайте "' . \Yii::$app->name . '"', $message);
    }

    /**
     * Отправляет пачками письма пользователям о новых комментариях
     * @param $newComment
     */
	protected function sendUserNotifies($newComment)
    {
        foreach (Comment::find()->page($newComment->url)->notify()->all() as $subscriber)
        {
            // Если email нового комментария (отправителя) совпадает с email подписчика,
            // то выходит что это один и тот же человек, ему уведомление не высылаем, пропускаем итерацию цикла
            if ($newComment->getEmail() === $subscriber->getEmail())
                continue;

            $message = $this->renderPartial('../../extensions/comments_widget/views/email/notifyUser', [
                'newComment' => $newComment,
                'userName' => $subscriber->getAuthor(),
                'userEmail' => $subscriber->getEmail(),
                'hash' => $subscriber->getHash(),
            ], true);

            $this->module->sendMail($subscriber->getEmail(), 'Новый комментарий на сайте "' . \Yii::$app->name . '"', $message);
        }
    }
}