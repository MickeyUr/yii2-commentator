composer.phar require mickeyur/yii2-commentator:dev-master

yii migrate/up --migrationPath=@vendor/mickeyur/yii2-commentator/migrations


   'modules' => [
      ...
      
        'comments' => [
            'class' => 'mickey\commentator\Module',
            'userModelClass' => 'common\models\User',
            'isSuperuser' => 'Yii::$app->user->identity->isAdmin',
            'userEmailField' => 'email',
            'usernameField' => 'username',
        ],
      ...
      
    ],

Yii-commentator
===============

Модуль древовидных комментариев для Yii. Настройка, использование и подробности на странице проекта <a href="http://zabolotskikh.com/yii/comments-module/">Yii Commentator</a>

<img src="http://zabolotskikh.com/wp-content/uploads/2014/07/comments-850x477.png" alt="Комментарии Yii Commentator">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/07/comments_manage.png" alt="Управление комментариями в Yii Commentator">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/07/comment_settings.png" alt="Настройки модуля комментариев Yii Commentator">
