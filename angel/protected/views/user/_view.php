<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id),array('view','id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('username')); ?>:</b>
	<?php echo CHtml::encode($data->username); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('password')); ?>:</b>
	<?php echo CHtml::encode($data->password); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('email')); ?>:</b>
	<?php echo CHtml::encode($data->email); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('phone')); ?>:</b>
	<?php echo CHtml::encode($data->phone); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_banned')); ?>:</b>
	<?php echo CHtml::encode($data->is_banned); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('need_reset_password')); ?>:</b>
	<?php echo CHtml::encode($data->need_reset_password); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('login_time')); ?>:</b>
	<?php echo CHtml::encode($data->login_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('login_ip')); ?>:</b>
	<?php echo CHtml::encode($data->login_ip); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_email_validated')); ?>:</b>
	<?php echo CHtml::encode($data->is_email_validated); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_phone_validated')); ?>:</b>
	<?php echo CHtml::encode($data->is_phone_validated); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('register_by')); ?>:</b>
	<?php echo CHtml::encode($data->register_by); ?>
	<br />

	*/ ?>

</div>