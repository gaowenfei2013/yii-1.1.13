<?php
/**
 * CFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFilter is the base class for all filters.
 *
 * A filter can be applied before and after an action is executed.
 * It can modify the context that the action is to run or decorate the result that the
 * action generates.
 *
 * Override {@link preFilter()} to specify the filtering logic that should be applied
 * before the action, and {@link postFilter()} for filtering logic after the action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.filters
 * @since 1.0
 */

// 过滤器基类
class CFilter extends CComponent implements IFilter
{
	/**
	 * Performs the filtering.
	 * The default implementation is to invoke {@link preFilter}
	 * and {@link postFilter} which are meant to be overridden
	 * child classes. If a child class needs to override this method,
	 * make sure it calls <code>$filterChain->run()</code>
	 * if the action should be executed.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	// 执行过滤
	public function filter($filterChain)
	{
		if($this->preFilter($filterChain))
		{
			$filterChain->run();
			$this->postFilter($filterChain);
		}
	}

	/**
	 * Initializes the filter.
	 * This method is invoked after the filter properties are initialized
	 * and before {@link preFilter} is called.
	 * You may override this method to include some initialization logic.
	 * @since 1.1.4
	 */
	// 初始化过滤器
	// 这个方法在初始化好过滤器属性后，在调用preFilter方法前调用
	// 这个方法是用在外部过滤器里，你的外部过滤器可以重写这个方法，做自己的事情
	public function init()
	{
	}

	/**
	 * Performs the pre-action filtering.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action
	 * should be executed.
	 */
	// 动作执行前执行的方法
	// 返回一个boolean值，表示是否继续过滤和执行这个动作
	// 我们的外部过滤器，一般只需要重写这个方法，来判断，如果通过，返回TRUE.
	// 如果失败，抛出个异常。或者输出，并返回FALSE.
	protected function preFilter($filterChain)
	{
		return true;
	}

	/**
	 * Performs the post-action filtering.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	// 动作执行后执行这个方法
	protected function postFilter($filterChain)
	{
	}
}