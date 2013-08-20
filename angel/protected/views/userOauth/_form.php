<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'user-oauth-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'uid',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'provider',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'access_token',array('class'=>'span5','maxlength'=>100)); ?>

	<?php echo $form->textFieldRow($model,'expires_in',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'refresh_token',array('class'=>'span5','maxlength'=>100)); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5','maxlength'=>10)); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>$model->isNewRecord ? 'Create' : 'Save',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
