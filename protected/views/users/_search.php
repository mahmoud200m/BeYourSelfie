<?php
/* @var $this UsersController */
/* @var $model Users */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id',array('size'=>40,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>40,'maxlength'=>80)); ?>
	</div>
        
        <div class="row">
		<?php echo $form->label($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>40,'maxlength'=>80)); ?>
	</div>
        
        <div class="row">
                <?php echo $form->label($model,'email'); ?>
		<?php echo $form->emailField($model,'email',array('size'=>40,'maxlength'=>40)); ?>
	</div>
        
        <div class="row">
		<?php echo $form->label($model,'gender'); ?>
                <?php echo $form->radioButtonList($model,'gender',array('male'=>"male",'female'=>"female"), array('separator' => " ")); ?>
	</div>
        
        <div class="row">
		<?php echo $form->label($model,'birthdate'); ?>
		<?php echo $form->dateField($model,'birthdate'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->