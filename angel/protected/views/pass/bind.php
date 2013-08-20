<?php
$this->pageTitle = '绑定账号 - ' . Yii::app()->name;
?>

<h1>绑定账号</h1>

<a href="<?php echo $this->createUrl('login'); ?>">绑定已有账号</a>
<a href="<?php echo $this->createUrl('register'); ?>">绑定新账号</a>

<?php
	echo $status == 2 ? '成功' : ($status == 3 ? '失败' : ''); 
?>