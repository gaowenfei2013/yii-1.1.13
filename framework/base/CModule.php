<?php
/**
 * CModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CModule is the base class for module and application classes.
 *
 * CModule mainly manages application components and sub-modules.
 *
 * @property string $id The module ID.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property CAttributeCollection $params The list of user-defined parameters.
 * @property string $modulePath The directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
 * @property CModule $parentModule The parent module. Null if this module does not have a parent.
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The application components (indexed by their IDs).
 * @property array $import List of aliases to be imported.
 * @property array $aliases List of aliases to be defined. The array keys are root aliases,
 * while the array values are paths or aliases corresponding to the root aliases.
 * For example,
 * <pre>
 * array(
 *    'models'=>'application.models',              // an existing alias
 *    'extensions'=>'application.extensions',      // an existing alias
 *    'backend'=>dirname(__FILE__).'/../backend',  // a directory
 * )
 * </pre>.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */

// 模块和应用程序的基类
abstract class CModule extends CComponent
{
	/**
	 * @var array the IDs of the application components that should be preloaded.
	 */
	public $preload=array(); // 预加载的组件 由组件id组成的数组
	/**
	 * @var array the behaviors that should be attached to the module.
	 * The behaviors will be attached to the module when {@link init} is called.
	 * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
	 */
	public $behaviors=array(); // 行为

	private $_id; // 模块ID
	private $_parentModule; // 父模块对象
	private $_basePath; // 模块的根目录
	private $_modulePath; // 应用程序模块的目录
	private $_params; // 保存用户自定义参数
	private $_modules=array(); // 实例化好的模块
	private $_moduleConfig=array(); // 所有模块配置信息
	private $_components=array(); // 实例化好的组件
	private $_componentConfig=array(); // 所有组件信息


	/**
	 * Constructor.
	 * @param string $id the ID of this module
	 * @param CModule $parent the parent module (if any)
	 * @param mixed $config the module configuration. It can be either an array or
	 * the path of a PHP file returning the configuration array.
	 */
	
	// $id 模块的ID值 字符串
	// $parent 父模块对象 CModule对象（如果存在父模块的话）
	// $config 模块的配置信息 一个配置数组或者配置文件的地址
	public function __construct($id,$parent,$config=null)
	{
		$this->_id=$id;
		$this->_parentModule=$parent;

		// set basePath at early as possible to avoid trouble
		// 尽早的设置basePath以避免一些麻烦
		if(is_string($config))
			$config=require($config);
		if(isset($config['basePath']))
		{
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		}

		// 设置模块$id的别名
		Yii::setPathOfAlias($id,$this->getBasePath());

		$this->preinit(); // 预初始化 在配置这个模块之前做一些自己想做的事情

		$this->configure($config); // 通过配置信息来配置这个模块
		$this->attachBehaviors($this->behaviors); // 添加行为
		$this->preloadComponents(); // 加载需要预加载的一些组件

		$this->init();
	}

	/**
	 * Getter magic method.
	 * This method is overridden to support accessing application components
	 * like reading module properties.
	 * @param string $name application component or property name
	 * @return mixed the named property value
	 */
	
