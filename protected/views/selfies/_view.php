<?php
/* @var $this SelfiesController */
/* @var $data Selfies */
?>

<div class="view">

	<?php echo CHtml::link('<img src="uploads/selfies/'.$data->image.'" style="max-height: 300px;max-width: 300px;" alt="image"/>', array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->user->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('likes')); ?>:</b>
	<?php echo CHtml::encode($data->likes); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('timestamp')); ?>:</b>
	<?php echo CHtml::encode($data->timestamp); ?>
	<br />

	<?php 

	echo CHtml::link(
		"delete", '#',
        array(
        	'onClick'=>' {'.CHtml::ajax(
    			array(
					'url'=>Yii::app()->createUrl( 'selfies/delete' ),
					// array( // ajaxOptions
				    'type' => 'POST',
				    'beforeSend' => "function( request )
		         	{
		           		$('.delete".$data->id."_loading').animate({opacity: 1});
		         	}",
		    		'success' => "function( data )
					{
		                if( data == 'done' ){
		                	$('.delete".$data->id."').parent().fadeOut();
		                }else{
		                    alert( data );
		                }
		              	$('.delete".$data->id."_loading').animate({opacity: 0});
		     	 	}",
			    	'data' => array('id' => $data->id),
		    	),
                array('live'=>false, 'id'=>'delete'.$data->id,)
	    	).' return false; }',
			'href' => Yii::app()->createUrl( 'selfies/delete' ),
			'class' => 'delete'.$data->id,
		)
	);

 	echo '<img class="action_loading delete'.$data->id.'_loading" />';

	echo CHtml::link(
		($data->confirmed=='y')?"disapprove":"approve", '#',
        array(
        	'onClick'=>' {'.CHtml::ajax(
    			array(
					'url'=>Yii::app()->createUrl( 'selfies/approve' ),
					// array( // ajaxOptions
				    'type' => 'POST',
				    'beforeSend' => "function( request )
		         	{
		           		$('.approve".$data->id."_loading').animate({opacity: 1});
		         	}",
		    		'success' => "function( data )
					{
		                if( data == 'done' ){
		                	if( $('.approve".$data->id."').text() == 'approve' ){
		                    	$('.approve".$data->id."').text('disapprove');
		                    }else{
		                    	$('.approve".$data->id."').text('approve');
		                    }
		                }else{
		                    alert( data );
		                }
		              	$('.approve".$data->id."_loading').animate({opacity: 0});
		     	 	}",
			    	'data' => array('id' => $data->id, 'approve'=>($data->confirmed=='y')?'false':'true'),
		    	),
                array('live'=>false, 'id'=>'approve'.$data->id,)
	    	).' return false; }',
			'href' => Yii::app()->createUrl( 'selfies/approve' ),
			'class' => 'approve'.$data->id,
		)
	);

 	echo '<img class="action_loading approve'.$data->id.'_loading" />';
 	?>

	<br />

</div>