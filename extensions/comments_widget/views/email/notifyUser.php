<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php echo $commentPage = Html::a(
    $newComment->loadPageTitle(),
    $newComment->getAbsolutePageUrl(),
    array('target'=>'_blank')
) ; ?>

Уважаемый <?php echo $userName; ?>!<br>
Вы получили это письмо потому, что подписаны на уведомления о новых комментариях на странице
<?= Html::a( $newComment->loadPageTitle(),$newComment->getAbsoluteUrl(), $options = ['target'=>'_blank'] )?>

<p>
    Пользователь <strong><?php echo $newComment->getAuthor(); ?></strong> оставил комментарий:
</p>
<p>
    <i><?php echo $newComment->content; ?></i>
</p>
Дата комментирования: <?php echo date('d.m.Y | H:i:s', $newComment->getLastModified()); ?><br>
<?= Html::a('Перейти на страницу для ответа',$newComment->getAbsoluteUrl(), $options = ['target'=>'_blank'] )?>
<hr/>

<p>
    <small>
    Вы всегда можете отписаться от рассылки комментариев со страницы <?php echo $commentPage; ?>, перейдя по этой
        <?= Html::a('ссылке',Url::toRoute(['/comments/handler/unsubscribe', 'hash' => $hash, 'url' => $newComment->url]), $options = ['target'=>'_blank'] )?>.<br>
    Если вы хотите отписаться от рассылки всех комментариев, перейдите по этой
        <?= Html::a('ссылке',Url::toRoute(['/comments/handler/unsubscribe', 'hash' => $hash]), $options = ['target'=>'_blank'] )?>
    </small>
</p>