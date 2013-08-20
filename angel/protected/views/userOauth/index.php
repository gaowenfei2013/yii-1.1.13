<?php
$this->breadcrumbs=array(
	'User Oauths',
);

$this->menu=array(
	array('label'=>'Create UserOauth','url'=>array('create')),
	array('label'=>'Manage UserOauth','url'=>array('admin')),
);
?>

<h1>User Oauths</h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
