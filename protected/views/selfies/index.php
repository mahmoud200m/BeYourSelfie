<?php
/* @var $this SelfiesController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Selfies',
);

$this->menu=array(
	array('label'=>'Create Selfies', 'url'=>array('create')),
);
?>

<h1>Selfies</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
