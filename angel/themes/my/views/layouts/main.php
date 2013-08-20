<!DOCTYPE html>
<html>
<head>
<title>iframe</title>
<style type="text/css">

#body {
	padding: 0;
	margin: 0;
	width: 100%;
	height: 100%;
	overflow: hidden;
}

#top {
	width:100%;
	height:66px;
	overflow: hidden;
	background: red;
}

#panel {
	position: absolute;
	top: 77px;
	width: 179px;
	left: 8px;
	bottom: 0;
	border-left: 0 none;
	height: auto;
}

#main {
	position: absolute;
	top: 77px;
	left: 192px;
	bottom: 0;
	right: 0;
	overflow: hidden;
	z-index: 4;
}

</style>
</head>

<body id="body">

<div id="top">top</div>

<div id="panel">panel</div>

<div id="main">
	<iframe src="<?php echo Yii::app()->createUrl('user/main');?>" width="100%" height="100%" name="mainFrame" id="mainFrame" frameborder="no" scrolling="auto"></iframe>
</div>

</body>
</html>