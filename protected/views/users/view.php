<?php
/* @var $this UsersController */
/* @var $model Users */

$this->breadcrumbs=array(
	'Users'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Users', 'url'=>array('index')),
	array('label'=>'Manage Users', 'url'=>array('admin')),
	array('label'=>'Create User', 'url'=>array('create')),
	array('label'=>'Update User', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete User', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
?>

<h1>View Users #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
    array(
        'name'=>'image',
        'value'=>'<img src="uploads/users/'.$model->image.'" style="max-height: 300px;max-width: 300px;"/>',
        'type'=>'raw',
    ), 
		'name',
		'email',
		'gender',
		'birthdate',
		array(
        'name'=>'friends',
        'type'=>'html',
        'value'=>$model->friendsLinks,
    ),
    array(
        'name'=>'selfies',
        'type'=>'html',
        'value'=>$model->selfiesLinks,
    ),
	),
)); ?>

<div class="row">
  <?php 
    // foreach ($model->photos as $key => $photo) {
    //   echo '<img src="uploads/users/'.$model->id.'/'.$photo->photo.'" width="100px;" height="100px;"/>';
    //   echo CHtml::link('remove', array('users/removePhoto', 'id' => $photo->id));
    // }
  ?>
	<input type="hidden" name="field_name" value="a"/>
    <?php /*$this->widget('ext.EAjaxUpload.EAjaxUpload', array(
        'id'=>'uploadFile',
        'config'=>array(
           'action'=>Yii::app()->createUrl('users/addPhoto'),
           'allowedExtensions'=>array("jpg","jpeg","gif","png"),//array("jpg","jpeg","gif","exe","mov" and etc...
           'sizeLimit'=>2*1024*1024,// maximum file size in bytes
           'onComplete'=>"js:function(id, fileName, responseJSON){ 
               $('#photo').val(responseJSON['filename']); 
            }",
           'messages'=>array(
                 'typeError'=>"{file} has invalid extension. Only {extensions} are allowed.",
                 'sizeError'=>"{file} is too large, maximum file size is {sizeLimit}.",
                 'minSizeError'=>"{file} is too small, minimum file size is {minSizeLimit}.",
                 'emptyError'=>"{file} is empty, please select files again without it.",
                 'onLeave'=>"The files are being uploaded, if you leave now the upload will be cancelled."
            ),
           'showMessage'=>"js:function(message){ alert(message); }"
        )
    ));*/ ?>
</div>
