<?php

$backend = dirname(dirname(__FILE__));

$frontend = dirname($backend);

Yii::setPathOfAlias('backend', $backend);

$config = array(
    
    'basePath' => $frontend,
    
    'controllerPath' => $backend.'/controllers', // 控制器目录
    
    'viewPath' => $backend.'/views', // 视图目录

    'runtimePath' => $backend.'/runtime', // 临时文件目录

    'name' => '后台管理系统', // 应用名称

    // 这里需要注意先后顺序
    'import' => array(
        'backend.models.*',
        'backend.components.*',
        'application.models.*',
        'application.components.*',
    ),
);

return CMap::mergeArray(require(dirname(__FILE__).'/../../config/main.php'), $config);