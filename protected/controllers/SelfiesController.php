<?php

class SelfiesController extends AdminController
{

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Selfies;
		$users_for_dropdown = CHtml::listData(Users::model()->findAll(), 'id', 'name');

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Selfies']))
		{
			$model->attributes=$_POST['Selfies'];

			if($model->save()){
				$image_path = Yii::getPathOfAlias('webroot').'/uploads/selfies';
				$image = CUploadedFile::getInstance($model, 'image');
				if( !isset($image) ){
					continue;
				}
				$image_name = $model->id.'.'.pathinfo($image->name, PATHINFO_EXTENSION);
    			$image->saveAs($image_path.'/'.$image_name);

    			//resizing image
				$original_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'.'.pathinfo($image_name, PATHINFO_EXTENSION);
				$big_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'_b.'.pathinfo($image_name, PATHINFO_EXTENSION);
				$small_image = Yii::getPathOfAlias('webroot').'/uploads/selfies/'.$model->id.'_s.'.pathinfo($image_name, PATHINFO_EXTENSION);
				$image = WideImage::load($original_image);
				$resized  = $image->resize(1080, 1080);
				$resized->saveToFile($big_image);
				$resized  = $image->resize(540, 540);
				$resized->saveToFile($small_image);

    			$model->image = $image_name;
    			if($model->save()){
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'users_for_dropdown'=>$users_for_dropdown,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Selfies']))
		{
			$model->attributes=$_POST['Selfies'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Selfies',array(
	        'criteria'=>array(
    	        'order'=>"id DESC",
    	        'condition'=>"confirmed != 'd'",
        	),
		));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Selfies('search');

		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Selfies']))
			$model->attributes=$_GET['Selfies'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Selfies::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='selfies-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionUploadImage(){
        AdminController::upload("uploads/selfies_images/");
    }

	public function actionDelete()
	{
		$selfie = $this->loadModel($_POST['id']);

		if( isset($selfie) ){
			$selfie->confirmed = 'd';

			if($selfie->save()){
				echo 'done';
			}else{
				echo 'error';
			}
		}else{
			echo 'error';
		}
	}

    public function actionApprove()
	{
		$model=$this->loadModel($_POST['id']);

		$model->confirmed=($_POST['approve'] == "true")?'y':'n';
		if(!$model->save()){
			echo $model->getErrors();
		}else{
			echo 'done';
		}
	}
}
