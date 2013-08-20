<?php
/**
 * YiiBase class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package system
 * @since 1.0
 */

/**
 * Gets the application start timestamp.
 */

// 记录应用开始时间 日志类用到 用于记录执行总时间的
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME',microtime(true));
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */

// 是否开启调试（debug）模式
defined('YII_DEBUG') or define('YII_DEBUG',false);
/**
 * This constant defines how much call stack information (file name and line number) should be logged by Yii::trace().
 * Defaults to 0, meaning no backtrace information. If it is greater than 0,
 * at most that number of call stacks will be logged. Note, only user application call stacks are considered.
 */

// 定义有多少调用堆栈信息（文件名称或行号）需要被Yii::trace()记录
// Yii::trace()用于跟踪的 需要开启debug模式
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',0);
/**
 * This constant defines whether exception handling should be enabled. Defaults to true.
 */

// 定义了是否启用自定义异常处理 如设为true 将用自定义的异常处理方法handleException来处理异常信息
// 详细的可以查看CApplication.php中handleException方法
// 参考：set_exception_handler
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',true);
/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */

// 同上类似 是否启用自定义的错误处理
// 参考：set_error_handler
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',true);
/**
 * Defines the Yii framework installation path.
 */

// 定义了框架路径
defined('YII_PATH') or define('YII_PATH',dirname(__FILE__));
/**
 * Defines the Zii library installation path.
 */

// 定义了Zii扩展库的路径 一般在框架路径下的zii目录
defined('YII_ZII_PATH') or define('YII_ZII_PATH',YII_PATH.DIRECTORY_SEPARATOR.'zii');

/**
 * YiiBase is a helper class serving common framework functionalities.
 *
 * Do not use YiiBase directly. Instead, use its child class {@link Yii} where
 * you can customize methods of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system
 * @since 1.0
 */

// YiiBase是一个帮助类提供框架些公用方法
class YiiBase
{
	/**
	 * @var array class map used by the Yii autoloading mechanism.
	 * The array keys are the class names and the array values are the corresponding class file paths.
	 * @since 1.1.5
	 */
	public static $classMap=array(); // 类名、类路径组成的键值对数组 用于半自动加载 在使用的使用才加载
	/**
	 * @var boolean whether to rely on PHP include path to autoload class files. Defaults to true.
	 * You may set this to be false if your hosting environment doesn't allow changing the PHP
	 * include path, or if you want to append additional autoloaders to the default Yii autoloader.
	 * @since 1.1.8
	 */
	
	// 是否依赖php include_path去自动加载类 默认true
	// 你可以设置为false，当你的主机环境不允许你改变include_path include_path在php.ini配置文件里定义
	public static $enableIncludePath=true;

	// 保存路径别名对应的真实路径 别名->真实路径 键值对数组
	private static $_aliases=array('system'=>YII_PATH,'zii'=>YII_ZII_PATH); // alias => path
	// 保存导入的类名称或目录地址
	private static $_imports=array();					// alias => class name or directory
	// 保存include_path
	private static $_includePaths;						// list of include paths
	// 保存应用实例
	private static $_app; // 保存应用实例
	private static $_logger; // 保存日志类对象



	/**
	 * @return string the version of Yii framework
	 */
	
	// 返回框架版本号
	public static function getVersion()
	{
		return '1.1.13';
	}

	/**
	 * Creates a Web application instance.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 * @return CWebApplication
	 */
	
	// 创建web应用实例
	// $config 应用配置信息
	// 可以是一个数组配置信息或一个字符串 如果是一个字符串 将被看做是一个保存是配置信息的文件地址
	// 请确保basePath被定义 若没定义 将被默认为protected
	public static function createWebApplication($config=null)
	{
		return self::createApplication('CWebApplication',$config);
	}

	/**
	 * Creates a console application instance.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 * @return CConsoleApplication
	 */
	
	// 创建一个控制台应用实例
	public static function createConsoleApplication($config=null)
	{
		return self::createApplication('CConsoleApplication',$config);
	}

	/**
	 * Creates an application of the specified class.
	 * @param string $class the application class name
	 * @param mixed $config application configuration. This parameter will be passed as the parameter
	 * to the constructor of the application class.
	 * @return mixed the application instance
	 */
	
	// 根据指定类创建应用实例
	public static function createApplication($class,$config=null)
	{
		return new $class($config);
	}

	/**
	 * Returns the application singleton or null if the singleton has not been created yet.
	 * @return CApplication the application singleton, null if the singleton has not been created yet.
	 */
	
	// 返回应用单列 如果为null 表示应用还没被创建
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * Stores the application instance in the class static member.
	 * This method helps implement a singleton pattern for CApplication.
	 * Repeated invocation of this method or the CApplication constructor
	 * will cause the throw of an exception.
	 * To retrieve the application instance, use {@link app()}.
	 * @param CApplication $app the application instance. If this is null, the existing
	 * application singleton will be removed.
	 * @throws CException if multiple application instances are registered.
	 */
	
