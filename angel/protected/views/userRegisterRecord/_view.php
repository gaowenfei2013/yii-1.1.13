<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('user_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->user_id),array('view','id'=>$data->user_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('time')); ?>:</b>
	<?php echo CHtml::encode($data->time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('ip')); ?>:</b>
	<?php echo CHtml::encode($data->ip); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('user_agent')); ?>:</b>
	<?php echo CHtml::encode($data->user_agent); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('source_id')); ?>:</b>
	<?php echo CHtml::encode($data->source_id); ?>
	<br />


</div>