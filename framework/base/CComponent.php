<?php
/**
 * This file contains the foundation classes for component-based and event-driven programming.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CComponent is the base class for all components.
 *
 * CComponent implements the protocol of defining, using properties and events.
 *
 * A property is defined by a getter method, and/or a setter method.
 * Properties can be accessed in the way like accessing normal object members.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method, e.g
 * <pre>
 * $a=$component->text;     // equivalent to $a=$component->getText();
 * $component->text='abc';  // equivalent to $component->setText('abc');
 * </pre>
 * The signatures of getter and setter methods are as follows,
 * <pre>
 * // getter, defines a readable property 'text'
 * public function getText() { ... }
 * // setter, defines a writable property 'text' with $value to be set to the property
 * public function setText($value) { ... }
 * </pre>
 *
 * An event is defined by the presence of a method whose name starts with 'on'.
 * The event name is the method name. When an event is raised, functions
 * (called event handlers) attached to the event will be invoked automatically.
 *
 * An event can be raised by calling {@link raiseEvent} method, upon which
 * the attached event handlers will be invoked automatically in the order they
 * are attached to the event. Event handlers must have the following signature,
 * <pre>
 * function eventHandler($event) { ... }
 * </pre>
 * where $event includes parameters associated with the event.
 *
 * To attach an event handler to an event, see {@link attachEventHandler}.
 * You can also use the following syntax:
 * <pre>
 * $component->onClick=$callback;    // or $component->onClick->add($callback);
 * </pre>
 * where $callback refers to a valid PHP callback. Below we show some callback examples:
 * <pre>
 * 'handleOnClick'                   // handleOnClick() is a global function
 * array($object,'handleOnClick')    // using $object->handleOnClick()
 * array('Page','handleOnClick')     // using Page::handleOnClick()
 * </pre>
 *
 * To raise an event, use {@link raiseEvent}. The on-method defining an event is
 * commonly written like the following:
 * <pre>
 * public function onClick($event)
 * {
 *     $this->raiseEvent('onClick',$event);
 * }
 * </pre>
 * where <code>$event</code> is an instance of {@link CEvent} or its child class.
 * One can then raise the event by calling the on-method instead of {@link raiseEvent} directly.
 *
 * Both property names and event names are case-insensitive.
 *
 * CComponent supports behaviors. A behavior is an
 * instance of {@link IBehavior} which is attached to a component. The methods of
 * the behavior can be invoked as if they belong to the component. Multiple behaviors
 * can be attached to the same component.
 *
 * To attach a behavior to a component, call {@link attachBehavior}; and to detach the behavior
 * from the component, call {@link detachBehavior}.
 *
 * A behavior can be temporarily enabled or disabled by calling {@link enableBehavior}
 * or {@link disableBehavior}, respectively. When disabled, the behavior methods cannot
 * be invoked via the component.
 *
 * Starting from version 1.1.0, a behavior's properties (either its public member variables or
 * its properties defined via getters and/or setters) can be accessed through the component it
 * is attached to.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 所有组件的基类
class CComponent
{
	private $_e; //保存事件
	private $_m; //保存行为

	/**
	 * Returns a property value, an event handler list or a behavior based on its name.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * $value=$component->propertyName;
	 * $handlers=$component->eventName;
	 * </pre>
	 * @param string $name the property name or event name
	 * @return mixed the property value, event handlers attached to the event, or the named behavior
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	
	// 用来获取组件属性、事件或行为或行为的属性
	public function __get($name)
	{
		$getter='get'.$name;
		// 判断是否存在get方法 获取组件属性
		if(method_exists($this,$getter))
			return $this->$getter();
		// 判断on开头的方法 例如$name = 'onClick';
		elseif(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
		{
			// duplicating getEventHandlers() here for performance
			$name=strtolower($name); // 事件名 小写
			// 如果$this->_e中不存在$name键名的数据 设置$this->_e[$name]为一个空CList对象
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			// 返回事件句柄队列，一个CList对象
			return $this->_e[$name];
		}
		// 如果$this->_m中存在$name键名的数据
		elseif(isset($this->_m[$name]))
			return $this->_m[$name];
		// 如果$this->_m是个数组
		elseif(is_array($this->_m))
		{
			// 循环遍历行为列表
			foreach($this->_m as $object)
			{
				// 如果在这个行为里面可以找到$name 则返回
				if($object->getEnabled() && (property_exists($object,$name) || $object->canGetProperty($name)))
					return $object->$name;
			}
		}
		// 上面都没找到个东西 则抛出未定义异常
		throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
			array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value or callback
	 * @return mixed
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	
	// 设置组件的属性、事件或行为的属性
	public function __set($name,$value)
	{
		$setter='set'.$name;
		// 如果存在set方法
		if(method_exists($this,$setter))
			return $this->$setter($value);
		// 判断on开头的方法 用以获取事件句柄
		elseif(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
		{
			// duplicating getEventHandlers() here for performance
			$name=strtolower($name); // 小写事件名
			// 如果$this->_e中不存在$name键名的数据 设置$this->_e[$name]为一个空CList对象
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			// 向$this->_e[$name]中添加$value事件句柄
			return $this->_e[$name]->add($value);
		}
		// 如果$this->_m是个数组
		elseif(is_array($this->_m))
		{
			foreach($this->_m as $object)
			{
				// 如果这个行为是启动状态，并且可以设置$name这个属性，则将$value值赋值给这个行为的$name属性
				if($object->getEnabled() && (property_exists($object,$name) || $object->canSetProperty($name)))
					return $object->$name=$value;
			}
		}
		// 如果存在get方法，抛出异常，提示属性只读
		if(method_exists($this,'get'.$name))
			throw new CException(Yii::t('yii','Property "{class}.{property}" is read only.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
		// 否则提示属性未定义
		else
			throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Checks if a property value is null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using isset() to detect if a component property is set or not.
	 * @param string $name the property name or the event name
	 * @return boolean
	 */
	
	// 判断是否存在属性、事件事件或行为或的属性
	public function __isset($name)
	{
		$getter='get'.$name;
		// 如果存在get方法，并且返回值不为NULL
		if(method_exists($this,$getter))
			return $this->$getter()!==null;
		// 如果存在以on开头的方法
		elseif(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
		{
			$name=strtolower($name);
			// 如果$this->_e中存在$name键名的值，并且值不为空
			return isset($this->_e[$name]) && $this->_e[$name]->getCount();
		}
		// 如果$this->_m是个数组
		elseif(is_array($this->_m))
		{
			// 如果$this->_m中存在$name名称的行为
 			if(isset($this->_m[$name]))
 				return true;
 			// 循环$this->_m
			foreach($this->_m as $object)
			{
				// 如果这个行为开启状态，并且存在$name属性
				if($object->getEnabled() && (property_exists($object,$name) || $object->canGetProperty($name)))
					return $object->$name!==null;
			}
		}
		return false;
	}

	/**
	 * Sets a component property to be null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using unset() to set a component property to be null.
	 * @param string $name the property name or the event name
	 * @throws CException if the property is read only.
	 * @return mixed
	 */
	
	// 删除组件的属性、事件句柄或行为
	public function __unset($name)
	{
		$setter='set'.$name;
		// 如果存在set方法 调用设置值为NULL
		if(method_exists($this,$setter))
			$this->$setter(null);
		// 如果存在on开头的方法 从$this->_e中删除
		elseif(strncasecmp($name,'on',2)===0 && method_exists($this,$name))
			unset($this->_e[strtolower($name)]);
		// 如果$this->_m是个数组
		elseif(is_array($this->_m))
		{
			// 如果存在$name名称的行为 去除这个行为
			if(isset($this->_m[$name]))
				$this->detachBehavior($name);
			else
			{
				// 循环$this->_m
				foreach($this->_m as $object)
				{
					// 如果这个行为是开启状态
					if($object->getEnabled())
					{
						// 如果存在$name属性 设置为null
						if(property_exists($object,$name))
							return $object->$name=null;
						elseif($object->canSetProperty($name))
							return $object->$setter(null);
					}
				}
			}
		}
		// 如果存在get方法 提示属性只读
		elseif(method_exists($this,'get'.$name))
			throw new CException(Yii::t('yii','Property "{class}.{property}" is read only.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * to implement the behavior feature.
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	
	// 
	public function __call($name,$parameters)
	{
		if($this->_m!==null)
		{
			foreach($this->_m as $object)
			{
				// 如果这个行为开启状态 并且存在$name这个方法 调用这个行为的$name方法
				if($object->getEnabled() && method_exists($object,$name))
					return call_user_func_array(array($object,$name),$parameters);
			}
		}
		// 判断Closure类存在（注：PHP闭包函数是通过Closure类实现的）
		// 如果存在$name这个属性，并且这个属性值是一个闭包函数
		// 则我们调用这个闭包
		if(class_exists('Closure', false) && $this->canGetProperty($name) && $this->$name instanceof Closure)
			return call_user_func_array($this->$name, $parameters);
		// 否则提示找不到
		throw new CException(Yii::t('yii','{class} and its behaviors do not have a method or closure named "{name}".',
			array('{class}'=>get_class($this), '{name}'=>$name)));
	}

	/**
	 * Returns the named behavior object.
	 * The name 'asa' stands for 'as a'.
	 * @param string $behavior the behavior name
	 * @return IBehavior the behavior object, or null if the behavior does not exist
	 */
	
	// 返回名称为$behavior的行为对象
	// 不存在返回null
	public function asa($behavior)
	{
		return isset($this->_m[$behavior]) ? $this->_m[$behavior] : null;
	}

	/**
	 * Attaches a list of behaviors to the component.
	 * Each behavior is indexed by its name and should be an instance of
	 * {@link IBehavior}, a string specifying the behavior class, or an
	 * array of the following structure:
	 * <pre>
	 * array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </pre>
	 * @param array $behaviors list of behaviors to be attached to the component
	 */
	
	// 向组件中添加行为列表
	public function attachBehaviors($behaviors)
	{
		foreach($behaviors as $name=>$behavior)
			$this->attachBehavior($name,$behavior);
	}

	/**
	 * Detaches all behaviors from the component.
	 */
	
	// 分离组件的所有行为
	public function detachBehaviors()
	{
		if($this->_m!==null)
		{
			foreach($this->_m as $name=>$behavior)
				$this->detachBehavior($name);
			$this->_m=null;
		}
	}

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be initialized
	 * by calling its {@link IBehavior::attach} method.
	 * @param string $name the behavior's name. It should uniquely identify this behavior.
	 * @param mixed $behavior the behavior configuration. This is passed as the first
	 * parameter to {@link YiiBase::createComponent} to create the behavior object.
	 * @return IBehavior the behavior object
	 */
	
	// 向组件中添加一个行为
	public function attachBehavior($name,$behavior)
	{
		if(!($behavior instanceof IBehavior))
			$behavior=Yii::createComponent($behavior);
		$behavior->setEnabled(true); // 设置为启动状态
		$behavior->attach($this); // 将行为绑定到当前组件中
		return $this->_m[$name]=$behavior;
	}

	/**
	 * Detaches a behavior from the component.
	 * The behavior's {@link IBehavior::detach} method will be invoked.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @return IBehavior the detached behavior. Null if the behavior does not exist.
	 */
	
	// 分离组件中名称为$name的行为
	// 如果存在则返回分离的行为对象，否则返回null
	public function detachBehavior($name)
	{
		if(isset($this->_m[$name]))
		{
			$this->_m[$name]->detach($this); // 从当前组件上分离
			$behavior=$this->_m[$name];
			unset($this->_m[$name]);
			return $behavior;
		}
	}

	/**
	 * Enables all behaviors attached to this component.
	 */
	
	// 启动组件中所有行为
	public function enableBehaviors()
	{
		if($this->_m!==null)
		{
			foreach($this->_m as $behavior)
				$behavior->setEnabled(true);
		}
	}

	/**
	 * Disables all behaviors attached to this component.
	 */
	
	// 禁用组件中所有行为
	public function disableBehaviors()
	{
		if($this->_m!==null)
		{
			foreach($this->_m as $behavior)
				$behavior->setEnabled(false);
		}
	}

	/**
	 * Enables an attached behavior.
	 * A behavior is only effective when it is enabled.
	 * A behavior is enabled when first attached.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 */
	
	// 启动名称为$name的行为
	public function enableBehavior($name)
	{
		if(isset($this->_m[$name]))
			$this->_m[$name]->setEnabled(true);
	}

	/**
	 * Disables an attached behavior.
	 * A behavior is only effective when it is enabled.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 */
	
	// 禁用名称为$name的行为
	public function disableBehavior($name)
	{
		if(isset($this->_m[$name]))
			$this->_m[$name]->setEnabled(false);
	}

	/**
	 * Determines whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note, property names are case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	
	// 通过set和get方法，判断组件使用有名称为$name的属性
	public function hasProperty($name)
	{
		return method_exists($this,'get'.$name) || method_exists($this,'set'.$name);
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	
	// 通过get方法判断名称为$name的属性是否可以读取
	public function canGetProperty($name)
	{
		return method_exists($this,'get'.$name);
	}

	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	
	// 通过set方法判断名称为$name的属性是否可以设置
	public function canSetProperty($name)
	{
		return method_exists($this,'set'.$name);
	}

	/**
	 * Determines whether an event is defined.
	 * An event is defined if the class has a method named like 'onXXX'.
	 * Note, event name is case-insensitive.
	 * @param string $name the event name
	 * @return boolean whether an event is defined
	 */
	
	// 判断组件中是否存在名称为$name的事件
	public function hasEvent($name)
	{
		return !strncasecmp($name,'on',2) && method_exists($this,$name);
	}

	/**
	 * Checks whether the named event has attached handlers.
	 * @param string $name the event name
	 * @return boolean whether an event has been attached one or several handlers
	 */
	
	// 返回指定事件是否含有事件句柄
	public function hasEventHandler($name)
	{
		$name=strtolower($name);
		return isset($this->_e[$name]) && $this->_e[$name]->getCount()>0;
	}

	/**
	 * Returns the list of attached event handlers for an event.
	 * @param string $name the event name
	 * @return CList list of attached event handlers for the event
	 * @throws CException if the event is not defined
	 */
	
	// 获取$name事件的所有事件句柄，一个CList队列
	public function getEventHandlers($name)
	{
		if($this->hasEvent($name))
		{
			$name=strtolower($name);
			if(!isset($this->_e[$name]))
				$this->_e[$name]=new CList;
			return $this->_e[$name];
		}
		else
			throw new CException(Yii::t('yii','Event "{class}.{event}" is not defined.',
				array('{class}'=>get_class($this), '{event}'=>$name)));
	}

	/**
	 * Attaches an event handler to an event.
	 *
	 * An event handler must be a valid PHP callback, i.e., a string referring to
	 * a global function name, or an array containing two elements with
	 * the first element being an object and the second element a method name
	 * of the object.
	 *
	 * An event handler must be defined with the following signature,
	 * <pre>
	 * function handlerName($event) {}
	 * </pre>
	 * where $event includes parameters associated with the event.
	 *
	 * This is a convenient method of attaching a handler to an event.
	 * It is equivalent to the following code:
	 * <pre>
	 * $component->getEventHandlers($eventName)->add($eventHandler);
	 * </pre>
	 *
	 * Using {@link getEventHandlers}, one can also specify the excution order
	 * of multiple handlers attaching to the same event. For example:
	 * <pre>
	 * $component->getEventHandlers($eventName)->insertAt(0,$eventHandler);
	 * </pre>
	 * makes the handler to be invoked first.
	 *
	 * @param string $name the event name
	 * @param callback $handler the event handler
	 * @throws CException if the event is not defined
	 * @see detachEventHandler
	 */
	
	// 往$name事件中添加事件句柄$handler
	public function attachEventHandler($name,$handler)
	{
		$this->getEventHandlers($name)->add($handler);
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of {@link attachEventHandler}.
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed
	 * @return boolean if the detachment process is successful
	 * @see attachEventHandler
	 */
	
	// 从$name事件中删除$handler句柄
	public function detachEventHandler($name,$handler)
	{
		if($this->hasEventHandler($name))
			return $this->getEventHandlers($name)->remove($handler)!==false;
		else
			return false;
	}

	/**
	 * Raises an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string $name the event name
	 * @param CEvent $event the event parameter
	 * @throws CException if the event is undefined or an event handler is invalid.
	 */
	// 发起一个事件
	// $name 事件的名称
	// $event CEvent 事件
	public function raiseEvent($name,$event)
	{
		$name=strtolower($name);
		if(isset($this->_e[$name]))
		{
			foreach($this->_e[$name] as $handler)
			{
				// 如果$handler是个字符串 意思就是$handler是个函数名字符串
				if(is_string($handler))
					call_user_func($handler,$event);
				// 如果是个可调用结构
				elseif(is_callable($handler,true))
				{
					// 如果是个数组
					if(is_array($handler))
					{
						// an array: 0 - object, 1 - method name
						list($object,$method)=$handler;
						// 如果$object是字符串，则表示这个回调函数是类静态方法的调用
						if(is_string($object))	// static method call
							call_user_func($handler,$event);
						// 如果是个类实例
						elseif(method_exists($object,$method))
							$object->$method($event);
						// 抛出异常 错误的事件句柄
						else
							throw new CException(Yii::t('yii','Event "{class}.{event}" is attached with an invalid handler "{handler}".',
								array('{class}'=>get_class($this), '{event}'=>$name, '{handler}'=>$handler[1])));
					}
					else
						call_user_func($handler,$event);
				}
				// 抛出异常 错误的事件句柄
				else
					throw new CException(Yii::t('yii','Event "{class}.{event}" is attached with an invalid handler "{handler}".',
						array('{class}'=>get_class($this), '{event}'=>$name, '{handler}'=>gettype($handler))));
				// stop further handling if param.handled is set true
				// 如果event的handled设置为true 则停止事件句柄对象剩下的事件句柄调用
				if(($event instanceof CEvent) && $event->handled)
					return;
			}
		}
		// 如果开启了debug模式，并且不存在$name事件，抛出异常 事件不存在
		elseif(YII_DEBUG && !$this->hasEvent($name))
			throw new CException(Yii::t('yii','Event "{class}.{event}" is not defined.',
				array('{class}'=>get_class($this), '{event}'=>$name)));
	}

	/**
	 * Evaluates a PHP expression or callback under the context of this component.
	 *
	 * Valid PHP callback can be class method name in the form of
	 * array(ClassName/Object, MethodName), or anonymous function (only available in PHP 5.3.0 or above).
	 *
	 * If a PHP callback is used, the corresponding function/method signature should be
	 * <pre>
	 * function foo($param1, $param2, ..., $component) { ... }
	 * </pre>
	 * where the array elements in the second parameter to this method will be passed
	 * to the callback as $param1, $param2, ...; and the last parameter will be the component itself.
	 *
	 * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
	 * that can be directly accessed in the expression. See {@link http://us.php.net/manual/en/function.extract.php PHP extract}
	 * for more details. In the expression, the component object can be accessed using $this.
	 *
	 * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
	 * @param array $_data_ additional parameters to be passed to the above expression/callback.
	 * @return mixed the expression result
	 * @since 1.1.0
	 */
	
	// 在组件的上下文中执行一段php表达式或回调函数
	public function evaluateExpression($_expression_,$_data_=array())
	{
		if(is_string($_expression_))
		{
			extract($_data_);
			return eval('return '.$_expression_.';');
		}
		else
		{
			$_data_[]=$this;
			return call_user_func_array($_expression_, $_data_);
		}
	}
}


/**
 * CEvent is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The {@link sender} property describes who raises the event.
 * And the {@link handled} property indicates if the event is handled.
 * If an event handler sets {@link handled} to true, those handlers
 * that are not invoked yet will not be invoked anymore.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 事件的基类
class CEvent extends CComponent
{
	/**
	 * @var object the sender of this event
	 */

	// 事件的发起者
	public $sender;
	/**
	 * @var boolean whether the event is handled. Defaults to false.
	 * When a handler sets this true, the rest of the uninvoked event handlers will not be invoked anymore.
	 */
	
	// 表示该事件有没有被处理完，默认值false，表示没有
	// 如果这个参数变为true了，即使还有事件句柄未执行，你不会执行了
	public $handled=false;
	/**
	 * @var mixed additional event parameters.
	 * @since 1.1.7
	 */
	
	// 额外的参数
	public $params;

	/**
	 * Constructor.
	 * @param mixed $sender sender of the event
	 * @param mixed $params additional parameters for the event
	 */
	
	// 初始化 设置事件的发起者和参数
	public function __construct($sender=null,$params=null)
	{
		$this->sender=$sender;
		$this->params=$params;
	}
}


/**
 * CEnumerable is the base class for all enumerable types.
 *
 * To define an enumerable type, extend CEnumberable and define string constants.
 * Each constant represents an enumerable value.
 * The constant name must be the same as the constant value.
 * For example,
 * <pre>
 * class TextAlign extends CEnumerable
 * {
 *     const Left='Left';
 *     const Right='Right';
 * }
 * </pre>
 * Then, one can use the enumerable values such as TextAlign::Left and
 * TextAlign::Right.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 枚举类型的基类
class CEnumerable
{
}
