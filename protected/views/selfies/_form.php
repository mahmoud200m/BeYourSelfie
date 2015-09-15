<?php
/* @var $this SelfiesController */
/* @var $model Selfies */
/* @var $form CActiveForm */
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'events-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('method'=>'post', 'enctype'=>'multipart/form-data'),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>
	
	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->dropDownList($model, 'user_id', $users_for_dropdown); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div id="images_input">
	<input id="Selfies_image" type="file" name="Selfies[image]">
	<input type="hidden" name="image" id="image" value="image">
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->