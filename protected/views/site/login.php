<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->getBaseUrl(true); ?>/css/login.css" />

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions'=>array(
        'class'=>'box login',
    ),
)); ?>
	<fieldset class="boxBody">
		<label>Enter your email & password</label>
		<?php echo $form->textField($model,'email', array('tabindex' => '1', 'placeholder' => 'email', 'required' => 'required')); ?>

		<?php echo $form->passwordField($model,'password', array('tabindex' => '2', 'placeholder' => 'password', 'required' => 'required')); ?>
		<?php echo $form->error($model,'password'); ?>
	</fieldset>
	<footer>
		<label for="LoginForm_rememberMe"><?php echo $form->checkBox($model,'rememberMe', array('tabindex' => '3')); ?>Keep me logged in</label>
		<?php echo CHtml::submitButton('Login', array('tabidex' => '4', 'class'=>"btnLogin")); ?>
	</footer>

<?php $this->endWidget(); ?>
</div><!-- form -->
