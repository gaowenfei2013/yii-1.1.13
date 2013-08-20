<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

// 设置bootstrap路径别名
Yii::setPathOfAlias('bootstrap', dirname(__FILE__).'/../extensions/bootstrap');

return array(

	'basePath'          => dirname(__FILE__).DIRECTORY_SEPARATOR.'..', // 应用的根目录
	
	'name'              => '我的应用', // 应用名称
	
	'theme'             => 'bootstrap', // 主题
	
	'defaultController' => 'site', // 默认控制器，其实应该叫默认路由，可以是 site/index
	
	'language'          => 'zh_cn', // 目标语言
	
	//'preload'           => array('log'), // 预先加载

	'catchAllRequest'   => NULL, // 维护模式，路由，例如 array('site/index') 所有请求将被拦截到site/index

	// autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'ext.helpers.*',
		'ext.image.helpers.*',
		'ext.validators.*',
		'application.modules.srbac.controllers.SBaseController', // srbac
		'ext.sshoppingcart.*', // 购物车
	),

	'modules' => array(

		// uncomment the following to enable the Gii tool
		'gii' => array(
			'class'          => 'system.gii.GiiModule',
			'password'       => 'angel',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'      => array('127.0.0.1','::1','192.168.3.153'),
			'generatorPaths' => array(
				'bootstrap.gii',
			),
		),

		// srbac
		'srbac' => array(
			'userclass' => 'User',
			'userid' => 'id',
			'username' => 'username',
			'delimeter' => '@',
			'debug' => TRUE,
			'pageSize' => 10,
			'superUser' => 'Authority',
			'css' => 'srbac.css',
			'layout' => 'webroot.themes.bootstrap.views.layouts.main',
			'notAuthorizedView' => 'srbac.views.authitem.unauthorized',
			'alwaysAllowed' => array('SiteLogin', 'SiteLogout', 'SiteIndex', 'SiteAdmin', 'SiteError', 'SiteContact'),
			'userActions' => array('Show', 'View', 'List'),
			'listBoxNumberOfLines' => 15,
			'imagesPath' => 'srbac.images',
			'imagesPack' => 'noia',
			'iconText' => TRUE,
			'header' => 'srbac.views.authitem.header',
			'footer' => 'srbac.views.authitem.footer',
			'showHeader' => TRUE,
			'showFooter' => TRUE,
			'alwaysAllowedPath' => 'srbac.components'
		),

		'oauth2' => array(
			'oauth2Table' => 'tbl_oauth2',
			'userTable' => '{{user}}',
			'userId' => 'id',
			'userUsername' => 'username',
			'sessionID' => 'session',
			'providerName' => 'provider',
			'providers' => array(
				'weibo' => array(
					'clientId' => '1596106690',
					'clientSecret' => '07b14c760235deb9820bd772761d46fc',
					'redirectUri' => '',
				),

				'qq' => array(
					'clientId' => '217310',
					'clientSecret' => '52bf5a2d17777c4f4d61e648bc29cf7d',
					'redirectUri' => '',
				),
			),
		),

	),

	// application components
	'components'=>array(

		// uncomment the following to enable URLs in path-format
		'urlManager' => array(
			'urlFormat'    => 'path', // url格式，path 或者 get
			'appendParams' => FALSE,
			'rules'        => array(
				'pass/oauth/<provider:[a-z0-9]+>' => 'pass/oauth',
				'oauth/index/<provider:[a-z0-9]+>' => 'oauth/index',
				'<controller:\w+>/<id:\d+>'              => '<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
			),
		),

		'request' => array(
			'class' => 'CHttpRequest',
            'enableCsrfValidation' => TRUE, // 开启防csrf攻击，必须使用CHtml::form创建表单
            'enableCookieValidation' => TRUE, // 开启cookie验证
        ),

		'db' => array(
			'connectionString'   => 'mysql:host=localhost;dbname=angel',
			'emulatePrepare'     => TRUE, // MySQL设置为TRUE
			'enableParamLogging' => TRUE, // 生产环境设置为FALSE
			'enableProfiling'    => TRUE, // 开启这个，我们可以使用 getStats() 查看sql的执行情况
			'schemaCachingDuration' => 0, // 缓存表元数据的时间，需要设置缓存组件，如果表固定了，为了提高性能，可设置个大于0的值，如果需要更新，Yii::app()->db->getSchema()->refresh(); 如果使用了分布式，每天机器上都要调用
			'username'           => 'root',
			'password'           => 'root',
			'charset'            => 'utf8',
			'tablePrefix'        => 'angel_',
		),

		'user' => array(
			'allowAutoLogin' => TRUE, // 是否可以基于cookie自动登录
			'loginUrl' => array('pass/login'), // 登录url地址
		),

        'authManager' => array(
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
            'itemTable' => 'angel_authitem',
            'itemChildTable' => 'angel_authitemchild',
            'assignmentTable' => 'angel_authassignment',
        ),

		'errorHandler' => array(
			'errorAction' => 'site/error', // 发生错误使用的动作
		),

		'log' => array(
			'class'  => 'MyLogRouter',
			'routes' => array(
				array(
					'class'  => 'CFileLogRoute',
					'levels' => 'error, warning, profile',
					'logFile' => 'log.log',
				),
			
				// uncomment the following to show log messages on web pages
/*				array(
					'class'=>'CWebLogRoute',
				),*/

				// 一个非常好用的日志路由扩展
				// 扩展地址：http://www.yiiframework.com/extension/yiidebugtb
/*				array(
					'class'=>'ext.yiidebugtb.XWebDebugRouter',
					'config'=>'alignLeft, opaque, runInDebug, fixedPos, collapsed, yamlStyle',
					'levels'=>'error, warning, trace, profile, info',
					'allowedIPs'=>array('127.0.0.1','::1','192.168.3.153','192\.168\.1[0-5]\.[0-9]{3}'),
				),*/
			),
		),

		// cache
		'cache' => array(
			'class'   => 'CMemCache',
			'servers' => array(
				array(
					'host'   => '127.0.0.1',
					'port'   => 11211,
					'weight' => 50
				),
			),
		),

		// session
		'session' => array(
			'class'   => 'CCacheHttpSession',
			'cacheID' => 'cache', // 保存用的组件
			'timeout' => 1800, // 保存的时间
		),

		// SecurityManager
		'securityManager' => array(
			'class' => 'CSecurityManager',
			'validationKey' => '5e99dbc544c55a9fe39dac8f5417cb74', // 验证用的key
			'encryptionKey' => 'd21f7c15998be10c06b543d6416874fa', // 加密用的key
		),

		// yii-bootstrap http://www.yiiframework.com/extension/bootstrap
		'bootstrap' => array(
			'class' => 'bootstrap.components.Bootstrap',
		),

		// yii-curl http://www.yiiframework.com/extension/yii-curl/
		'curl' => array(
			'class'   => 'ext.curl.Curl',
			'options' => array(),
		),

		// image http://www.yiiframework.com/extension/image
		'image'=>array(
			'class'  => 'ext.image.CImageComponent',
			'driver' => 'ImageMagick', // ImageMagick 或者 GD，当然 ImageMagick 好些
		),


		'settings'=>array(
			'class' => 'ext.cmssettings.CmsSettings',
			'cacheComponentId' => 'cache',
			'cacheId' => 'global_website_settings',
			'cacheTime' => 84000,
			'tableName' => '{{settings}}',
			'dbComponentId' => 'db',
			'createTable' => FALSE,
			'dbEngine' => 'InnoDB',
		),

		// config http://www.yiiframework.com/extension/config
		'config' => array(
			'class' => 'ext.econfig.Econfig',
			'configTableName' => 'angel_config', // 数据表
			'autoCreateConfigTable' => FALSE, // 是否自动创建数据表
			'connectionID' => 'db', // 数据库ID
			'cacheID' => 'cache', // 缓存ID
			'strictMode' => FALSE, // 是否严格模式，当缓存键名（无论是设置还是获取）不存在将抛出异常
		),

		// 购物车
		'shoppingCart' => array(
			'class' => 'ext.sshoppingcart.SShoppingCart',
			'refresh' => TRUE,
			'cacheID' => 'cache',
			'discounts' => array(
				array(
					'class' => 'ext.sshoppingcart.discounts.TestDiscount',
				),
			),
		),

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params' => array(
		// this is used in contact page
		'adminEmail' => 'webmaster@example.com',
	),
);