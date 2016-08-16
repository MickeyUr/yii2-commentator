<?php
use mickey\commentator\helpers\CHelper as CHelper;
use mickey\commentator\models\Comment as Comment;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$script = <<< JS
        $(document).ready(function() {

           $('.search-form form').submit(function(){
            $('#comment-grid').yiiGridView('update', {
                data: $(this).serialize()
            });
            return false;
            });

            function reloadGrid() {
                $.fn.yiiGridView.update("comment-grid");
            };

        });

JS;
//маркер конца строки, обязательно сразу, без пробелов и табуляции
$this->registerJs($script, yii\web\View::POS_END);
?>

<div class="comments admin-comments">

<h1>Менеджер комментариев</h1>

<p>
    В поисковый запрос можно вводить условные операторы (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b> или <b>=</b>).
</p>

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
    ]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function($model, $key, $index, $grid) {
            return [
                'class' => $index%2? "even{$model->getRowCssClass()}" : "odd{$model->getRowCssClass()}"
            ];
        }
        ,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn',
                // you may configure additional properties here
            ],
            'id',
            [
                'attribute'=>'url',
                'format' => 'raw',
                'value'=>function ($data) {
                    return Html::a($data->loadPageTitle(), $data->getAbsoluteUrl());
                },
            ],
            [
                'attribute'=>'author',
                'content'=>function($data){
                    return $data->getAuthor();
                }
            ],
            [
                'attribute'=>'email',
                'content'=>function($data){
                    return $data->getEmail();
                }
            ],
            'content:ntext',
            [
                'attribute'=>'likes',
                'content'=>function($data){
                    return $data->getLikes();
                }
            ],
            [
                'attribute'=>'status',
                'format'=>'text', // Возможные варианты: raw, html
                'content'=>function($data){
                    return $data->getStatus();
                },
                'filter' => Comment::getStatusArray()
            ],
            [
                'attribute'=>'created',
                'content'=>function($data){
                    return Yii::$app->formatter->asDatetime($data->created);
                }
            ],
//            'status',
//            'created',
//            'parent_id',
//            'user_id',
//            'url:ntext',
            // 'email:email',
            // 'ip',
            // 'notify',
            // 'updated',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

<?php //$this->widget('zii.widgets.grid.CGridView', array(
//	'id' => 'comment-grid',
//	'dataProvider' => $model->search(),
//	'filter' => $model,
//    'rowCssClassExpression' => function($row, $data) {
//        return $row%2? "even{$data->getRowCssClass()}" : "odd{$data->getRowCssClass()}";
//    },
//	'columns' => array(
//        array(
//            'class' => 'CCheckBoxColumn',
//            'id' => 'checkboxes',
//            'selectableRows' => 2,
//        ),
//		array(
//            'name' => 'id',
//            'htmlOptions' => array(
//                'width' => '50px',
//                'style' => 'text-align: center;'
//            ),
//        ),
//        array(
//            'name' => 'url',
//            'type' => 'html',
//            'value' => function($data) {
//                return CHtml::link($data->loadPageTitle(), $data->getAbsoluteUrl());
//            },
//        ),
//        array(
//            'name' => 'author',
//            'value' => function($data) {
//                return $data->getAuthor();
//            },
//        ),
//        array(
//            'name' => 'email',
//            'value' => function($data) {
//                return $data->getEmail();
//            },
//        ),
//        array(
//            'name' => 'content',
//            'value' => function($data) {
//                return CHelper::cutStr($data->content);
//            },
//        ),
//        array(
//            'name' => 'likes',
//            'value' => function($data) {
//                return $data->getLikes();
//            },
//            'htmlOptions' => array(
//                'width' => '50px',
//                'style' => 'text-align: center;'
//            ),
//        ),
//        array(
//            'name' => 'status',
//            'filter' => Comment::getStatusArray(),
//            'value' => function($data) {
//                return $data->getStatus();
//            },
//            'htmlOptions' => array(
//                'width' => '120px',
//                'style' => 'text-align: center;'
//            ),
//        ),
//        array(
//            'name' => 'created',
//            'filter' => false,
//            'value' => function($data) {
//                    return CHelper::date( $data->created );
//                },
//            'htmlOptions' => array(
//                'width' => '140px',
//                'style' => 'text-align: center;'
//            ),
//        ),
//		array(
//            'header' => 'Операции',
//			'class'=>'CButtonColumn',
//            'htmlOptions' => array(
//                'width' => '70px',
//                'style' => 'text-align: center;'
//            ),
//		),
//	),
//)); ?>

<p class="control">
    Статус:
    <?= Html::dropDownList('status', '',  Comment::getStatusArray(),$params = ['prompt' => '--Выберите статус--']); ?>
    <?= Html::submitButton('Применить', ['class' => 'ajaxUpdateStatus']) ?>
<!--    --><?php //echo CHtml::ajaxSubmitButton('Применить', array('ajaxUpdateStatus'), array('success' => 'reloadGrid')); ?>
    |
    <?= Html::submitButton('Отметить прочитанными', ['class' => 'ajaxUpdateSetOld']) ?>
<!--    --><?php //echo CHtml::ajaxSubmitButton('Отметить прочитанными', array('ajaxUpdateSetOld'), array('success' => 'reloadGrid')); ?>
    |
    <?= Html::submitButton('Отметить новыми', ['class' => 'ajaxUpdateSetNew']) ?>
<!--    --><?php //echo CHtml::ajaxSubmitButton('Отметить новыми', array('ajaxUpdateSetNew'), array('success' => 'reloadGrid')); ?>
    |
    <?php echo Html::submitButton('Удалить', array('ajaxDelete'), array(
        'beforeSend' => 'function(){
            return confirm("' . Yii::t('mickeyur\commentator\Module.main', 'Are you sure you want to delete selected items?') . '");
        }',
        'success' => 'reloadGrid'
    )); ?>
<!--    --><?php //echo CHtml::ajaxSubmitButton('Удалить', array('ajaxDelete'), array(
//        'beforeSend' => 'function(){
//            return confirm("' . Yii::t('pendalf89\yii_commentator\CommentsModule.main', 'Are you sure you want to delete selected items?') . '");
//        }',
//        'success' => 'reloadGrid'
//    )); ?>
    |
    <?= Html::a("<i class='fa fa-cog'></i> Настройки", Url::toRoute('settings'), $options = [] )?>
<!--    --><?php //echo CHtml::link('<i class="fa fa-cog"></i> Настройки', array('settings')); ?>
</p>

    <?php ActiveForm::end(); ?>
</div>