<?php
$this->pageTitle = '登录 - ' . Yii::app()->name;
?>

<h1>登录</h1>

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

<br />
<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'label'=>'登录')); ?>

<?php $this->endWidget(); ?>
</div><!-- form -->