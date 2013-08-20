<?php
/**
 * CFilterChain class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CFilterChain represents a list of filters being applied to an action.
 *
 * CFilterChain executes the filter list by {@link run()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.filters
 * @since 1.0
 */

// 过滤器链
// 代表一个被应用到动作上的过滤器列表
class CFilterChain extends CList
{
	/**
	 * @var CController the controller who executes the action.
	 */
	public $controller; // 控制器对象
	/**
	 * @var CAction the action being filtered by this chain.
	 */
	public $action; // 动作对象
	/**
	 * @var integer the index of the filter that is to be executed when calling {@link run()}.
	 */
	public $filterIndex=0; // 过滤器链使用的位置索引


	/**
	 * Constructor.
	 * @param CController $controller the controller who executes the action.
	 * @param CAction $action the action being filtered by this chain.
	 */
	public function __construct($controller,$action)
	{
		$this->controller=$controller;
		$this->action=$action;
	}

	/**
	 * CFilterChain factory method.
	 * This method creates a CFilterChain instance.
	 * @param CController $controller the controller who executes the action.
	 * @param CAction $action the action being filtered by this chain.
	 * @param array $filters list of filters to be applied to the action.
	 * @return CFilterChain
	 */
	// 工厂
	public static function create($controller,$action,$filters)
	{
		$chain=new CFilterChain($controller,$action); // 实例化命令链

		$actionID=$action->getId();
		foreach($filters as $filter)
		{
			// 如果是内部过滤器
			if(is_string($filter))  // filterName [+|- action1 action2]
			{
				if(($pos=strpos($filter,'+'))!==false || ($pos=strpos($filter,'-'))!==false)
				{
					$matched=preg_match("/\b{$actionID}\b/i",substr($filter,$pos+1))>0;
					// 这里写的很好的
					// 如果是 + 号，匹配上的话，就使用这个过滤器
					// 如果是 - 号，匹配不上的话，就使用过滤器
					if(($filter[$pos]==='+')===$matched)
						$filter=CInlineFilter::create($controller,trim(substr($filter,0,$pos)));
				}
				else
					$filter=CInlineFilter::create($controller,$filter);
			}
			// 如果是个数组，也就是外部过滤器
			elseif(is_array($filter))  // array('path.to.class [+|- action1, action2]','param1'=>'value1',...)
			{
				// 数组的第一个元素必须存在，就是过滤器的配置信息
				if(!isset($filter[0]))
					throw new CException(Yii::t('yii','The first element in a filter configuration must be the filter class.'));
				$filterClass=$filter[0];
				unset($filter[0]);
				if(($pos=strpos($filterClass,'+'))!==false || ($pos=strpos($filterClass,'-'))!==false)
				{
					$matched=preg_match("/\b{$actionID}\b/i",substr($filterClass,$pos+1))>0;
					if(($filterClass[$pos]==='+')===$matched)
						$filterClass=trim(substr($filterClass,0,$pos));
					else
						continue;
				}
				$filter['class']=$filterClass;
				$filter=Yii::createComponent($filter);
			}

			// 加入命令链
			if(is_object($filter))
			{
				$filter->init();
				$chain->add($filter);
			}
		}
		return $chain;
	}

	/**
	 * Inserts an item at the specified position.
	 * This method overrides the parent implementation by adding
	 * additional check for the item to be added. In particular,
	 * only objects implementing {@link IFilter} can be added to the list.
	 * @param integer $index the specified position.
	 * @param mixed $item new item
	 * @throws CException If the index specified exceeds the bound or the list is read-only, or the item is not an {@link IFilter} instance.
	 */
	// 插入，其实多了一步验证是否实现了 IFilter 接口，也就是要继承 CFilter
	public function insertAt($index,$item)
	{
		if($item instanceof IFilter)
			parent::insertAt($index,$item);
		else
			throw new CException(Yii::t('yii','CFilterChain can only take objects implementing the IFilter interface.'));
	}

	/**
	 * Executes the filter indexed at {@link filterIndex}.
	 * After this method is called, {@link filterIndex} will be automatically incremented by one.
	 * This method is usually invoked in filters so that the filtering process
	 * can continue and the action can be executed.
	 */
	// 执行当前位置的过滤器
	public function run()
	{
		// 如果存在
		if($this->offsetExists($this->filterIndex))
		{
			// 获取这个位置的过滤器，并将位置后移
			$filter=$this->itemAt($this->filterIndex++);
			Yii::trace('Running filter '.($filter instanceof CInlineFilter ? get_class($this->controller).'.filter'.$filter->name.'()':get_class($filter).'.filter()'),'system.web.filters.CFilterChain');
			$filter->filter($this); // 执行这个过滤器，传递的参数就是这个命令链
		}
		else // 没有了，说明过滤器都通过，执行控制器的动作
			$this->controller->runAction($this->action);
	}
}