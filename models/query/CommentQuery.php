<?php

namespace mickey\commentator\models\query;
use mickey\commentator\models\Comment;

/**
 * This is the ActiveQuery class for [[\common\models\Comment]].
 *
 * @see \common\models\Comment
 */
class CommentQuery extends \yii\db\ActiveQuery
{
    public function approved()
    {
        $this->andWhere(['status'=>Comment::STATUS_APPROVED]);
        return $this;
    }

    public function rejected()
    {
        $this->andWhere(['status'=>Comment::STATUS_REJECTED]);
        return $this;
    }

    public function notify()
    {
        $this->andWhere(['notify'=>Comment::NOTIFY]);
        $this->groupBy(['email']);
        return $this;
    }

    public function pending()
    {
        $this->andWhere(['status'=>Comment::STATUS_PENDING]);
        return $this;
    }

    /**
     * @inheritdoc
     * @return \common\models\Comment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * Условие для поиска комментариев на определённой странице
     * @param string $url по-умолчанию текущая страница
     * @return $this
     */
    public function page($url='')
    {
        if ( empty($url) )
            $url = \Yii::$app->getRequest()->getUrl();//   Yii::$app->request->requestUri;

        $this->andWhere(['url' => $url]);
        $this->orderBy('created DESC'); // добавил Desc

        return $this;
    }

    /**
     * @inheritdoc
     * @return \common\models\Comment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}