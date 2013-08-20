<?php
$this->breadcrumbs=array(
	'User Profiles'=>array('index'),
	$model->user_id,
);

$this->menu=array(
	array('label'=>'List UserProfile','url'=>array('index')),
	array('label'=>'Create UserProfile','url'=>array('create')),
	array('label'=>'Update UserProfile','url'=>array('update','id'=>$model->user_id)),
	array('label'=>'Delete UserProfile','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->user_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage UserProfile','url'=>array('admin')),
);
?>

<h1>View UserProfile #<?php echo $model->user_id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'user_id',
		'level_id',
		'real_name',
		'tel',
		'phone',
		'nickname',
		'gender',
		'avatar',
		'birthday',
		'qq',
		'msn',
		'province',
		'city',
		'area',
		'safe_question',
		'safe_answer',
		'money',
		'integral',
	),
)); ?>
