<?php
/**
 * CBehavior class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CBehavior is a convenient base class for behavior classes.
 *
 * @property CComponent $owner The owner component that this behavior is attached to.
 * @property boolean $enabled Whether this behavior is enabled.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */

// 所有行为类的基类
class CBehavior extends CComponent implements IBehavior
{
	private $_enabled=false; // 是否开启
	private $_owner; // 行为的所有者

	/**
	 * Declares events and the corresponding event handler methods.
	 * The events are defined by the {@link owner} component, while the handler
	 * methods by the behavior class. The handlers will be attached to the corresponding
	 * events when the behavior is attached to the {@link owner} component; and they
	 * will be detached from the events when the behavior is detached from the component.
	 * Make sure you've declared handler method as public.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	
	// 重写这个方法 返回 事件名=>方法 事件名由$owner组件决定 然而方法由行为定义
	// 当开启行为时 会将此行为中定义的事件绑定到$owner上
	// 相反禁用时  会移除
	// 注意：确保你在此行为类上定义的事件句柄是public的 否则会访问不到
	// 例如 $owner 组件中 定义了一个事件onHaha 
	// 我们可以重写这个方法 返回 array('onhaha' => 'haha') 其中haha是这个行为类中的一个可以访问的方法名称
	public function events()
	{
		return array();
	}

	/**
	 * Attaches the behavior object to the component.
	 * The default implementation will set the {@link owner} property
	 * and attach event handlers as declared in {@link events}.
	 * This method will also set {@link enabled} to true.
	 * Make sure you've declared handler as public and call the parent implementation if you override this method.
	 * @param CComponent $owner the component that this behavior is to be attached to.
	 */
	
	// 将此行为类绑定到组件$owner
	public function attach($owner)
	{
		$this->_enabled=true; // 设置启动行为
		$this->_owner=$owner; // 记录绑定这个行为的组件
		$this->_attachEventHandlers(); // 将这个行为上的事件绑定到$owner上
	}

	/**
	 * Detaches the behavior object from the component.
	 * The default implementation will unset the {@link owner} property
	 * and detach event handlers declared in {@link events}.
	 * This method will also set {@link enabled} to false.
	 * Make sure you call the parent implementation if you override this method.
	 * @param CComponent $owner the component that this behavior is to be detached from.
	 */
	
	// 将此行为从组件$owner中去除
	public function detach($owner)
	{
		// 移除事件
		foreach($this->events() as $event=>$handler)
			$owner->detachEventHandler($event,array($this,$handler));

		$this->_owner=null;
		$this->_enabled=false;
	}

	/**
	 * @return CComponent the owner component that this behavior is attached to.
	 */
	
	// 返回绑定此行为的组件
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * @return boolean whether this behavior is enabled
	 */
	
	// 返回行为是否开启
	public function getEnabled()
	{
		return $this->_enabled;
	}

	/**
	 * @param boolean $value whether this behavior is enabled
	 */
	
	// 设置行为开启状态
	public function setEnabled($value)
	{
		$value=(bool)$value;

		// 如果状态发生了改变，且已经绑定了组件
		if($this->_enabled!=$value && $this->_owner)
		{
			if($value) // 如果是开启，则重新将此行为上的所有事件绑定到$this->_owner上
				$this->_attachEventHandlers();
			else // 如果是禁用 则将此行为上的所有事件从$this->_owner上剥离
			{
				foreach($this->events() as $event=>$handler)
					$this->_owner->detachEventHandler($event,array($this,$handler));
			}
		}

		$this->_enabled=$value;
	}

	// 将此行为上绑定的所有事件绑定到绑定此行为的组件上
	private function _attachEventHandlers()
	{
		$class=new ReflectionClass($this); // 这里使用反射，是为了下面判断方法是否 public 的

		foreach($this->events() as $event=>$handler)
		{
			if($class->getMethod($handler)->isPublic()) // 句柄必须是public的
				$this->_owner->attachEventHandler($event,array($this,$handler));
		}
	}
}
