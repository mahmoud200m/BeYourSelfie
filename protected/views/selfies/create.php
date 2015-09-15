<?php
/* @var $this SelfiesController */
/* @var $model Selfies */

$this->breadcrumbs=array(
	'Selfies'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Selfies', 'url'=>array('index')),
);
?>

<h1>Create Selfies</h1>

<?php echo $this->renderPartial('_form', array(
    'model'=>$model, 
    'users_for_dropdown'=>$users_for_dropdown,
)); ?>