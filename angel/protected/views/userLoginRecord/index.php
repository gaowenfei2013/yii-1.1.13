<?php
$this->breadcrumbs=array(
	'User Login Records',
);

$this->menu=array(
	array('label'=>'Create UserLoginRecord','url'=>array('create')),
	array('label'=>'Manage UserLoginRecord','url'=>array('admin')),
);
?>

<h1>User Login Records</h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
