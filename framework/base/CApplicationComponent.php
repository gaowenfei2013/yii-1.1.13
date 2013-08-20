<?php
/**
 * This file contains the base application component class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CApplicationComponent is the base class for application component classes.
 *
 * CApplicationComponent implements the basic methods required by {@link IApplicationComponent}.
 *
 * When developing an application component, try to put application component initialization code in
 * the {@link init()} method instead of the constructor. This has the advantage that
 * the application component can be customized through application configuration.
 *
 * @property boolean $isInitialized Whether this application component has been initialized (ie, {@link init()} is invoked).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 应用组件的基类
// 当开发一个应用组件，将初始化代码写入 init 方法而不是 __controller 里。
// 这有个优点，就是应用组件可以通过应用程序进行配置
// 如果我们写了个组件，希望可以通过配置信息来配置的话，我们需要继承这个 CApplicationComponent
// 在 CModule 的 getComponent 里，new 后 执行 init
abstract class CApplicationComponent extends CComponent implements IApplicationComponent
{
	/**
	 * @var array the behaviors that should be attached to this component.
	 * The behaviors will be attached to the component when {@link init} is called.
	 * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
	 */
	public $behaviors=array(); // 保存需要添加到这个组件上的行为

	private $_initialized=false; // 标记是否已经初始化过

	/**
	 * Initializes the application component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application.
	 * If you override this method, make sure to call the parent implementation
	 * so that the application component can be marked as initialized.
	 */
	
	// 初始化这个应用组件
	// 如果你的子类想要重写这个方法，记住要调用 parent::init()
	public function init()
	{
		$this->attachBehaviors($this->behaviors); // 注册行为
		$this->_initialized=true; // 设置已经初始化过了
	}

	/**
	 * Checks if this application component bas been initialized.
	 * @return boolean whether this application component has been initialized (ie, {@link init()} is invoked).
	 */
	
	// 返回是否已经初始化过了
	// 这个方法会在 CModule 的 setComponent 方法里被调用
	public function getIsInitialized()
	{
		return $this->_initialized;
	}
}