	// 保存应用实例到静态成员中
	// 这个方法帮助实现单利模式
	// 反复调用这个方法 将抛出只能创建一次的异常
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new CException(Yii::t('yii','Yii application can only be created once.'));
	}

	/**
	 * @return string the path of the framework
	 */
	
	// 返回框架目录
	public static function getFrameworkPath()
	{
		return YII_PATH;
	}

	/**
	 * Creates an object and initializes it based on the given configuration.
	 *
	 * The specified configuration can be either a string or an array.
	 * If the former, the string is treated as the object type which can
	 * be either the class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * If the latter, the 'class' element is treated as the object type,
	 * and the rest of the name-value pairs in the array are used to initialize
	 * the corresponding object properties.
	 *
	 * Any additional parameters passed to this method will be
	 * passed to the constructor of the object being created.
	 *
	 * @param mixed $config the configuration. It can be either a string or an array.
	 * @return mixed the created object
	 * @throws CException if the configuration does not have a 'class' element.
	 */
	
	// 根据提供的配置信息 创建组件
	// 配置信息可以是个数组配置信息 或 配置信息文件的字符串地址
	// 如果是字符串 那么这个字符串将被看做对象类型
	// 如果是数组 那么class字段值将被视为对象类型
	// 对象类型将在导入是 被设置为个路径别名
	public static function createComponent($config)
	{
		if(is_string($config))
		{
			$type=$config;
			$config=array();
		}
		elseif(isset($config['class']))
		{
			$type=$config['class'];
			unset($config['class']);
		}
		else
			throw new CException(Yii::t('yii','Object configuration must be an array containing a "class" element.'));

		if(!class_exists($type,false)) // 判断类是否存在
			$type=Yii::import($type,true); // 使用import方法 从$type中解析中类名

		if(($n=func_num_args())>1) // 如果提供的参数大于1 其他参数将作为$type类初始化参数
		{
			$args=func_get_args();
			if($n===2)
				$object=new $type($args[1]);
			elseif($n===3)
				$object=new $type($args[1],$args[2]);
			elseif($n===4)
				$object=new $type($args[1],$args[2],$args[3]);
			else
			{
				unset($args[0]);
				$class=new ReflectionClass($type);
				// Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
				// $object=$class->newInstanceArgs($args);
				$object=call_user_func_array(array($class,'newInstance'),$args); // 5.1.3后才可用
			}
		}
		else
			$object=new $type;

		foreach($config as $key=>$value)
			$object->$key=$value;

		return $object;
	}

	/**
	 * Imports a class or a directory.
	 *
	 * Importing a class is like including the corresponding class file.
	 * The main difference is that importing a class is much lighter because it only
	 * includes the class file when the class is referenced the first time.
	 *
	 * Importing a directory is equivalent to adding a directory into the PHP include path.
	 * If multiple directories are imported, the directories imported later will take
	 * precedence in class file searching (i.e., they are added to the front of the PHP include path).
	 *
	 * Path aliases are used to import a class or directory. For example,
	 * <ul>
	 *   <li><code>application.components.GoogleMap</code>: import the <code>GoogleMap</code> class.</li>
	 *   <li><code>application.components.*</code>: import the <code>components</code> directory.</li>
	 * </ul>
	 *
	 * The same path alias can be imported multiple times, but only the first time is effective.
	 * Importing a directory does not import any of its subdirectories.
	 *
	 * Starting from version 1.1.5, this method can also be used to import a class in namespace format
	 * (available for PHP 5.3 or above only). It is similar to importing a class in path alias format,
	 * except that the dot separator is replaced by the backslash separator. For example, importing
	 * <code>application\components\GoogleMap</code> is similar to importing <code>application.components.GoogleMap</code>.
	 * The difference is that the former class is using qualified name, while the latter unqualified.
	 *
	 * Note, importing a class in namespace format requires that the namespace corresponds to
	 * a valid path alias once backslash characters are replaced with dot characters.
	 * For example, the namespace <code>application\components</code> must correspond to a valid
	 * path alias <code>application.components</code>.
	 *
	 * @param string $alias path alias to be imported
	 * @param boolean $forceInclude whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used. This parameter is used only when
	 * the path alias refers to a class.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	
	// 导入一个类或整个目录
	// 导入一个类是像包括相应的类文件
	// 主要的区别是,当第一次引用这个类的时候才真正包含进来
	// 导入一个目录是相当于一个目录添加到PHP包含路径
	// 如果多个目录被导入，这最后导入的先进行搜索 就是说倒序查找
	// 导入目录的优先级高于include_path
	// 可以使用路径别名导入类或目录
	// 相同的路径别名可以导入多次,但只在第一次是有效的
	// 导入一个目录不会导入他的子目录的
	// 从1.1.5开始，这个方法可以导入一个使用了命名空间的类，这类似于导入一个命名空间下的类，除了点分隔符变成斜杠分隔符。
	// 例如：导入application\components\GoogleMap，就像导入application.components.GoogleMap
	// 区别在于,前者使用了严谨的名称，而后者不是
	// 也就是说若需要使用带命名空间的类我们需要保证命名空间必须和路径别名一致 比如命名空间application\components 和 路径别名application.components
	
	// $alias 路径别名
	// $forceInclude 是否立即将类文件包进来，如果为false，类文件将在使用时才包含进来。这个参数是适用于类文件，目录不行的
	// 返回类名或目录地址 如Yii::import('system.a.b.c') => c Yii::import('system.a.b.c.*') => D:\www\yii\framework\a\b\c
	// 若$alias是一个命名空间表示的类 则返回命名空间形式的类名 如 Yii::import('system\a\b\c') => system\a\b\c
	public static function import($alias,$forceInclude=false)
	{
		// 如果已经导入了
		if(isset(self::$_imports[$alias]))  // previously imported
			return self::$_imports[$alias];

		// 如果存在$alias的类或接口 这里把$alias看做类或接口名看待
		// 比如类不带命名空间的类a  和 带命名空间的类 namespace\a
		if(class_exists($alias,false) || interface_exists($alias,false))
			return self::$_imports[$alias]=$alias;

		// 带命名空间的类 $pos \\ 符号最后一次出现的位置
		if(($pos=strrpos($alias,'\\'))!==false) // a class name in PHP 5.3 namespace format
		{
			$namespace=str_replace('\\','.',ltrim(substr($alias,0,$pos),'\\')); // 计算出命名空间 用.替换\\
			if(($path=self::getPathOfAlias($namespace))!==false) // 命名空间（路径别名）必须存在
			{
				$classFile=$path.DIRECTORY_SEPARATOR.substr($alias,$pos+1).'.php'; // 类文件的路径
				if($forceInclude) // 如果需要立即包含进来
				{
					if(is_file($classFile))
						require($classFile);
					else
						throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file and the file is readable.',array('{alias}'=>$alias)));
					self::$_imports[$alias]=$alias; // 保存到_imports 记录已经导入$alias别名
				}
				else
					self::$classMap[$alias]=$classFile; // 如果不是立即包含 则记录到懒加载中
				return $alias; // 这里返回的是带命名空间的类名
			}
			else
				throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory.',
					array('{alias}'=>$namespace)));
		}

		// 如果$alias是一个简单的类名称
		if(($pos=strrpos($alias,'.'))===false)  // a simple class name
		{
			// 如果立即包含 并且 可以找到这个类
			if($forceInclude && self::autoload($alias))
				self::$_imports[$alias]=$alias;
			return $alias; // 这里返回类名
		}

		// 从路径别名中计算出类名称
		$className=(string)substr($alias,$pos+1);
		$isClass=$className!=='*'; // 是否类 如果等于* 则表示导入的是个目录

		// 若是类 并且 类存在或接口存在
		if($isClass && (class_exists($className,false) || interface_exists($className,false)))
			return self::$_imports[$alias]=$className; // 记录到_imports中 并返回类名

		if(($path=self::getPathOfAlias($alias))!==false)
		{
			// 是类
			if($isClass)
			{
				if($forceInclude) // 立即包含
				{
					if(is_file($path.'.php'))
						require($path.'.php');
					else
						throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file and the file is readable.',array('{alias}'=>$alias)));
					self::$_imports[$alias]=$className; // 保存到_imports中 记录起来
				}
				else
					self::$classMap[$className]=$path.'.php';
				return $className; // 返回的是类名
			}
			else  // a directory
			{
				if(self::$_includePaths===null)
				{
					// 用php include_path值给self::$_includePaths赋值
					self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
					// 删除掉当前目录 . 
					if(($pos=array_search('.',self::$_includePaths,true))!==false)
						unset(self::$_includePaths[$pos]);
				}

				// 将路径别名解析的路径加入到self::$_includePaths中
				array_unshift(self::$_includePaths,$path);

				// 如果不能设置环境的include_path 设置self::$enableIncludePath为fasle
				if(self::$enableIncludePath && set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
					self::$enableIncludePath=false;

				return self::$_imports[$alias]=$path; // 记录起来并返回 返回的是目录路径
			}
		}
		else
			throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
				array('{alias}'=>$alias)));
	}

	/**
	 * Translates an alias into a file path.
	 * Note, this method does not ensure the existence of the resulting file path.
	 * It only checks if the root alias is valid or not.
	 * @param string $alias alias (e.g. system.web.CController)
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	
	// 翻译别名路径到一个真实的路径
	// 注意：这个方法并不确保存在产生的文件路径 也就是说只管解析 不管是否真的存在这个路径
	// 它只检查根别名是有效的还是无效的
	public static function getPathOfAlias($alias)
	{
		// 如果已经保存了这个别名对应的路径 直接返回
		if(isset(self::$_aliases[$alias]))
			return self::$_aliases[$alias];
		// 从$alias中查找.号出现的第一个位置
		elseif(($pos=strpos($alias,'.'))!==false)
		{
			$rootAlias=substr($alias,0,$pos); // 根别名
			if(isset(self::$_aliases[$rootAlias])) // 存在这个根别名
				return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
			elseif(self::$_app instanceof CWebApplication) // 是否web应用
			{
				if(self::$_app->findModule($rootAlias)!==null) // 是否能找到$rootAlias名称的模块 若存在这个名称的模块 会设置这个名称的别名为模块目录的
					return self::getPathOfAlias($alias);
			}
		}
		return false;
	}

	/**
	 * Create a path alias.
	 * Note, this method neither checks the existence of the path nor normalizes the path.
	 * @param string $alias alias to the path
	 * @param string $path the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed.
	 */
	
	// 创建一个路径别名
	// 同上 不确定路径真实性
	// 同时规范路径后面没有斜杠或反斜杠的
	public static function setPathOfAlias($alias,$path)
	{
		if(empty($path))
			unset(self::$_aliases[$alias]);
		else
			self::$_aliases[$alias]=rtrim($path,'\\/');
	}

	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 */
	
	// 类自动装载装载机
	// 这个方法用在__autoload()方法中
	// 返回是否加载成功 true or false
	public static function autoload($className)
	{
		// use include so that the error PHP file may appear
		// 使用include include的时候发生错误 不会立即停止 方便爆出完整的错误信息
		if(isset(self::$classMap[$className])) // 先检查$classMap
			include(self::$classMap[$className]);
		elseif(isset(self::$_coreClasses[$className])) // 从核心类信息中查找
			include(YII_PATH.self::$_coreClasses[$className]);
		else
		{
			// include class file relying on include_path
			// 从include_path中取找

			// 首先检查不含命名空间的
			if(strpos($className,'\\')===false)  // class without namespace
			{
				// 如果self::$enableIncludePath为false  就是你没办法改变include_path值的情况下
				// 循环去找
				if(self::$enableIncludePath===false)
				{
					foreach(self::$_includePaths as $path)
					{
						$classFile=$path.DIRECTORY_SEPARATOR.$className.'.php';
						if(is_file($classFile))
						{
							include($classFile);
							if(YII_DEBUG && basename(realpath($classFile))!==$className.'.php')
								throw new CException(Yii::t('yii','Class name "{class}" does not match class file "{file}".', array(
									'{class}'=>$className,
									'{file}'=>$classFile,
								)));
							break;
						}
					}
				}
				// 如果self::$enableIncludePath为true，yii会调用set_include_path将其设置到php的include_path中
				// 这里我们直接include，让php自动查找
				else
					include($className.'.php'); // 我们让php自己去找
			}
			// 包含命名空间的 5.3以上适用
			else  // class name with namespace in PHP 5.3
			{
				$namespace=str_replace('\\','.',ltrim($className,'\\'));
				if(($path=self::getPathOfAlias($namespace))!==false)
					include($path.'.php');
				else
					return false;
			}
			return class_exists($className,false) || interface_exists($className,false);
		}
		return true;
	}

	/**
	 * Writes a trace message.
	 * This method will only log a message when the application is in debug mode.
	 * @param string $msg message to be logged
	 * @param string $category category of the message
	 * @see log
	 */
	
	// 记录一个跟踪信息 在debug模式下才可以用
	// $msg 要被记录的信息
	// $category 信息的分类
	public static function trace($msg,$category='application')
	{
		if(YII_DEBUG)
			self::log($msg,CLogger::LEVEL_TRACE,$category);
	}

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
	 * and may be recorded in different media, such as file, email, database, using
	 * {@link CLogRouter}.
	 * @param string $msg message to be logged
	 * @param string $level level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	
	// 记录一个信息 使用此方法记录的信息 可以使用Clogger::getLogs检索出来
	// 可以修改CLogRouter是日志被记录到不同的媒体上，如问文件、邮件、数据库
	// $msg 要被记录的信息
	// $level 信息的级别 如trace、warning、error 不区分大小写
	// $category 信息的分类 如system.web 不区分大小写
	public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		// 如果日志类还没创建 先创建
		if(self::$_logger===null)
			self::$_logger=new CLogger;
		// 如果开启debug模式 并且trace_level大于0 并且$level不等于CLogger::LEVEL_PROFILE（也就是profile）
		if(YII_DEBUG && YII_TRACE_LEVEL>0 && $level!==CLogger::LEVEL_PROFILE)
		{
			$traces=debug_backtrace(); // 产生一条回溯跟踪
			$count=0;
			foreach($traces as $trace) // 循环这个跟踪信息（数组）
			{
				// 这里只记录了框架本身的跟踪信息
				if(isset($trace['file'],$trace['line']) && strpos($trace['file'],YII_PATH)!==0)
				{
					$msg.="\nin ".$trace['file'].' ('.$trace['line'].')';
					if(++$count>=YII_TRACE_LEVEL) // 大于YII_TRACE_LEVEL 就停止
						break;
				}
			}
		}
		self::$_logger->log($msg,$level,$category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to {@link endProfile()} with the same token.
	 * The begin- and end- calls must also be properly nested, e.g.,
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block2');
	 * Yii::endProfile('block1');
	 * </pre>
	 * The following sequence is not valid:
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block1');
	 * Yii::endProfile('block2');
	 * </pre>
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile
	 */
	
	// 标志一个代码段的开始
	public static function beginProfile($token,$category='application')
	{
		self::log('begin:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to {@link beginProfile()} with the same token.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile
	 */
	
	// 标志一个代码段的结束
	public static function endProfile($token,$category='application')
	{
		self::log('end:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * @return CLogger message logger
	 */
	
	// 返回日志类实例
	public static function getLogger()
	{
		if(self::$_logger!==null)
			return self::$_logger;
		else
			return self::$_logger=new CLogger;
	}

	/**
	 * Sets the logger object.
	 * @param CLogger $logger the logger object.
	 * @since 1.1.8
	 */
	
	// 设置日志类对象
	public static function setLogger($logger)
	{
		self::$_logger=$logger;
	}

	/**
	 * Returns a string that can be displayed on your Web page showing Powered-by-Yii information
	 * @return string a string that can be displayed on your Web page showing Powered-by-Yii information
	 */
	
	// 返回一个字符串，显示这个项目Powered-by-Yii
	public static function powered()
	{
		return Yii::t('yii','Powered by {yii}.', array('{yii}'=>'<a href="http://www.yiiframework.com/" rel="external">Yii Framework</a>'));
	}

	/**
	 * Translates a message to the specified language.
	 * This method supports choice format (see {@link CChoiceFormat}),
	 * i.e., the message returned will be chosen from a few candidates according to the given
	 * number value. This feature is mainly used to solve plural format issue in case
	 * a message has different plural forms in some languages.
	 * @param string $category message category. Please use only word letters. Note, category 'yii' is
	 * reserved for Yii framework core code use. See {@link CPhpMessageSource} for
	 * more interpretation about message category.
	 * @param string $message the original message
	 * @param array $params parameters to be applied to the message using <code>strtr</code>.
	 * The first parameter can be a number without key.
	 * And in this case, the method will call {@link CChoiceFormat::format} to choose
	 * an appropriate message translation.
	 * Starting from version 1.1.6 you can pass parameter for {@link CChoiceFormat::format}
	 * or plural forms format without wrapping it with array.
	 * This parameter is then available as <code>{n}</code> in the message translation string.
	 * @param string $source which message source application component to use.
	 * Defaults to null, meaning using 'coreMessages' for messages belonging to
	 * the 'yii' category and using 'messages' for the rest messages.
	 * @param string $language the target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 * @return string the translated message
	 * @see CMessageSource
	 */
	
	// 翻译一条为指定语言的信息。 这个方法支持选择格式（参见CChoiceFormat）， 例如，根据给定的数字值从几个侯选条件中返回消息。 假设一条信息在一些语言中有不同的复数格式， 此功能主要用来解决复数格式问题
	// $category 消息分类，请使用字母表示。注意分类yii是预留给框架自己用的
	// $message 原始的消息
	// $params 参数被应用到信息中使用的strtr。 第一个参数可以是一个没有键的数字。 并且在这种情况下，这个方法将调用CChoiceFormat::format 选择一个适当的信息（进行）翻译。 自1.1.6开始，你为CChoiceFormat::format 传递复数参数不必封装成数组
	// $source 指定消息源使用的应用组件。 默认为null，意思是消息使用的‘coreMessages’属于‘yii’类别， 而剩余的消息使用‘messages’。
	// $language 目标语言 如果为空（默认），将使用application language。
	public static function t($category,$message,$params=array(),$source=null,$language=null)
	{
		// 如果应用已经建立
		if(self::$_app!==null)
		{
			// 如果$source没有被指定 默认分类yii和zii使用coreMessages，其他使用messages
			if($source===null)
				$source=($category==='yii'||$category==='zii')?'coreMessages':'messages';
			// 返回指定的message组件 如果message组件存在 翻译信息到指定的语言
			if(($source=self::$_app->getComponent($source))!==null)
				$message=$source->translate($category,$message,$language);
		}

		// 如果参数 直接返回翻译的语言
		if($params===array())
			return $message;
		if(!is_array($params))
			$params=array($params);
		if(isset($params[0])) // number choice
		{
			// 下面这if中 是选择格式 详细可以看i18n/CChoiceFormat类 很简单的一个帮助类
			if(strpos($message,'|')!==false)
			{
				if(strpos($message,'#')===false)
				{
					$chunks=explode('|',$message);
					$expressions=self::$_app->getLocale($language)->getPluralRules();
					if($n=min(count($chunks),count($expressions)))
					{
						for($i=0;$i<$n;$i++)
							$chunks[$i]=$expressions[$i].'#'.$chunks[$i];

						$message=implode('|',$chunks);
					}
				}
				$message=CChoiceFormat::format($message,$params[0]);
			}
			// 如果存在{n}索引 将0索引值赋值给{n}索引
			if(!isset($params['{n}']))
				$params['{n}']=$params[0];
			unset($params[0]);
		}
		return $params!==array() ? strtr($message,$params) : $message;
	}

	/**
	 * Registers a new class autoloader.
	 * The new autoloader will be placed before {@link autoload} and after
	 * any other existing autoloaders.
	 * @param callback $callback a valid PHP callback (function name or array($className,$methodName)).
	 * @param boolean $append whether to append the new autoloader after the default Yii autoloader.
	 */
	
	// 注册类自动加载器 新的类自动加载器将置于autoload之前或 其他已经存在的加载器之后
	// $callback 一个有效的PHP回调
	// $append 是否在默认的Yii类自动加载器后附加新的类自动加载器
	public static function registerAutoloader($callback, $append=false)
	{
		if($append)
		{
			self::$enableIncludePath=false;
			spl_autoload_register($callback); // 使用提供的回调函数 注册__autoload()函数
		}
		else
		{
			spl_autoload_unregister(array('YiiBase','autoload')); // 先去除yii的autload
			spl_autoload_register($callback); // 使用提供的回调函数 注册__autoload()函数
			spl_autoload_register(array('YiiBase','autoload')); // 在加上yii的autoload
		}
	}

	/**
	 * @var array class map for core Yii classes.
	 * NOTE, DO NOT MODIFY THIS ARRAY MANUALLY. IF YOU CHANGE OR ADD SOME CORE CLASSES,
	 * PLEASE RUN 'build autoload' COMMAND TO UPDATE THIS ARRAY.
	 */
	private static $_coreClasses=array(
		'CApplication' => '/base/CApplication.php',
		'CApplicationComponent' => '/base/CApplicationComponent.php',
		'CBehavior' => '/base/CBehavior.php',
		'CComponent' => '/base/CComponent.php',
		'CErrorEvent' => '/base/CErrorEvent.php',
		'CErrorHandler' => '/base/CErrorHandler.php',
		'CException' => '/base/CException.php',
		'CExceptionEvent' => '/base/CExceptionEvent.php',
		'CHttpException' => '/base/CHttpException.php',
		'CModel' => '/base/CModel.php',
		'CModelBehavior' => '/base/CModelBehavior.php',
		'CModelEvent' => '/base/CModelEvent.php',
		'CModule' => '/base/CModule.php',
		'CSecurityManager' => '/base/CSecurityManager.php',
		'CStatePersister' => '/base/CStatePersister.php',
		'CApcCache' => '/caching/CApcCache.php',
		'CCache' => '/caching/CCache.php',
		'CDbCache' => '/caching/CDbCache.php',
		'CDummyCache' => '/caching/CDummyCache.php',
		'CEAcceleratorCache' => '/caching/CEAcceleratorCache.php',
		'CFileCache' => '/caching/CFileCache.php',
		'CMemCache' => '/caching/CMemCache.php',
		'CWinCache' => '/caching/CWinCache.php',
		'CXCache' => '/caching/CXCache.php',
		'CZendDataCache' => '/caching/CZendDataCache.php',
		'CCacheDependency' => '/caching/dependencies/CCacheDependency.php',
		'CChainedCacheDependency' => '/caching/dependencies/CChainedCacheDependency.php',
		'CDbCacheDependency' => '/caching/dependencies/CDbCacheDependency.php',
		'CDirectoryCacheDependency' => '/caching/dependencies/CDirectoryCacheDependency.php',
		'CExpressionDependency' => '/caching/dependencies/CExpressionDependency.php',
		'CFileCacheDependency' => '/caching/dependencies/CFileCacheDependency.php',
		'CGlobalStateCacheDependency' => '/caching/dependencies/CGlobalStateCacheDependency.php',
		'CAttributeCollection' => '/collections/CAttributeCollection.php',
		'CConfiguration' => '/collections/CConfiguration.php',
		'CList' => '/collections/CList.php',
		'CListIterator' => '/collections/CListIterator.php',
		'CMap' => '/collections/CMap.php',
		'CMapIterator' => '/collections/CMapIterator.php',
		'CQueue' => '/collections/CQueue.php',
		'CQueueIterator' => '/collections/CQueueIterator.php',
		'CStack' => '/collections/CStack.php',
		'CStackIterator' => '/collections/CStackIterator.php',
		'CTypedList' => '/collections/CTypedList.php',
		'CTypedMap' => '/collections/CTypedMap.php',
		'CConsoleApplication' => '/console/CConsoleApplication.php',
		'CConsoleCommand' => '/console/CConsoleCommand.php',
		'CConsoleCommandBehavior' => '/console/CConsoleCommandBehavior.php',
		'CConsoleCommandEvent' => '/console/CConsoleCommandEvent.php',
		'CConsoleCommandRunner' => '/console/CConsoleCommandRunner.php',
		'CHelpCommand' => '/console/CHelpCommand.php',
		'CDbCommand' => '/db/CDbCommand.php',
		'CDbConnection' => '/db/CDbConnection.php',
		'CDbDataReader' => '/db/CDbDataReader.php',
		'CDbException' => '/db/CDbException.php',
		'CDbMigration' => '/db/CDbMigration.php',
		'CDbTransaction' => '/db/CDbTransaction.php',
		'CActiveFinder' => '/db/ar/CActiveFinder.php',
		'CActiveRecord' => '/db/ar/CActiveRecord.php',
		'CActiveRecordBehavior' => '/db/ar/CActiveRecordBehavior.php',
		'CDbColumnSchema' => '/db/schema/CDbColumnSchema.php',
		'CDbCommandBuilder' => '/db/schema/CDbCommandBuilder.php',
		'CDbCriteria' => '/db/schema/CDbCriteria.php',
		'CDbExpression' => '/db/schema/CDbExpression.php',
		'CDbSchema' => '/db/schema/CDbSchema.php',
		'CDbTableSchema' => '/db/schema/CDbTableSchema.php',
		'CMssqlColumnSchema' => '/db/schema/mssql/CMssqlColumnSchema.php',
		'CMssqlCommandBuilder' => '/db/schema/mssql/CMssqlCommandBuilder.php',
		'CMssqlPdoAdapter' => '/db/schema/mssql/CMssqlPdoAdapter.php',
		'CMssqlSchema' => '/db/schema/mssql/CMssqlSchema.php',
		'CMssqlSqlsrvPdoAdapter' => '/db/schema/mssql/CMssqlSqlsrvPdoAdapter.php',
		'CMssqlTableSchema' => '/db/schema/mssql/CMssqlTableSchema.php',
		'CMysqlColumnSchema' => '/db/schema/mysql/CMysqlColumnSchema.php',
		'CMysqlCommandBuilder' => '/db/schema/mysql/CMysqlCommandBuilder.php',
		'CMysqlSchema' => '/db/schema/mysql/CMysqlSchema.php',
		'CMysqlTableSchema' => '/db/schema/mysql/CMysqlTableSchema.php',
		'COciColumnSchema' => '/db/schema/oci/COciColumnSchema.php',
		'COciCommandBuilder' => '/db/schema/oci/COciCommandBuilder.php',
		'COciSchema' => '/db/schema/oci/COciSchema.php',
		'COciTableSchema' => '/db/schema/oci/COciTableSchema.php',
		'CPgsqlColumnSchema' => '/db/schema/pgsql/CPgsqlColumnSchema.php',
		'CPgsqlSchema' => '/db/schema/pgsql/CPgsqlSchema.php',
		'CPgsqlTableSchema' => '/db/schema/pgsql/CPgsqlTableSchema.php',
		'CSqliteColumnSchema' => '/db/schema/sqlite/CSqliteColumnSchema.php',
		'CSqliteCommandBuilder' => '/db/schema/sqlite/CSqliteCommandBuilder.php',
		'CSqliteSchema' => '/db/schema/sqlite/CSqliteSchema.php',
		'CChoiceFormat' => '/i18n/CChoiceFormat.php',
		'CDateFormatter' => '/i18n/CDateFormatter.php',
		'CDbMessageSource' => '/i18n/CDbMessageSource.php',
		'CGettextMessageSource' => '/i18n/CGettextMessageSource.php',
		'CLocale' => '/i18n/CLocale.php',
		'CMessageSource' => '/i18n/CMessageSource.php',
		'CNumberFormatter' => '/i18n/CNumberFormatter.php',
		'CPhpMessageSource' => '/i18n/CPhpMessageSource.php',
		'CGettextFile' => '/i18n/gettext/CGettextFile.php',
		'CGettextMoFile' => '/i18n/gettext/CGettextMoFile.php',
		'CGettextPoFile' => '/i18n/gettext/CGettextPoFile.php',
		'CChainedLogFilter' => '/logging/CChainedLogFilter.php',
		'CDbLogRoute' => '/logging/CDbLogRoute.php',
		'CEmailLogRoute' => '/logging/CEmailLogRoute.php',
		'CFileLogRoute' => '/logging/CFileLogRoute.php',
		'CLogFilter' => '/logging/CLogFilter.php',
		'CLogRoute' => '/logging/CLogRoute.php',
		'CLogRouter' => '/logging/CLogRouter.php',
		'CLogger' => '/logging/CLogger.php',
		'CProfileLogRoute' => '/logging/CProfileLogRoute.php',
		'CWebLogRoute' => '/logging/CWebLogRoute.php',
		'CDateTimeParser' => '/utils/CDateTimeParser.php',
		'CFileHelper' => '/utils/CFileHelper.php',
		'CFormatter' => '/utils/CFormatter.php',
		'CMarkdownParser' => '/utils/CMarkdownParser.php',
		'CPropertyValue' => '/utils/CPropertyValue.php',
		'CTimestamp' => '/utils/CTimestamp.php',
		'CVarDumper' => '/utils/CVarDumper.php',
		'CBooleanValidator' => '/validators/CBooleanValidator.php',
		'CCaptchaValidator' => '/validators/CCaptchaValidator.php',
		'CCompareValidator' => '/validators/CCompareValidator.php',
		'CDateValidator' => '/validators/CDateValidator.php',
		'CDefaultValueValidator' => '/validators/CDefaultValueValidator.php',
		'CEmailValidator' => '/validators/CEmailValidator.php',
		'CExistValidator' => '/validators/CExistValidator.php',
		'CFileValidator' => '/validators/CFileValidator.php',
		'CFilterValidator' => '/validators/CFilterValidator.php',
		'CInlineValidator' => '/validators/CInlineValidator.php',
		'CNumberValidator' => '/validators/CNumberValidator.php',
		'CRangeValidator' => '/validators/CRangeValidator.php',
		'CRegularExpressionValidator' => '/validators/CRegularExpressionValidator.php',
		'CRequiredValidator' => '/validators/CRequiredValidator.php',
		'CSafeValidator' => '/validators/CSafeValidator.php',
		'CStringValidator' => '/validators/CStringValidator.php',
		'CTypeValidator' => '/validators/CTypeValidator.php',
		'CUniqueValidator' => '/validators/CUniqueValidator.php',
		'CUnsafeValidator' => '/validators/CUnsafeValidator.php',
		'CUrlValidator' => '/validators/CUrlValidator.php',
		'CValidator' => '/validators/CValidator.php',
		'CActiveDataProvider' => '/web/CActiveDataProvider.php',
		'CArrayDataProvider' => '/web/CArrayDataProvider.php',
		'CAssetManager' => '/web/CAssetManager.php',
		'CBaseController' => '/web/CBaseController.php',
		'CCacheHttpSession' => '/web/CCacheHttpSession.php',
		'CClientScript' => '/web/CClientScript.php',
		'CController' => '/web/CController.php',
		'CDataProvider' => '/web/CDataProvider.php',
		'CDataProviderIterator' => '/web/CDataProviderIterator.php',
		'CDbHttpSession' => '/web/CDbHttpSession.php',
		'CExtController' => '/web/CExtController.php',
		'CFormModel' => '/web/CFormModel.php',
		'CHttpCookie' => '/web/CHttpCookie.php',
		'CHttpRequest' => '/web/CHttpRequest.php',
		'CHttpSession' => '/web/CHttpSession.php',
		'CHttpSessionIterator' => '/web/CHttpSessionIterator.php',
		'COutputEvent' => '/web/COutputEvent.php',
		'CPagination' => '/web/CPagination.php',
		'CSort' => '/web/CSort.php',
		'CSqlDataProvider' => '/web/CSqlDataProvider.php',
		'CTheme' => '/web/CTheme.php',
		'CThemeManager' => '/web/CThemeManager.php',
		'CUploadedFile' => '/web/CUploadedFile.php',
		'CUrlManager' => '/web/CUrlManager.php',
		'CWebApplication' => '/web/CWebApplication.php',
		'CWebModule' => '/web/CWebModule.php',
		'CWidgetFactory' => '/web/CWidgetFactory.php',
		'CAction' => '/web/actions/CAction.php',
		'CInlineAction' => '/web/actions/CInlineAction.php',
		'CViewAction' => '/web/actions/CViewAction.php',
		'CAccessControlFilter' => '/web/auth/CAccessControlFilter.php',
		'CAuthAssignment' => '/web/auth/CAuthAssignment.php',
		'CAuthItem' => '/web/auth/CAuthItem.php',
		'CAuthManager' => '/web/auth/CAuthManager.php',
		'CBaseUserIdentity' => '/web/auth/CBaseUserIdentity.php',
		'CDbAuthManager' => '/web/auth/CDbAuthManager.php',
		'CPhpAuthManager' => '/web/auth/CPhpAuthManager.php',
		'CUserIdentity' => '/web/auth/CUserIdentity.php',
		'CWebUser' => '/web/auth/CWebUser.php',
		'CFilter' => '/web/filters/CFilter.php',
		'CFilterChain' => '/web/filters/CFilterChain.php',
		'CHttpCacheFilter' => '/web/filters/CHttpCacheFilter.php',
		'CInlineFilter' => '/web/filters/CInlineFilter.php',
		'CForm' => '/web/form/CForm.php',
		'CFormButtonElement' => '/web/form/CFormButtonElement.php',
		'CFormElement' => '/web/form/CFormElement.php',
		'CFormElementCollection' => '/web/form/CFormElementCollection.php',
		'CFormInputElement' => '/web/form/CFormInputElement.php',
		'CFormStringElement' => '/web/form/CFormStringElement.php',
		'CGoogleApi' => '/web/helpers/CGoogleApi.php',
		'CHtml' => '/web/helpers/CHtml.php',
		'CJSON' => '/web/helpers/CJSON.php',
		'CJavaScript' => '/web/helpers/CJavaScript.php',
		'CJavaScriptExpression' => '/web/helpers/CJavaScriptExpression.php',
		'CPradoViewRenderer' => '/web/renderers/CPradoViewRenderer.php',
		'CViewRenderer' => '/web/renderers/CViewRenderer.php',
		'CWebService' => '/web/services/CWebService.php',
		'CWebServiceAction' => '/web/services/CWebServiceAction.php',
		'CWsdlGenerator' => '/web/services/CWsdlGenerator.php',
		'CActiveForm' => '/web/widgets/CActiveForm.php',
		'CAutoComplete' => '/web/widgets/CAutoComplete.php',
		'CClipWidget' => '/web/widgets/CClipWidget.php',
		'CContentDecorator' => '/web/widgets/CContentDecorator.php',
		'CFilterWidget' => '/web/widgets/CFilterWidget.php',
		'CFlexWidget' => '/web/widgets/CFlexWidget.php',
		'CHtmlPurifier' => '/web/widgets/CHtmlPurifier.php',
		'CInputWidget' => '/web/widgets/CInputWidget.php',
		'CMarkdown' => '/web/widgets/CMarkdown.php',
		'CMaskedTextField' => '/web/widgets/CMaskedTextField.php',
		'CMultiFileUpload' => '/web/widgets/CMultiFileUpload.php',
		'COutputCache' => '/web/widgets/COutputCache.php',
		'COutputProcessor' => '/web/widgets/COutputProcessor.php',
		'CStarRating' => '/web/widgets/CStarRating.php',
		'CTabView' => '/web/widgets/CTabView.php',
		'CTextHighlighter' => '/web/widgets/CTextHighlighter.php',
		'CTreeView' => '/web/widgets/CTreeView.php',
		'CWidget' => '/web/widgets/CWidget.php',
		'CCaptcha' => '/web/widgets/captcha/CCaptcha.php',
		'CCaptchaAction' => '/web/widgets/captcha/CCaptchaAction.php',
		'CBasePager' => '/web/widgets/pagers/CBasePager.php',
		'CLinkPager' => '/web/widgets/pagers/CLinkPager.php',
		'CListPager' => '/web/widgets/pagers/CListPager.php',
	);
}

spl_autoload_register(array('YiiBase','autoload')); // 注册__autoload函数
require(YII_PATH.'/base/interfaces.php'); // 包含框架所需的所有接口类
