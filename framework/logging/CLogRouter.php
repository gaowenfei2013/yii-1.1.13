<?php
/**
 * CLogRouter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogRouter manages log routes that record log messages in different media.
 *
 * For example, a file log route {@link CFileLogRoute} records log messages
 * in log files. An email log route {@link CEmailLogRoute} sends log messages
 * to specific email addresses. See {@link CLogRoute} for more details about
 * different log routes.
 *
 * Log routes may be configured in application configuration like following:
 * <pre>
 * array(
 *     'preload'=>array('log'), // preload log component when app starts
 *     'components'=>array(
 *         'log'=>array(
 *             'class'=>'CLogRouter',
 *             'routes'=>array(
 *                 array(
 *                     'class'=>'CFileLogRoute',
 *                     'levels'=>'trace, info',
 *                     'categories'=>'system.*',
 *                 ),
 *                 array(
 *                     'class'=>'CEmailLogRoute',
 *                     'levels'=>'error, warning',
 *                     'emails'=>array('admin@example.com'),
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 * </pre>
 *
 * You can specify multiple routes with different filtering conditions and different
 * targets, even if the routes are of the same type.
 *
 * @property array $routes The currently initialized routes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.logging
 * @since 1.0
 */

// 这个类用于管理日志路由，并将日志记录到不同的媒体上
class CLogRouter extends CApplicationComponent
{
	private $_routes=array();

	/**
	 * Initializes this application component.
	 * This method is required by the IApplicationComponent interface.
	 */
	public function init()
	{
		parent::init();
		// 根据路由配置信息，创建（日志）路由组件，并保存配置信息
		foreach($this->_routes as $name=>$route)
		{
			$route=Yii::createComponent($route);
			$route->init();
			$this->_routes[$name]=$route;
		}

		// 向日志组件注册onFlush事件
		Yii::getLogger()->attachEventHandler('onFlush',array($this,'collectLogs'));
		// 像应用注册onEndRequest事件
		Yii::app()->attachEventHandler('onEndRequest',array($this,'processLogs'));
	}

	/**
	 * @return array the currently initialized routes
	 */
	// 返回所有日志路由组件
	public function getRoutes()
	{
		return new CMap($this->_routes);
	}

	/**
	 * @param array $config list of route configurations. Each array element represents
	 * the configuration for a single route and has the following array structure:
	 * <ul>
	 * <li>class: specifies the class name or alias for the route class.</li>
	 * <li>name-value pairs: configure the initial property values of the route.</li>
	 * </ul>
	 */
	// 设置日志路由
	public function setRoutes($config)
	{
		foreach($config as $name=>$route)
			$this->_routes[$name]=$route;
	}

	/**
	 * Collects log messages from a logger.
	 * This method is an event handler to the {@link CLogger::onFlush} event.
	 * @param CEvent $event event parameter
	 */
	// 从日志组件收集日志信息
	public function collectLogs($event)
	{
		$logger=Yii::getLogger();
		$dumpLogs=isset($event->params['dumpLogs']) && $event->params['dumpLogs'];
		foreach($this->_routes as $route)
		{
			if($route->enabled)
				$route->collectLogs($logger,$dumpLogs);
		}
	}

	/**
	 * Collects and processes log messages from a logger.
	 * This method is an event handler to the {@link CApplication::onEndRequest} event.
	 * @param CEvent $event event parameter
	 * @since 1.1.0
	 */
	// 收集并处理日志信息
	public function processLogs($event)
	{
		$logger=Yii::getLogger();
		foreach($this->_routes as $route)
		{
			if($route->enabled)
				$route->collectLogs($logger,true);
		}
	}
}
