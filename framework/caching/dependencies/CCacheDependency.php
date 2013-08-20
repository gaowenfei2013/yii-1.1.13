<?php
/**
 * CCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCacheDependency is the base class for cache dependency classes.
 *
 * CCacheDependency implements the {@link ICacheDependency} interface.
 * Child classes should override its {@link generateDependentData} for
 * actual dependency checking.
 *
 * @property boolean $hasChanged Whether the dependency has changed.
 * @property mixed $dependentData The data used to determine if dependency has been changed.
 * This data is available after {@link evaluateDependency} is called.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching.dependencies
 * @since 1.0
 */

// 缓存依赖项
class CCacheDependency extends CComponent implements ICacheDependency
{
	/**
	 * @var boolean Whether this dependency is reusable or not.
	 * If set to true, dependent data for this cache dependency will only be generated once per request.
	 * You can then use the same cache dependency for multiple separate cache calls on the same page
	 * without the overhead of re-evaluating the dependency each time.
	 * Defaults to false;
	 * @since 1.1.11
	 */
	// 这个依赖项是否可重复使用。
	// 如果设为true，这个缓存依赖项的数据在每一次请求中只计算一次。
	// 你可以对多个缓存使用同一个依赖项，减小开销
	public $reuseDependentData=false;

	/**
	 * @var array cached data for reusable dependencies.
	 * @since 1.1.11
	 */
	// 可重复使用的依赖项数据
	private static $_reusableData=array();

	private $_hash;
	private $_data;

	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	// 计算依赖项并保存相关数据
	public function evaluateDependency()
	{
		if ($this->reuseDependentData) // 如果可以重复使用
		{
			$hash=$this->getHash();
			if (!isset(self::$_reusableData[$hash]['dependentData']))
				self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
			$this->_data=self::$_reusableData[$hash]['dependentData'];
		}
		else
			$this->_data=$this->generateDependentData();
	}

	/**
	 * @return boolean whether the dependency has changed.
	 */
	// 返回换成依赖性是否改变
	public function getHasChanged()
	{
		if ($this->reuseDependentData) // 如何可复用
		{
			$hash=$this->getHash();
			if (!isset(self::$_reusableData[$hash]['hasChanged']))
			{
				if (!isset(self::$_reusableData[$hash]['dependentData']))
					self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
				self::$_reusableData[$hash]['hasChanged']=self::$_reusableData[$hash]['dependentData']!=$this->_data;
			}
			return self::$_reusableData[$hash]['hasChanged'];
		}
		else
			return $this->generateDependentData()!=$this->_data;
	}

	/**
	 * @return mixed the data used to determine if dependency has been changed.
	 * This data is available after {@link evaluateDependency} is called.
	 */
	// 返回依赖项数据，用于判断缓存依赖是否改变
	// 这个数据是由evaluateDependency方法产生的
	public function getDependentData()
	{
		return $this->_data;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * Derived classes should override this method to generate actual dependent data.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	// 计算依赖项数据，以确定依赖项是否改变
	// 子类需要重写这个方法
	protected function generateDependentData()
	{
		return null;
	}
	/**
	 * Generates a unique hash that identifies this cache dependency.
	 * @return string the hash for this cache dependency
	 */
	// 生成一个唯一的hash值，用于标识这个缓存依赖项
	private function getHash()
	{
		if($this->_hash===null)
			$this->_hash=sha1(serialize($this));
		return $this->_hash;
	}
}