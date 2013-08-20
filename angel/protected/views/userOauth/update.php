<?php
$this->breadcrumbs=array(
	'User Oauths'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List UserOauth','url'=>array('index')),
	array('label'=>'Create UserOauth','url'=>array('create')),
	array('label'=>'View UserOauth','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage UserOauth','url'=>array('admin')),
);
?>

<h1>Update UserOauth <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>