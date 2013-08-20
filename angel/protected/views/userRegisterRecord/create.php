<?php
$this->breadcrumbs=array(
	'User Register Records'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List UserRegisterRecord','url'=>array('index')),
	array('label'=>'Manage UserRegisterRecord','url'=>array('admin')),
);
?>

<h1>Create UserRegisterRecord</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>