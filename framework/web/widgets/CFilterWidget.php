<?php
/**
 * CFilterWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFilterWidget is the base class for widgets that can also be used as filters.
 *
 * Derived classes may need to override the following methods:
 * <ul>
 * <li>{@link CWidget::init()} : called when this is object is used as a widget and needs initialization.</li>
 * <li>{@link CWidget::run()} : called when this is object is used as a widget.</li>
 * <li>{@link filter()} : the filtering method called when this object is used as an action filter.</li>
 * </ul>
 *
 * CFilterWidget provides all properties and methods of {@link CWidget} and {@link CFilter}.
 *
 * @property boolean $isFilter Whether this widget is used as a filter.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */

// 过滤器部件
class CFilterWidget extends CWidget implements IFilter
{
	/**
	 * @var boolean whether to stop the action execution when this widget is used as a filter.
	 * This property should be changed only in {@link CWidget::init} method.
	 * Defaults to false, meaning the action should be executed.
	 */
	// 是否终止执行动作（当这个部件作为一个过滤器时）
	// 这个属性只应该在 init 方法里改变
	// 默认为false，表示需要执行动作
	public $stopAction=false;

	// 是否作为过滤器
	private $_isFilter;

	/**
	 * Constructor.
	 * @param CBaseController $owner owner/creator of this widget. It could be either a widget or a controller.
	 */
	// $owner 可以是一个控制器或者一个部件（都是继承于 CBaseController ）
	public function __construct($owner=null)
	{
		parent::__construct($owner);
		$this->_isFilter=($owner===null);
	}

	/**
	 * @return boolean whether this widget is used as a filter.
	 */
	// 返回是否作为过滤器
	public function getIsFilter()
	{
		return $this->_isFilter;
	}

	/**
	 * Performs the filtering.
	 * The default implementation simply calls {@link init()},
	 * {@link CFilterChain::run()} and {@link run()} in order
	 * Derived classes may want to override this method to change this behavior.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	// 执行过滤
	public function filter($filterChain)
	{
		$this->init();
		if(!$this->stopAction)
		{
			$filterChain->run();
			$this->run();
		}
	}
}