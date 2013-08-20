<?php
$this->pageTitle = '绑定账号 - ' . Yii::app()->name;
?>

<h1>绑定账号</h1>

<div class="form">
<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'login-form',
	'enableClientValidation' => TRUE,
	'clientOptions' => array(
		'validateOnSubmit' => TRUE,
	),
)); ?>

<?php echo $form->textFieldRow($model, 'username', array('class' => 'span3', 'maxlength' => 20)); ?>
<?php echo $form->passwordFieldRow($model, 'password', array('class' => 'span3', 'maxlength' => 20)); ?>
<?php echo $form->checkboxRow($model, 'autologin'); ?>
<br />
<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'label'=>'登录')); ?>

<?php $this->endWidget(); ?>
</div><!-- form -->