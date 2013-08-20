<?php
$this->breadcrumbs=array(
	'User Register Records'=>array('index'),
	$model->user_id=>array('view','id'=>$model->user_id),
	'Update',
);

$this->menu=array(
	array('label'=>'List UserRegisterRecord','url'=>array('index')),
	array('label'=>'Create UserRegisterRecord','url'=>array('create')),
	array('label'=>'View UserRegisterRecord','url'=>array('view','id'=>$model->user_id)),
	array('label'=>'Manage UserRegisterRecord','url'=>array('admin')),
);
?>

<h1>Update UserRegisterRecord <?php echo $model->user_id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>