<?php
use mickey\commentator\helpers\CHelper as CHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
?>

<div class="comments admin-comments">
<h1><i class="fa fa-search"></i> Просмотр комментария #<?php echo $model->id; ?></h1>
<!--    --><?php //echo \Yii::$app->getBaseUrl(true); ?>
<!--    --><?php //echo Url::base(); ?>
<!--    --><?php //echo Yii::$app->homeUrl; ?>
<!--    --><?php //echo Yii::$app->getUrlManager()->getBaseUrl(); ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'parent_id',
            'user_id',
            [
                'label' => $model->getAttributeLabel('url'),
                'value'=>Html::a($model->getAbsoluteUrl(), $model->getAbsoluteUrl()),
            ],
            [
                'label' => $model->getAttributeLabel('author'),
                'value'=>$model->getAuthor(),
            ],
//            'author',
            'email:email',
            'content:ntext',
            'ip',
            [
                'label'=>$model->getAttributeLabel('likes'),
                'value'=>$model->getLikes(),
            ],
            [
                'label'=>$model->getAttributeLabel('status'),
                'value'=> $model->getStatus(),
            ],
            [
                'label'=>$model->getAttributeLabel('notify'),
                'value'=> $model->getNotifyStatus(),
            ],
            [
                'label'=>$model->getAttributeLabel('created'),
                'value'=> Yii::$app->formatter->asDatetime($model->created),
            ],
            [
                'label'=>$model->getAttributeLabel('updated'),
                'value'=> Yii::$app->formatter->asDatetime($model->updated),
            ],
        ],
    ]) ?>


<p class="control">
    <?= Html::a("<i class='fa fa-list'></i> Менеджер комментариев", 'index', $options = [] )?>
    |
<!--    --><?php //echo \CHtml::link('<i class="fa fa-pencil"></i> Редактировать комментарий', array('update', 'id' => $model->id)); ?>
    <?= Html::a("<i class='fa fa-pencil'></i> Редактировать комментарий", Url::toRoute(['update', 'id' => $model->id]), $options = [] )?>
    |
    <?= Html::a("<i class='fa fa-cog'></i> Настройки", 'settings', $options = [] )?>
</p>
</div>