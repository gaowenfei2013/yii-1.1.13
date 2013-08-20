<?php
$this->pageTitle = '绑定账号 - ' . Yii::app()->name;
?>

<h1>绑定账号</h1>

<a href="<?php echo $this->createUrl('pass/login', array('back' => $back)); ?>">绑定账号</a>