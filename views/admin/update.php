<?php
use mickey\commentator\helpers\CHelper as CHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="comments admin-comments">
<h1><i class="fa fa-pencil"></i> Редактирование комментария #<?= $model->id; ?></h1>

    <?php $form = ActiveForm::begin(['id' => 'comment-form']); ?>

<p class="note">Поля, помеченные <span class="required">*</span> обязательны для заполнения</p>

    <?php echo $form->errorSummary($model, null, null, array('class'=>'alert alert-danger')); ?>

<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            <?= $form->field($model, 'url')->textInput() ?>
<!--            --><?php //echo $form->labelEx($model,'url'); ?>
<!--            --><?php //echo $form->textField($model,'url',array('class'=>'form-control')); ?>
<!--            --><?php //echo $form->error($model,'url',array('class'=>'text-danger')); ?>
        </div>

        <div class="form-group">
            <?php echo $model->getAttributeLabel('author'); ?>
            <?php echo !empty($model->user) ? '<small>' . $model->user->{\Yii::$app->getModule('comments')->usernameField} . '</small>' : $form->field($model, 'author')->textInput()?>
<!--            --><?//=  ?>
<!--            --><?php //echo $form->error($model,'author',array('class'=>'text-danger')); ?>
        </div>

        <div class="form-group">
<!--            --><?php //echo $form->labelEx($model,'email'); ?>
<!--            --><?php //echo $form->textField($model,'email',array('class'=>'form-control')); ?>
<!--            --><?php //echo $form->error($model,'email',array('class'=>'text-danger')); ?>
            <?= $form->field($model, 'email')->textInput() ?>
        </div>

        <div class="form-group">
<!--            --><?php //echo $form->labelEx($model,'created'); ?>
<!--            --><?php //$this->widget('mickey\yii_commentator\extensions\timepicker\Timepicker', array(
//                'model' => $model,
//                'name' => 'custom_created',
//                'options' => array(
//                    'stepSecond' => 1,
//                ),
//            )); ?>
<!--            --><?php //echo $form->error($model,'created',array('class'=>'text-danger')); ?>
        </div>
    </div>
    <div class="col-md-6">

        <div class="form-group">
            <?= $form->field($model, 'status')->dropDownList( $model->getStatusArray());?>
<!--            --><?php //echo $form->labelEx($model,'status'); ?>
<!--            --><?php //echo $form->dropDownList($model, 'status', $model->getStatusArray(), array('class'=>'form-control')); ?>
<!--            --><?php //echo $form->error($model,'status',array('class'=>'text-danger')); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'notify')->dropDownList( $model->getNotifyStatusArray());?>
<!--            --><?php //echo $form->labelEx($model,'notify'); ?>
<!--            --><?php //echo $form->dropDownList($model, 'notify', $model->getNotifyStatusArray(), array('class'=>'form-control')); ?>
<!--            --><?php //echo $form->error($model,'notify',array('class'=>'text-danger')); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'likes')->textInput() ?>
<!--            --><?php //echo $form->labelEx($model,'likes'); ?>
<!--            --><?php //echo $form->textField($model,'likes',array('class'=>'form-control')); ?>
<!--            --><?php //echo $form->error($model,'likes',array('class'=>'text-danger')); ?>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <?= $form->field($model, 'content')->textArea(['rows' => '3']) ?>
<!--            --><?php //echo $form->labelEx($model,'content'); ?>
<!--            --><?php //echo $form->textArea($model,'content',array('class'=>'form-control', 'rows'=>2, 'cols'=>50)); ?>
<!--            --><?php //echo $form->error($model,'content',array('class'=>'text-danger')); ?>
        </div>

        <p class="pull-left">
            <?= Html::submitButton('Обновить', ['class' => 'btn btn-success']) ?>
            <?= Html::a("<i class='fa fa-list'></i> Менеджер комментариев", 'index', $options = [] )?>
            |
            <?= Html::a("<i class='fa fa-search'></i> Просмотр комментария", ['view', 'id' => $model->id], $options = [] )?>
            |
            Лайки: <span class="label label-primary"><?php echo $model->getLikes(); ?></span>
            Создан: <span class="label label-success"><?php echo CHelper::date($model->created); ?></span>
            <?php if ( !empty($model->updated) ) : ?>
                Обновлён: <span class="label label-warning"><?php echo CHelper::date($model->updated); ?></span>
            <?php endif; ?>
            IP: <span class="label label-default"><?php echo $model->ip; ?></span>
        </p>
    </div>

</div>

    <?php ActiveForm::end(); ?>
</div>