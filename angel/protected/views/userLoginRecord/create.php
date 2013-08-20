<?php
$this->breadcrumbs=array(
	'User Login Records'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List UserLoginRecord','url'=>array('index')),
	array('label'=>'Manage UserLoginRecord','url'=>array('admin')),
);
?>

<h1>Create UserLoginRecord</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>