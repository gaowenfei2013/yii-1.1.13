<?php
/**
 * CInlineFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInlineFilter represents a filter defined as a controller method.
 *
 * CInlineFilter executes the 'filterXYZ($action)' method defined
 * in the controller, where the name 'XYZ' can be retrieved from the {@link name} property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.filters
 * @since 1.0
 */

// 内置过滤器类
class CInlineFilter extends CFilter
{
	/**
	 * @var string name of the filter. It stands for 'XYZ' in the filter method name 'filterXYZ'.
	 */
	// 过滤器的名称
	public $name;

	/**
	 * Creates an inline filter instance.
	 * The creation is based on a string describing the inline method name
	 * and action names that the filter shall or shall not apply to.
	 * @param CController $controller the controller who hosts the filter methods
	 * @param string $filterName the filter name
	 * @return CInlineFilter the created instance
	 * @throws CException if the filter method does not exist
	 */
	// 创建内部过滤器实例
	public static function create($controller,$filterName)
	{
		// 判断内部过滤器的方法是否存在
		if(method_exists($controller,'filter'.$filterName))
		{
			$filter=new CInlineFilter;
			$filter->name=$filterName;
			return $filter;
		}
		else // 否则抛出异常
			throw new CException(Yii::t('yii','Filter "{filter}" is invalid. Controller "{class}" does not have the filter method "filter{filter}".',
				array('{filter}'=>$filterName, '{class}'=>get_class($controller))));
	}

	/**
	 * Performs the filtering.
	 * This method calls the filter method defined in the controller class.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	// 执行这个过滤器的过滤
	// 在控制器内部过滤器里，我们如果希望继续执行剩下的过滤器和动作，必须调用$filterChain->run();
	public function filter($filterChain)
	{
		$method='filter'.$this->name;
		$filterChain->controller->$method($filterChain);
	}
}
