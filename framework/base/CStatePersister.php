<?php
/**
 * This file contains classes implementing security manager feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStatePersister implements a file-based persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 *
 * By default, CStatePersister stores data in a file named 'state.bin' that is located
 * under the application {@link CApplication::getRuntimePath runtime path}.
 * You may change the location by setting the {@link stateFile} property.
 *
 * To retrieve the data from CStatePersister, call {@link load()}. To save the data,
 * call {@link save()}.
 *
 * Comparison among state persister, session and cache is as follows:
 * <ul>
 * <li>session: data persisting within a single user session.</li>
 * <li>state persister: data persisting through all requests/sessions (e.g. hit counter).</li>
 * <li>cache: volatile and fast storage. It may be used as storage medium for session or state persister.</li>
 * </ul>
 *
 * Since server resource is often limited, be cautious if you plan to use CStatePersister
 * to store large amount of data. You should also consider using database-based persister
 * to improve the throughput.
 *
 * CStatePersister is a core application component used to store global application state.
 * It may be accessed via {@link CApplication::getStatePersister()}.
 * page state persistent method based on cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// CStatePersister实现一个基于文件的持久数据存储
// 它可以用来保持多个请求或会话的数据。 
// 默认, CStatePersister存储数据在一个名字叫‘state.bin’文件中它位于应用程序指定的 runtime 路径下。 你可以通过设置stateFile属性来改变它的位置。 
// 从CStatePersister调用数据使用load()。保存数据使用 save(). 
// 持久数据，会话和缓存之间的比较如下:
// session：单个用户的会话持久数据.
// state persister：所有请求的/会话的持久数据（例如，点击统计）。
// cache：不稳定并快速的储诸。它可的使用介于会话和持久数据之间。
// 
// 因为服务器的资源通常有限, 如果你计划使用CStatePersister存储大量的数据。 你应该考虑使用吞吐性高的 基于数据的持久存储。
// CStatePersister是一个核心的应用组件，用于存储全局的应用程序状态。 你也可以通过访问CApplication::getStatePersister() 了解基于缓存的页面持久存储。
class CStatePersister extends CApplicationComponent implements IStatePersister
{
	/**
	 * @var string the file path storing the state data. Make sure the directory containing
	 * the file exists and is writable by the Web server process. If using relative path, also
	 * make sure the path is correct.
	 */
	
	// 存储数据的文件路径。 请确保目录存在这个文件并且Web服务器进程对这个它具有可写权限请确保目录存在这个文件并且Web服务器进程对这个它具有可写权限。 如果使用相对路径，请确保路径是正确的。
	public $stateFile;
	/**
	 * @var string the ID of the cache application component that is used to cache the state values.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching state values.
	 */
	
	// 这个ID指定的是缓存应用组件，用于缓存应用程序状态数据。 默认是‘cache’指的是主缓存应用组件。 设置这个属性为false，将禁止缓存应用程序状态数据。
	public $cacheID='cache';

	/**
	 * Initializes the component.
	 * This method overrides the parent implementation by making sure {@link stateFile}
	 * contains valid value.
	 */
	
	// 初始化这个组件
	public function init()
	{
		parent::init();
		// 如果stateFile未设置 给它个默认值
		if($this->stateFile===null)
			$this->stateFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'state.bin';
		// 判断stateFile目录是否可读可写 否则抛出异常
		$dir=dirname($this->stateFile);
		if(!is_dir($dir) || !is_writable($dir))
			throw new CException(Yii::t('yii','Unable to create application state file "{file}". Make sure the directory containing the file exists and is writable by the Web server process.',
				array('{file}'=>$this->stateFile)));
	}

	/**
	 * Loads state data from persistent storage.
	 * @return mixed state data. Null if no state data available.
	 */
	
	// 从持久存储加载状态数据
	public function load()
	{
		$stateFile=$this->stateFile; // 要读取的文件

		// 如果设置了缓存组件
		if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$cacheKey='Yii.CStatePersister.'.$stateFile; // 缓存的键名
			if(($value=$cache->get($cacheKey))!==false) // 如果存在缓存中未失效 直接反序列化返回
				return unserialize($value);
			elseif(($content=@file_get_contents($stateFile))!==false) // 读取文件
			{
				// 写入缓存， 这里加入了缓存依赖项 文件改变
				$cache->set($cacheKey,$content,0,new CFileCacheDependency($stateFile));
				return unserialize($content);
			}
			else
				return null;
		}
		// 没使用缓存 直接读文件 序列化返回
		elseif(($content=@file_get_contents($stateFile))!==false)
			return unserialize($content);
		else
			return null;
	}

	/**
	 * Saves application state in persistent storage.
	 * @param mixed $state state data (must be serializable).
	 */
	
	// 保存状态数据到持久存储中
	public function save($state)
	{
		file_put_contents($this->stateFile,serialize($state),LOCK_EX);
	}
}
