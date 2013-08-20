<?php
$this->breadcrumbs=array(
	'User Register Records',
);

$this->menu=array(
	array('label'=>'Create UserRegisterRecord','url'=>array('create')),
	array('label'=>'Manage UserRegisterRecord','url'=>array('admin')),
);
?>

<h1>User Register Records</h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
