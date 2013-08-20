<?php
$this->breadcrumbs=array(
	'User Login Records'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List UserLoginRecord','url'=>array('index')),
	array('label'=>'Create UserLoginRecord','url'=>array('create')),
	array('label'=>'Update UserLoginRecord','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete UserLoginRecord','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage UserLoginRecord','url'=>array('admin')),
);
?>

<h1>View UserLoginRecord #<?php echo $model->id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'user_id',
		'time',
		'ip',
		'user_agent',
		'status',
	),
)); ?>
