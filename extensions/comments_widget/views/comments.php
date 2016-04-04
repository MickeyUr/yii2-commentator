<?php use yii\helpers\Url;

$commentsReplyFormUrl=Url::toRoute('/comments/handler/reply-form');
$commentsUpdateFormUrl=Url::toRoute('/comments/handler/update-form');
$commentsCreateUrl=Url::toRoute('/comments/handler/create');
$commentsUpdateUrl=Url::toRoute('/comments/handler/update');
$commentsDeleteUrl=Url::toRoute('/comments/handler/delete');
$commentsLikesUrl=Url::toRoute('/comments/handler/likes');
$pageUrl=Url::to('');
$script = <<< JS

	var commentsReplyFormUrl = "$commentsReplyFormUrl";
    var commentsUpdateFormUrl = "$commentsUpdateFormUrl";
    var commentsCreateUrl = "$commentsCreateUrl";
    var commentsUpdateUrl = "$commentsUpdateUrl";
    var commentsDeleteUrl = "$commentsDeleteUrl";
    var commentsLikesUrl = "$commentsLikesUrl";
    var pageUrl = "$pageUrl";

JS;
//маркер конца строки, обязательно сразу, без пробелов и табуляции
$this->registerJs($script, yii\web\View::POS_HEAD);
?>

<div class="comments">
    <?php if ( !empty($count) ) : ?>
        <span class="title"><i class="fa fa-comments"></i> Комментарии (<span<?php echo $enableMicrodata ? ' itemprop="commentCount"' : '' ?> data-role="count"><?php echo $count; ?></span>):</span>
    <?php endif; ?>

    <div data-role="tree"><?php echo $this->context->renderTree(); ?></div>

    <span class="title"><i class="fa fa-comment"></i> Добавить комментарий:</span>
    <?php echo $this->render('form', array('model' => $model, 'url' => Yii::$app->getRequest()->getUrl())); ?>

    <div data-role="modal-container"></div>
</div>