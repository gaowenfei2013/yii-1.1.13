<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/assets/bootstrap/css/bootstrap.min.css" />
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>

	<?php echo $content; ?>

</body>
</html>