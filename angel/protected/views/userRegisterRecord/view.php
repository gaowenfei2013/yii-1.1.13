<?php
$this->breadcrumbs=array(
	'User Register Records'=>array('index'),
	$model->user_id,
);

$this->menu=array(
	array('label'=>'List UserRegisterRecord','url'=>array('index')),
	array('label'=>'Create UserRegisterRecord','url'=>array('create')),
	array('label'=>'Update UserRegisterRecord','url'=>array('update','id'=>$model->user_id)),
	array('label'=>'Delete UserRegisterRecord','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->user_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage UserRegisterRecord','url'=>array('admin')),
);
?>

<h1>View UserRegisterRecord #<?php echo $model->user_id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'user_id',
		'time',
		'ip',
		'user_agent',
		'source_id',
	),
)); ?>