	// 重写了get魔术方法
	// 先查找应用组件
	public function __get($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name);
		else
			return parent::__get($name);
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named application component is loaded.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	
	// 重写了isset模块方法
	// 先检查组件是否存在
	public function __isset($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name)!==null;
		else
			return parent::__isset($name);
	}

	/**
	 * Returns the module ID.
	 * @return string the module ID.
	 */
	
	// 返回模块的id值
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Sets the module ID.
	 * @param string $id the module ID
	 */
	
	// 设置模块Id值
	public function setId($id)
	{
		$this->_id=$id;
	}

	/**
	 * Returns the root directory of the module.
	 * @return string the root directory of the module. Defaults to the directory containing the module class.
	 */
	
	// 返回模块根目录 如果在配置文件里没有指定basePath值 这里默认为模块类的目录
	public function getBasePath()
	{
		if($this->_basePath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_basePath=dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the module.
	 * This method can only be invoked at the beginning of the constructor.
	 * @param string $path the root directory of the module.
	 * @throws CException if the directory does not exist.
	 */
	
	// 设置模块的根目录
	// 这个方法只应该在初始化时被调用
	public function setBasePath($path)
	{
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii','Base path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * Returns user-defined parameters.
	 * @return CAttributeCollection the list of user-defined parameters
	 */
	
	// 返回用户自定义的参数 一个CAttributeCollection集合对象
	public function getParams()
	{
		if($this->_params!==null)
			return $this->_params;
		else
		{
			$this->_params=new CAttributeCollection;
			$this->_params->caseSensitive=true; // 区分大小写的
			return $this->_params;
		}
	}

	/**
	 * Sets user-defined parameters.
	 * @param array $value user-defined parameters. This should be in name-value pairs.
	 */
	
	// 设置用户自定义参数 有参数名、参数值组成的数组
	public function setParams($value)
	{
		$params=$this->getParams();
		foreach($value as $k=>$v)
			$params->add($k,$v);
	}

	/**
	 * Returns the directory that contains the application modules.
	 * @return string the directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
	 */
	
	// 返回包含应用程序模块的目录
	public function getModulePath()
	{
		if($this->_modulePath!==null)
			return $this->_modulePath;
		else
			return $this->_modulePath=$this->getBasePath().DIRECTORY_SEPARATOR.'modules';
	}

	/**
	 * Sets the directory that contains the application modules.
	 * @param string $value the directory that contains the application modules.
	 * @throws CException if the directory is invalid
	 */
	
	// 设置包含应用程序模块的目录
	public function setModulePath($value)
	{
		if(($this->_modulePath=realpath($value))===false || !is_dir($this->_modulePath))
			throw new CException(Yii::t('yii','The module path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * Sets the aliases that are used in the module.
	 * @param array $aliases list of aliases to be imported
	 */
	
	// 设置在模块的路径别名
	public function setImport($aliases)
	{
		foreach($aliases as $alias)
			Yii::import($alias);
	}

	/**
	 * Defines the root aliases.
	 * @param array $mappings list of aliases to be defined. The array keys are root aliases,
	 * while the array values are paths or aliases corresponding to the root aliases.
	 * For example,
	 * <pre>
	 * array(
	 *    'models'=>'application.models',              // an existing alias
	 *    'extensions'=>'application.extensions',      // an existing alias
	 *    'backend'=>dirname(__FILE__).'/../backend',  // a directory
	 * )
	 * </pre>
	 */
	
	// 定义根别名
	public function setAliases($mappings)
	{
		foreach($mappings as $name=>$alias)
		{
			// 先解析$alias到路径 解析成功 设置这个路径别名 否则直接设置
			if(($path=Yii::getPathOfAlias($alias))!==false)
				Yii::setPathOfAlias($name,$path);
			else
				Yii::setPathOfAlias($name,$alias);
		}
	}

	/**
	 * Returns the parent module.
	 * @return CModule the parent module. Null if this module does not have a parent.
	 */
	
	// 返回父模块
	// 如果没有父模块，返回null，例如应用 Yii::app()->getParentModule();
	public function getParentModule()
	{
		return $this->_parentModule;
	}

	/**
	 * Retrieves the named application module.
	 * The module has to be declared in {@link modules}. A new instance will be created
	 * when calling this method with the given ID for the first time.
	 * @param string $id application module ID (case-sensitive)
	 * @return CModule the module instance, null if the module is disabled or does not exist.
	 */
	
	// 返回指定$id的模块
	// 模块必须定义在modules目录下
	// 第一次获取的时候创建
	public function getModule($id)
	{
		// 如果已经存在 则直接返回
		if(isset($this->_modules[$id]) || array_key_exists($id,$this->_modules))
			return $this->_modules[$id];
		// 若存在这个模块的配置信息 下面尝试创建这个模块
		elseif(isset($this->_moduleConfig[$id]))
		{
			$config=$this->_moduleConfig[$id]; // 从模块配置配置信息中获取$id模块的配置信息
			if(!isset($config['enabled']) || $config['enabled']) // 如果模块没有被禁用
			{
				Yii::trace("Loading \"$id\" module",'system.base.CModule'); // 跟踪
				$class=$config['class']; // 模块类
				unset($config['class'], $config['enabled']);

				// 如果当前对象是应用本身
				// 父模块就是null，第3个参数
				if($this===Yii::app())
					$module=Yii::createComponent($class,$id,null,$config);
				else
					$module=Yii::createComponent($class,$this->getId().'/'.$id,$this,$config);

				return $this->_modules[$id]=$module;
			}
		}
	}

	/**
	 * Returns a value indicating whether the specified module is installed.
	 * @param string $id the module ID
	 * @return boolean whether the specified module is installed.
	 * @since 1.1.2
	 */
	
	// 返回是否安装了指定$id的模块
	public function hasModule($id)
	{
		// 这里会依次从模块的的配置信息和模块的实例里面寻找
		return isset($this->_moduleConfig[$id]) || isset($this->_modules[$id]);
	}

	/**
	 * Returns the configuration of the currently installed modules.
	 * @return array the configuration of the currently installed modules (module ID => configuration)
	 */
	
	// 返回所有已安装模块的配置信息
	public function getModules()
	{
		return $this->_moduleConfig;
	}

	/**
	 * Configures the sub-modules of this module.
	 *
	 * Call this method to declare sub-modules and configure them with their initial property values.
	 * The parameter should be an array of module configurations. Each array element represents a single module,
	 * which can be either a string representing the module ID or an ID-configuration pair representing
	 * a module with the specified ID and the initial property values.
	 *
	 * For example, the following array declares two modules:
	 * <pre>
	 * array(
	 *     'admin',                // a single module ID
	 *     'payment'=>array(       // ID-configuration pair
	 *         'server'=>'paymentserver.com',
	 *     ),
	 * )
	 * </pre>
	 *
	 * By default, the module class is determined using the expression <code>ucfirst($moduleID).'Module'</code>.
	 * And the class file is located under <code>modules/$moduleID</code>.
	 * You may override this default by explicitly specifying the 'class' option in the configuration.
	 *
	 * You may also enable or disable a module by specifying the 'enabled' option in the configuration.
	 *
	 * @param array $modules module configurations.
	 */
	
	// 配置子模块
	public function setModules($modules)
	{
		foreach($modules as $id=>$module)
		{
			// 如果是数字$id，就比如上面的 'admin' 索引为数字 0
			if(is_int($id))
			{
				$id=$module;
				$module=array();
			}

			if(!isset($module['class']))
			{
				Yii::setPathOfAlias($id,$this->getModulePath().DIRECTORY_SEPARATOR.$id);
				$module['class']=$id.'.'.ucfirst($id).'Module';
			}

			if(isset($this->_moduleConfig[$id]))
				$this->_moduleConfig[$id]=CMap::mergeArray($this->_moduleConfig[$id],$module);
			else
				$this->_moduleConfig[$id]=$module;
		}
	}

	/**
	 * Checks whether the named component exists.
	 * @param string $id application component ID
	 * @return boolean whether the named application component exists (including both loaded and disabled.)
	 */
	
	// 判断是否存在指定$id的应用组件（包括已实例化和未实例化的）
	public function hasComponent($id)
	{
		// 这里依次从已实例化的组件和组件配置信息里面寻找
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	/**
	 * Retrieves the named application component.
	 * @param string $id application component ID (case-sensitive)
	 * @param boolean $createIfNull whether to create the component if it doesn't exist yet.
	 * @return IApplicationComponent the application component instance, null if the application component is disabled or does not exist.
	 * @see hasComponent
	 */
	
	// 返回指定$id的应用组件对象 若没东西返回表示应用组件不存在或被禁用了
	// $id 组件名（区分大小写）
	// $createIfNull 是否在组件不存在时 创建这个组件 默认在不存在时创建
	public function getComponent($id,$createIfNull=true)
	{
		// 如果存在 直接返回
		if(isset($this->_components[$id]))
			return $this->_components[$id];
		// 如果存在配置信息 并且 设置了$createIfNull = true 试着创建
		elseif(isset($this->_componentConfig[$id]) && $createIfNull)
		{
			$config=$this->_componentConfig[$id];
			if(!isset($config['enabled']) || $config['enabled']) // 禁用判断
			{
				Yii::trace("Loading \"$id\" application component",'system.CModule'); // 跟踪
				unset($config['enabled']);
				$component=Yii::createComponent($config); // 根据配置信息 创建主键 createComponent在YiiBase.php中
				$component->init(); // 组件初始化
				return $this->_components[$id]=$component; // 记录到_components中
			}
		}
	}

	/**
	 * Puts a component under the management of the module.
	 * The component will be initialized by calling its {@link CApplicationComponent::init() init()}
	 * method if it has not done so.
	 * @param string $id component ID
	 * @param array|IApplicationComponent $component application component
	 * (either configuration array or instance). If this parameter is null,
	 * component will be unloaded from the module.
	 * @param boolean $merge whether to merge the new component configuration
	 * with the existing one. Defaults to true, meaning the previously registered
	 * component configuration with the same ID will be merged with the new configuration.
	 * If set to false, the existing configuration will be replaced completely.
	 * This parameter is available since 1.1.13.
	 */
	
	// 设置一个组件
	// $id 组件ID
	// $component 组件对象 或者 组件的配置信息，或者null表示删除这个应用组件
	// $merge 是否合并 （存在$Id组件配置信息但未实例化，同一个组件类情况下） 合并新的旧的配置信息
	public function setComponent($id,$component,$merge=true)
	{
		// 如果提供的是null，表示删除组件
		if($component===null)
		{
			// 如果不存在，报错哦！！
			unset($this->_components[$id]);
			return;
		}
		// 如果提供的是个组件实例
		elseif($component instanceof IApplicationComponent)
		{
			$this->_components[$id]=$component;

			// 判断有没有初始化过  没有 我们初始化下
			if(!$component->getIsInitialized())
				$component->init();

			return;
		}
		// 如果存在$id的组件实例
		elseif(isset($this->_components[$id]))
		{
			// 如果不是同一个类型
			if(isset($component['class']) && get_class($this->_components[$id])!==$component['class'])
			{
				// 删除已经存在的实例，使用提供的配置信息替换
				unset($this->_components[$id]);
				$this->_componentConfig[$id]=$component; //we should ignore merge here
				return;
			}

			// 配置属性
			foreach($component as $key=>$value)
			{
				if($key!=='class')
					$this->_components[$id]->$key=$value;
			}
		}
		// 存在$id组件（配置信息存在 未实例化） 并且不是同一个组件类 删除掉旧的 用新的（配置）代替
		elseif(isset($this->_componentConfig[$id]['class'],$component['class'])
			&& $this->_componentConfig[$id]['class']!==$component['class'])
		{
			$this->_componentConfig[$id]=$component; //we should ignore merge here
			return;
		}

		// 如果存在这个$id的组件 （上面已经过滤掉了不同组件类情况） 这里就是同一个组件  未实例化情况下
		// 合并配置信息
		if(isset($this->_componentConfig[$id]) && $merge)
			$this->_componentConfig[$id]=CMap::mergeArray($this->_componentConfig[$id],$component);
		// 不存在$id组件
		else
			$this->_componentConfig[$id]=$component;
	}

	/**
	 * Returns the application components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * This parameter has been available since version 1.1.3.
	 * @return array the application components (indexed by their IDs)
	 */
	
	// 返回所有应用组件（组件实例或组件配置信息）
	// 若$loadedOnly为true 则只返回已经实例化的组件 否则返回全部（未实例化的返回配置信息）
	public function getComponents($loadedOnly=true)
	{
		if($loadedOnly)
			return $this->_components;
		else
			return array_merge($this->_componentConfig, $this->_components);
	}

	/**
	 * Sets the application components.
	 *
	 * When a configuration is used to specify a component, it should consist of
	 * the component's initial property values (name-value pairs). Additionally,
	 * a component can be enabled (default) or disabled by specifying the 'enabled' value
	 * in the configuration.
	 *
	 * If a configuration is specified with an ID that is the same as an existing
	 * component or configuration, the existing one will be replaced silently.
	 *
	 * The following is the configuration for two components:
	 * <pre>
	 * array(
	 *     'db'=>array(
	 *         'class'=>'CDbConnection',
	 *         'connectionString'=>'sqlite:path/to/file.db',
	 *     ),
	 *     'cache'=>array(
	 *         'class'=>'CDbCache',
	 *         'connectionID'=>'db',
	 *         'enabled'=>!YII_DEBUG,  // enable caching in non-debug mode
	 *     ),
	 * )
	 * </pre>
	 *
	 * @param array $components application components(id=>component configuration or instances)
	 * @param boolean $merge whether to merge the new component configuration with the existing one.
	 * Defaults to true, meaning the previously registered component configuration of the same ID
	 * will be merged with the new configuration. If false, the existing configuration will be replaced completely.
	 */
	
	// 设置应用程序组件
	// $components 表示组件名->组件配置信息 形式的数组
	// $merge 是否合并 详细解释看setComponent方法
	public function setComponents($components,$merge=true)
	{
		foreach($components as $id=>$component)
			$this->setComponent($id,$component,$merge);
	}

	/**
	 * Configures the module with the specified configuration.
	 * @param array $config the configuration array
	 */
	
	// 使用配置信息配置这个模块
	// 这个$config就是配置文件返回的数组
	public function configure($config)
	{
		if(is_array($config)) // 必须是个数组
		{
			foreach($config as $key=>$value)
				$this->$key=$value;
		}
	}

	/**
	 * Loads static application components.
	 */
	
	// 加载静态应用程序组件
	// 这个通过配置文件的 preload 配置
	// 如 "preload" => array('log'),
	protected function preloadComponents()
	{
		foreach($this->preload as $id)
			$this->getComponent($id);
	}

	/**
	 * Preinitializes the module.
	 * This method is called at the beginning of the module constructor.
	 * You may override this method to do some customized preinitialization work.
	 * Note that at this moment, the module is not configured yet.
	 * @see init
	 */
	
	// 预初始化模块
	// 这个方法在模块的构造函数开头执行
	// 你可以重写这个方法，来做一些自己想要的操作
	// 注意：在此之前模块还没被配置
	protected function preinit()
	{
	}

	/**
	 * Initializes the module.
	 * This method is called at the end of the module constructor.
	 * Note that at this moment, the module has been configured, the behaviors
	 * have been attached and the application components have been registered.
	 * @see preinit
	 */
	
	// 初始化这个模块
	// 这个方法将在模块构造函数执行后执行
	// 注意：在这个时候，模块已经被配置，行为以及被添加、预加载的组件已经被加载好
	protected function init()
	{
	}
}
