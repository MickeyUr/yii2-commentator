<?php
namespace mickey\commentator\controllers;
use mickey\commentator\models\Comment as Comment;
use mickey\commentator\models\NewComments as NewComments;
use mickey\commentator\models\CommentSettings as CommentSettings;
use mickey\commentator\models\query\CommentQuery;
use mickey\commentator\models\search\CommentSearch;
use yii\web\Controller;
use Yii;
use yii\base\View;
use yii\widgets\ActiveForm;
use mickey\commentator\CommentatorAsset;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessRule;
use yii\filters\AccessControl;

class AdminController extends Controller
{
    /**
     * Инициализация контроллера
     */
    public function init()
    {
        parent::init();
//TODO не нравится
        \Yii::$app->view->registerCssFile(
            \Yii::$app->assetManager->publish(
                \Yii::getAlias('@vendor/mickeyur/yii2-commentator/assets/css/styles.css')
        )[1]
		);
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'AjaxUpdateStatus' => ['post'],
                    'AjaxUpdateSetNew' => ['post'],
                    'AjaxUpdateSetOld' => ['post'],
                    'AjaxDelete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => \Yii::$app->getModule('comments')->isSuperuser()?true:false,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
            'accessControl',
			'postOnly + delete',
            'ajaxOnly + AjaxUpdateStatus, AjaxUpdateSetNew, AjaxUpdateSetOld, AjaxDelete',
		);
	}


	public function accessRules()
	{
		if ( \Yii::$app->getModule('comments')->isSuperuser() )
			return array(
				array('allow')
			);

		return array(
			array('deny')
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$model = $this->findModel($id);

		$userID = \Yii::$app->getModule('comments')->getUserID();

		$newComment=NewComments::find()->where(['user_id'=>$userID,'comment_id'=>$id])->one();
		if($newComment)$newComment->delete();

		return $this->render('view',array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->findModel($id);

		$userID = \Yii::$app->getModule('comments')->getUserID();
		$newComment=NewComments::find()->where(['user_id'=>$userID,'comment_id'=>$id])->one();

		if($newComment)$newComment->delete();

		if ( isset($_POST['Comment']) )
		{
			$model->attributes=$_POST['Comment'];

			if( $model->save() )
				$this->redirect(array('view','id'=>$model->id));
		}

		return $this->render('update',['model'=>$model,]);
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			return $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : ['index']);
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
		$searchModel = new CommentSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

    /**
     * Настройки комментариев
     */
    public function actionSettings()
    {
		//        $model = CommentSettings::load();
		$model = CommentSettings::find()->where(['id'=>1])->one();

		if ( isset($_POST['CommentSettings']) )
		{
			$model->attributes = $_POST['CommentSettings'];
			if ( $model->save() )
				\Yii::$app->session->setFlash('settings_saved', \Yii::t('mickeyur\commentator\Module.main', 'Settings saved successfully'));
		}

		return $this->render('settings', ['model'=>$model]);
    }

    /**
     * Обновляет статусы по ajax
     */
    public function actionAjaxUpdateStatus()
    {
		if ( !isset($_POST['status']) || !isset($_POST['checkboxes']) )
			return;

		$userID = \Yii::$app->getModule('comments')->getUserID();

		foreach ($this->loadModels($_POST['checkboxes']) as $model)
		{
			$model->status = $_POST['status'];
			$model->save();
			NewComments::find()->where(['user_id'=>$userID,'comment_id'=>$model->id])->delete();
		}
    }

    /**
     * Делает комментарий новым
     */
    public function actionAjaxUpdateSetNew()
    {
		if ( !isset($_POST['checkboxes']) )
			return;

		$userID = \Yii::$app->getModule('comments')->getUserID();

		foreach ($_POST['checkboxes'] as $comment_id)
		{
			$model = new NewComments();
			$model->user_id = $userID;
			$model->comment_id = $comment_id;
			$model->save();
		}
    }

    /**
     * Делает комментарий новым
     */
    public function actionAjaxUpdateSetOld()
    {
		if ( !isset($_POST['checkboxes']) )
			return;

		$userID = \Yii::$app->getModule('comments')->getUserID();

		foreach ($_POST['checkboxes'] as $comment_id)
			NewComments::find()->where(['user_id'=>$userID,'comment_id'=>$comment_id])->delete();
    }

    /**
     * Удаляет модели по ajax
     */
    public function actionAjaxDelete()
    {
        if ( !isset($_POST['checkboxes']) )
            return;

		foreach ($this->loadModels($_POST['checkboxes']) as $model)
			$model->delete();
    }

    /**
     * Загружает модели по массиву с id'шниками
     * @param $ids
     * @return \CActiveRecord[]
     */
    private function loadModels($ids)
    {
		return Comment::find()->where(['id' => $ids])->all();
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Comment the loaded model
	 * @throws \CHttpException
	 */
	public function findModel($id)
	{
		$model=Comment::findOne($id);
		if($model===null)
			throw new NotFoundHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Comment $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='comment-form')
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
	}
}
