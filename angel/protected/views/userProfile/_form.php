<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'user-profile-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'level_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'real_name',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'tel',array('class'=>'span5','maxlength'=>20)); ?>

	<?php echo $form->textFieldRow($model,'phone',array('class'=>'span5','maxlength'=>11)); ?>

	<?php echo $form->textFieldRow($model,'nickname',array('class'=>'span5','maxlength'=>20)); ?>

	<?php echo $form->textFieldRow($model,'gender',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'avatar',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'birthday',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'qq',array('class'=>'span5','maxlength'=>20)); ?>

	<?php echo $form->textFieldRow($model,'msn',array('class'=>'span5','maxlength'=>60)); ?>

	<?php echo $form->textFieldRow($model,'province',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'city',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'area',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'safe_question',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'safe_answer',array('class'=>'span5','maxlength'=>100)); ?>

	<?php echo $form->textFieldRow($model,'money',array('class'=>'span5','maxlength'=>10)); ?>

	<?php echo $form->textFieldRow($model,'integral',array('class'=>'span5','maxlength'=>10)); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>$model->isNewRecord ? 'Create' : 'Save',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
