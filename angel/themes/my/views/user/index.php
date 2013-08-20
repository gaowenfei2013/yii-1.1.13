<?php
/* @var $this UserController */

$this->breadcrumbs=array(
	'User',
);
?>
<h1><?php echo $this->id . '/' . $this->action->id; ?></h1>

<p>
	You may change the content of this page by modifying
	the file <tt><?php echo __FILE__; ?></tt>.
</p>
fdfd
<?php if( ! $this->beginCache('haha', array('duration'=>3600))) { ?>
...被缓存的内容...
<?php $this->endCache(); } ?>