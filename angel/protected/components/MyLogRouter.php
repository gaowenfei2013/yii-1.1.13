<?php

/**
 * 重写了 CLogRouter ，将 router 的初始化推迟
 * 并分析路由的配置信息，将需要路由的级别传递给 CLogger ，以直接放弃掉不需要路由的信息，例如profile级别的，以节省内存开销
 * 其实分类也可以像上面那么干的，但是我们一般日志路由会记录所有的分类吧
 */

class MyLogRouter extends CApplicationComponent
{
	private $_routes=array();

	private $_routeInitialized = false;

	private $_needLogLevels = array();

	public function init()
	{
		parent::init();
		Yii::getLogger()->attachEventHandler('onFlush',array($this,'collectLogs'));
		Yii::app()->attachEventHandler('onEndRequest',array($this,'processLogs'));
	}

	protected function initRoute()
	{
		if ( ! $this->_routeInitialized)
		{
			foreach($this->_routes as $name=>$route)
			{
				$route=Yii::createComponent($route);
				$route->init();
				$this->_routes[$name]=$route;
			}

			$this->_routeInitialized = true;
		}
	}

	public function getNeedLogLeves()
	{
		return $this->_needLogLevels ? array_unique($this->_needLogLevels) : array();
	}

	public function getRoutes()
	{
		return new CMap($this->_routes);
	}

	public function setRoutes($config)
	{
		foreach($config as $name=>$route)
		{
			$this->_routes[$name]=$route;
			if (isset($this->_needLogLevels) && isset($route['levels']) && trim($route['levels']) != '')
			{
				$this->_needLogLevels = array_merge($this->_needLogLevels, preg_split('/[\s,]+/',strtolower($route['levels']),-1,PREG_SPLIT_NO_EMPTY));
			}
			else
			{
				$this->_needLogLevels = null;
			}
		}
	}

	public function collectLogs($event)
	{
		$this->initRoute();
		$logger=Yii::getLogger();
		$dumpLogs=isset($event->params['dumpLogs']) && $event->params['dumpLogs'];
		foreach($this->_routes as $route)
		{
			if($route->enabled)
				$route->collectLogs($logger,$dumpLogs);
		}
	}

	public function processLogs($event)
	{
		$this->initRoute();
		$logger=Yii::getLogger();
		foreach($this->_routes as $route)
		{
			if($route->enabled)
				$route->collectLogs($logger,true);
		}
	}
}
