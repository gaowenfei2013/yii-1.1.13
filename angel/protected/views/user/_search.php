<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'username',array('class'=>'span5','maxlength'=>40)); ?>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>40)); ?>

	<?php echo $form->textFieldRow($model,'phone',array('class'=>'span5','maxlength'=>11)); ?>

	<?php echo $form->textFieldRow($model,'is_banned',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'login_time',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'login_ip',array('class'=>'span5','maxlength'=>15)); ?>

	<?php echo $form->textFieldRow($model,'is_email_validated',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'is_phone_validated',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'register_by',array('class'=>'span5','maxlength'=>10)); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'Search',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
