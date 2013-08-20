<?php
$this->breadcrumbs=array(
	'User Login Records'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List UserLoginRecord','url'=>array('index')),
	array('label'=>'Create UserLoginRecord','url'=>array('create')),
	array('label'=>'View UserLoginRecord','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage UserLoginRecord','url'=>array('admin')),
);
?>

<h1>Update UserLoginRecord <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>