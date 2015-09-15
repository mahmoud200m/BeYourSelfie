<?php
/* @var $this SelfiesController */
/* @var $model Selfies */

$this->breadcrumbs=array(
	'Selfies'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Selfies', 'url'=>array('index')),
	array('label'=>'Create Selfies', 'url'=>array('create')),
	array('label'=>'Delete Selfies', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
?>

<h1>View Selfies #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
        array(
            'name'=>'image',
            'value'=>'<img src="uploads/selfies/'.$model->image.'" style="max-height: 300px;max-width: 300px;"/>',
            'type'=>'raw',
        ), 
		'user.name',
		'likes',
		'timestamp',
	),
)); ?>
