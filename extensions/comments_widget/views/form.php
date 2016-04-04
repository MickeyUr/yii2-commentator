<?php
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>

<?php $form = ActiveForm::begin([
    'action' => $model->isNewRecord ? Url::toRoute(['/comments/handler/create']) : Url::toRoute(['/comments/handler/update']),
    'method' => 'post',
    'id' => $model->isNewRecord ? 'comment-form' : 'comment-form-'. $model->id,
//    'enableAjaxValidation' => true,    //TODO enableAjaxValidation отправляет 2 запроса
//    'clientOptions' => [ //TODO client options yii2 ?
//        'validateOnSubmit' => true,
//        'validateOnChange' => false,
//        'errorCssClass' => 'has-error',
//        'successCssClass' => 'has-success',
//    ],
]); ?>

<div class="row">
    <?php if ( !$user = \Yii::$app->getModule('comments')->loadUser() ) : ?>

        <div data-role="input-container" class="form-group col-md-6">
            <div class="input-group">
                <span class="input-group-addon">Имя:</span>
                <?= $form->field($model, 'author')->textInput(['class'=>'form-control', 'placeholder' => 'Введите ваше имя']) ?>
            </div>
<!--            --><?php //echo $form->error($model, 'author', array('class' => 'text-danger')); ?>
        </div>

        <div data-role="input-container" class="form-group col-md-6">
            <div class="input-group">
                <span class="input-group-addon">E-mail:</span>
                <?= $form->field($model, 'email')->textInput(['class'=>'form-control', 'placeholder' => 'Введите ваш e-mail']) ?>
            </div>
<!--            --><?php //echo $form->error($model, 'email', array('class' => 'text-danger')); ?>
        </div>

    <?php else : ?>
        <?php $model->setScenario('authorized'); ?>
        <div class="col-md-6">
            <span class="username">
                <i class="fa fa-user"></i> <?php echo $user->{\Yii::$app->getModule('comments')->usernameField}; ?>
            </span>
        </div>
    <?php endif; ?>

    <div data-role="input-container" class="form-group col-md-12">
        <div class="input-group">
            <span class="input-group-addon">Комментарий:</span>
            <?= $form->field($model, 'content')->textArea(['class'=>'form-control', 'placeholder' => 'Напишите комментарий', 'rows' => '3']) ?>
        </div>
<!--        --><?php //echo $form->error($model, 'content', array('class' => 'text-danger')); ?>
    </div>

    <div class="form-group col-md-12">
        <div class="btn-group">
            <button data-role="reply" data-is-new="<?php echo $model->isNewRecord ? 'true' : 'false' ?>" class="btn btn-success"><i class="fa fa-reply"></i> Отправить комментарий</button>
            <?php if ( !empty($cancelButton) ) : ?>
                <button data-role="cancel" class="btn btn-danger"><i class="fa fa-times"></i> Отмена</button>
            <?php endif; ?>
        </div>
        <label class="checkbox-inline">
	        <?php $model->notify = $model->isNewRecord ? 1 : $model->notify ?>
            <?= $form->field($model, 'notify')->checkbox()?> <!--Уведомлять меня о новых комментариях-->
        </label>
    </div>
</div>

<?php if ( !$model->isNewRecord ) : ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<?php endif; ?>

<?php if ( !empty($parent_id) ) : ?>
    <?= $form->field($model, 'parent_id')->hiddenInput(['value' => $parent_id])->label(false) ?>
<?php endif; ?>

<?= $form->field($model, 'url')->hiddenInput(['value' => $url])->label(false) ?>

<?php ActiveForm::end(); ?>