<?php
$this->pageTitle = '注册 - ' . Yii::app()->name;
?>

<h1>注册</h1>

<div class="form">
<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'register-form',
	'enableClientValidation' => TRUE,
	'enableAjaxValidation' => TRUE,
	'clientOptions' => array(
		'validateOnSubmit' => TRUE,
		'beforeValidateAttribute' => new CJavaScriptExpression('yii_beforeValidateAttribute'),
	),
)); ?>

<?php echo $form->textFieldRow($model, $by, array('class' => 'span3', 'maxlength' => 20)); ?>

<?php if ($by == 'phone'): ?>
<?php echo $form->textFieldRow($model, 'smsCode', array('class' => 'span3', 'maxlength' => 20)); ?>
<?php endif; ?>

<?php echo $form->passwordFieldRow($model, 'password', array('class' => 'span3', 'maxlength' => 20)); ?>

<?php if(CCaptcha::checkRequirements()): ?>
<?php echo $form->textFieldRow($model, 'verifyCode', array('class' => 'span3', 'maxlength' => 20)); ?>
<br />
<?php $this->widget('CCaptcha'); ?>
<?php endif; ?>

<br />
<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'label'=>'注册')); ?>

<?php $this->endWidget(); ?>
</div><!-- form -->

<script type="text/javascript">
	function yii_beforeValidateAttribute(form, attribute){
		if (attribute.inputID.match(/RegisterForm_password$/) || attribute.inputID == 'UsernameRegisterForm_verifyCode'){
			attribute.enableAjaxValidation = false;
		}
		return true;
	}
</script>