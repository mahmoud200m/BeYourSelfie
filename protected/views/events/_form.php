<?php
/* @var $this EventsController */
/* @var $model Events */
/* @var $form CActiveForm */
?>
<script type="text/javascript">
	var number_of_images = 1;
	function add_new_image(){
		number_of_images++;

		$('#images_input').append('<input id="Events_image'+number_of_images+'" type="file" name="Events[image'+number_of_images+']"> ');
		$('#images_input').append('<input type="hidden" name="image'+number_of_images+'" id="image'+number_of_images+'" value="image'+number_of_images+'"> ');
		$('#images_input').append('<a href="#" onclick="add_new_image()">+</a> ');
		$('#images_input').append('<br>');
		return false;
	}
</script>

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
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>512)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div id="images_input">
	<input id="Events_image1" type="file" name="Events[image1]">
	<input type="hidden" name="image1" id="image1" value="image1">
	<a href="#" onclick="add_new_image()">+</a>
	<br>	
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->