<?php
/**
 * CApplication class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CApplication is the base class for all application classes.
 *
 * An application serves as the global context that the user request
 * is being processed. It manages a set of application components that
 * provide specific functionalities to the whole application.
 *
 * The core application components provided by CApplication are the following:
 * <ul>
 * <li>{@link getErrorHandler errorHandler}: handles PHP errors and
 *   uncaught exceptions. This application component is dynamically loaded when needed.</li>
 * <li>{@link getSecurityManager securityManager}: provides security-related
 *   services, such as hashing, encryption. This application component is dynamically
 *   loaded when needed.</li>
 * <li>{@link getStatePersister statePersister}: provides global state
 *   persistence method. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCache cache}: provides caching feature. This application component is
 *   disabled by default.</li>
 * <li>{@link getMessages messages}: provides the message source for translating
 *   application messages. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCoreMessages coreMessages}: provides the message source for translating
 *   Yii framework messages. This application component is dynamically loaded when needed.</li>
 * <li>{@link getUrlManager urlManager}: provides URL construction as well as parsing functionality.
 *   This application component is dynamically loaded when needed.</li>
 * <li>{@link getRequest request}: represents the current HTTP request by encapsulating
 *   the $_SERVER variable and managing cookies sent from and sent to the user.
 *   This application component is dynamically loaded when needed.</li>
 * <li>{@link getFormat format}: provides a set of commonly used data formatting methods.
 *   This application component is dynamically loaded when needed.</li>
 * </ul>
 *
 * CApplication will undergo the following lifecycles when processing a user request:
 * <ol>
 * <li>load application configuration;</li>
 * <li>set up class autoloader and error handling;</li>
 * <li>load static application components;</li>
 * <li>{@link onBeginRequest}: preprocess the user request;</li>
 * <li>{@link processRequest}: process the user request;</li>
 * <li>{@link onEndRequest}: postprocess the user request;</li>
 * </ol>
 *
 * Starting from lifecycle 3, if a PHP error or an uncaught exception occurs,
 * the application will switch to its error handling logic and jump to step 6 afterwards.
 *
 * @property string $id The unique identifier for the application.
 * @property string $basePath The root directory of the application. Defaults to 'protected'.
 * @property string $runtimePath The directory that stores runtime files. Defaults to 'protected/runtime'.
 * @property string $extensionPath The directory that contains all extensions. Defaults to the 'extensions' directory under 'protected'.
 * @property string $language The language that the user is using and the application should be targeted to.
 * Defaults to the {@link sourceLanguage source language}.
 * @property string $timeZone The time zone used by this application.
 * @property CLocale $locale The locale instance.
 * @property string $localeDataPath The directory that contains the locale data. It defaults to 'framework/i18n/data'.
 * @property CNumberFormatter $numberFormatter The locale-dependent number formatter.
 * The current {@link getLocale application locale} will be used.
 * @property CDateFormatter $dateFormatter The locale-dependent date formatter.
 * The current {@link getLocale application locale} will be used.
 * @property CDbConnection $db The database connection.
 * @property CErrorHandler $errorHandler The error handler application component.
 * @property CSecurityManager $securityManager The security manager application component.
 * @property CStatePersister $statePersister The state persister application component.
 * @property CCache $cache The cache application component. Null if the component is not enabled.
 * @property CPhpMessageSource $coreMessages The core message translations.
 * @property CMessageSource $messages The application message translations.
 * @property CHttpRequest $request The request component.
 * @property CUrlManager $urlManager The URL manager component.
 * @property CController $controller The currently active controller. Null is returned in this base class.
 * @property string $baseUrl The relative URL for the application.
 * @property string $homeUrl The homepage URL.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// CApplication是所有应用的基类
abstract class CApplication extends CModule
{
	/**
	 * @var string the application name. Defaults to 'My Application'.
	 */
	public $name='My Application'; // 应用名称
	/**
	 * @var string the charset currently used for the application. Defaults to 'UTF-8'.
	 */
	public $charset='UTF-8'; // 应用使用的字符集
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are in. Defaults to 'en_us' (US English).
	 */
	public $sourceLanguage='en_us'; // 源语言

	private $_id; // 应用ID 应用程序的唯一标识符
	private $_basePath; // 应用程序的根目录
	private $_runtimePath; // 应用临时文件目录
	private $_extensionPath; // 应用扩展文件目录
	private $_globalState; // 保存应用中使用的全局值
	private $_stateChanged;
	private $_ended=false; // 标记应用程序是否已经结束了
	private $_language; // 应用语言
	private $_homeUrl; // 应用的首页地址

	/**
	 * Processes the request.
	 * This is the place where the actual request processing work is done.
	 * Derived classes should override this method.
	 */
	
	// 处理请求
	abstract public function processRequest();

	/**
	 * Constructor.
	 * @param mixed $config application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link getBasePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public function __construct($config=null)
	{
		Yii::setApplication($this);

		// set basePath at early as possible to avoid trouble
		if(is_string($config))
			$config=require($config);
		if(isset($config['basePath']))
		{
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		}
		else
			$this->setBasePath('protected');
		Yii::setPathOfAlias('application',$this->getBasePath()); // 应用基础目录
		Yii::setPathOfAlias('webroot',dirname($_SERVER['SCRIPT_FILENAME'])); // 入口文件所在目录
		Yii::setPathOfAlias('ext',$this->getBasePath().DIRECTORY_SEPARATOR.'extensions'); // 扩展文件目录

		$this->preinit(); // 预初始化 在配置这个模块之前做一些自己想做的事情

		$this->initSystemHandlers(); // 初始化错误处理程序
		$this->registerCoreComponents(); // 注册核心组件（不实例化）

		$this->configure($config); // 配置
		$this->attachBehaviors($this->behaviors); // 添加行为
		$this->preloadComponents(); // 加载需要预加载的一些组件

		$this->init();
	}


	/**
	 * Runs the application.
	 * This method loads static application components. Derived classes usually overrides this
	 * method to do more application-specific tasks.
	 * Remember to call the parent implementation so that static application components are loaded.
	 */
	
	// 运行应用程序
	// 这个方法加载静态应用组件。派生类通常覆盖这个方法来做更多的任务
	public function run()
	{
		if($this->hasEventHandler('onBeginRequest')) // 触发onBeginRequest事件
			$this->onBeginRequest(new CEvent($this));
		register_shutdown_function(array($this,'end'),0,false); // 注册终止程序
		$this->processRequest(); // 处理请求
		if($this->hasEventHandler('onEndRequest')) // 触发onEndRequest事件
			$this->onEndRequest(new CEvent($this));
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by calling
	 * {@link onEndRequest} before exiting.
	 * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param boolean $exit whether to exit the current request. This parameter has been available since version 1.1.5.
	 * It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
	 */
	
	// 终止应用程序
	// 这个方法取代PHP的exit()函数 其实不能是exit函数 应该说在程序结束后执行这个注册的程序
	// 这个方法保证无论怎么结束 都会触发onEndRequest事件
	public function end($status=0,$exit=true)
	{
		if($this->hasEventHandler('onEndRequest'))
			$this->onEndRequest(new CEvent($this));
		if($exit)
			exit($status);
	}

	/**
	 * Raised right BEFORE the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	
	// onBeginRequest事件 在处理应用程序之前
	public function onBeginRequest($event)
	{
		$this->raiseEvent('onBeginRequest',$event);
	}

	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	
	// onEndRequest事件 在处理应用程序之后
	public function onEndRequest($event)
	{
		if(!$this->_ended) // 应用程序是否结束
		{
			$this->_ended=true; // 标记应用程序已经结束
			$this->raiseEvent('onEndRequest',$event);
		}
	}

	/**
	 * Returns the unique identifier for the application.
	 * @return string the unique identifier for the application.
	 */
	
	// 返回应用的ID 唯一标识
	public function getId()
	{
		if($this->_id!==null)
			return $this->_id;
		else
			return $this->_id=sprintf('%x',crc32($this->getBasePath().$this->name));
	}

	/**
	 * Sets the unique identifier for the application.
	 * @param string $id the unique identifier for the application.
	 */
	
	// 设置应用的ID标识
	public function setId($id)
	{
		$this->_id=$id;
	}

	/**
	 * Returns the root path of the application.
	 * @return string the root directory of the application. Defaults to 'protected'.
	 */
	
	// 返回应用根目录 默认 protected
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the application.
	 * This method can only be invoked at the begin of the constructor.
	 * @param string $path the root directory of the application.
	 * @throws CException if the directory does not exist.
	 */
	
	// 设置应用根目录
	// 这个方法仅在初始化前调用 在 __construct 中调用
	public function setBasePath($path)
	{
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii','Application base path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
	 */
	
	// 返回保存存储临时文件的目录 默认 protected/runtime
	public function getRuntimePath()
	{
		if($this->_runtimePath!==null)
			return $this->_runtimePath;
		else
		{
			$this->setRuntimePath($this->getBasePath().DIRECTORY_SEPARATOR.'runtime');
			return $this->_runtimePath;
		}
	}

	/**
	 * Sets the directory that stores runtime files.
	 * @param string $path the directory that stores runtime files.
	 * @throws CException if the directory does not exist or is not writable
	 */
	
	// 设置保存存储临时文件的目录
	public function setRuntimePath($path)
	{
		if(($runtimePath=realpath($path))===false || !is_dir($runtimePath) || !is_writable($runtimePath))
			throw new CException(Yii::t('yii','Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.',
				array('{path}'=>$path)));
		$this->_runtimePath=$runtimePath;
	}

	/**
	 * Returns the root directory that holds all third-party extensions.
	 * @return string the directory that contains all extensions. Defaults to the 'extensions' directory under 'protected'.
	 */
	
	// 返回保存第三方扩展程序的目录
	public function getExtensionPath()
	{
		return Yii::getPathOfAlias('ext');
	}

	/**
	 * Sets the root directory that holds all third-party extensions.
	 * @param string $path the directory that contains all third-party extensions.
	 */
	
	// 设置保存第三方扩展程序的目录
	public function setExtensionPath($path)
	{
		if(($extensionPath=realpath($path))===false || !is_dir($extensionPath))
			throw new CException(Yii::t('yii','Extension path "{path}" does not exist.',
				array('{path}'=>$path)));
		Yii::setPathOfAlias('ext',$extensionPath);
	}

	/**
	 * Returns the language that the user is using and the application should be targeted to.
	 * @return string the language that the user is using and the application should be targeted to.
	 * Defaults to the {@link sourceLanguage source language}.
	 */
	
	// 返回目标语言 如果未定义 默认为源语言
	public function getLanguage()
	{
		return $this->_language===null ? $this->sourceLanguage : $this->_language;
	}

	/**
	 * Specifies which language the application is targeted to.
	 *
	 * This is the language that the application displays to end users.
	 * If set null, it uses the {@link sourceLanguage source language}.
	 *
	 * Unless your application needs to support multiple languages, you should always
	 * set this language to null to maximize the application's performance.
	 * @param string $language the user language (e.g. 'en_US', 'zh_CN').
	 * If it is null, the {@link sourceLanguage} will be used.
	 */
	
	// 设置目标语言
	public function setLanguage($language)
	{
		$this->_language=$language;
	}

	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	
	// 返回时区
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Sets the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_set().
	 * @param string $value the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	
	// 设置时区
	public function setTimeZone($value)
	{
		date_default_timezone_set($value);
	}

	/**
	 * Returns the localized version of a specified file.
	 *
	 * The searching is based on the specified language code. In particular,
	 * a file with the same name will be looked for under the subdirectory
	 * named as the locale ID. For example, given the file "path/to/view.php"
	 * and locale ID "zh_cn", the localized file will be looked for as
	 * "path/to/zh_cn/view.php". If the file is not found, the original file
	 * will be returned.
	 *
	 * For consistency, it is recommended that the locale ID is given
	 * in lower case and in the format of LanguageID_RegionID (e.g. "en_us").
	 *
	 * @param string $srcFile the original file
	 * @param string $srcLanguage the language that the original file is in. If null, the application {@link sourceLanguage source language} is used.
	 * @param string $language the desired language that the file should be localized to. If null, the {@link getLanguage application language} will be used.
	 * @return string the matching localized file. The original file is returned if no localized version is found
	 * or if source language is the same as the desired language.
	 */
	
	// 返回指定文件的本地化版本
	// 基于指定语言去搜索的。具有相同名称的文件将在以本地ID命名子目录下查找。
	// 例如。给定文件“path/to/view.php”和本地ID标识：“zh_cn”， 本地化文件将以这种形式查找“path/to/zh_cn/view.php”。 如果该文件没有找到， 将返回原始文件。 
	// $srcFile 原文件
	// $srcLanguage 原文件中的语言，如果为null，将使用source language。
	// $language 所需要的本地语言.如果为null, 将使用application language。
	public function findLocalizedFile($srcFile,$srcLanguage=null,$language=null)
	{
		if($srcLanguage===null)
			$srcLanguage=$this->sourceLanguage;
		if($language===null)
			$language=$this->getLanguage();
		if($language===$srcLanguage)
			return $srcFile;
		$desiredFile=dirname($srcFile).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.basename($srcFile);
		return is_file($desiredFile) ? $desiredFile : $srcFile;
	}

	/**
	 * Returns the locale instance.
	 * @param string $localeID the locale ID (e.g. en_US). If null, the {@link getLanguage application language ID} will be used.
	 * @return CLocale the locale instance
	 */
	
	// 返回本地（环境）实例
	// $localeID 本地（环境）实例ID（例如，en_US）。如果null，将使用application language ID。
	public function getLocale($localeID=null)
	{
		return CLocale::getInstance($localeID===null?$this->getLanguage():$localeID);
	}

	/**
	 * Returns the directory that contains the locale data.
	 * @return string the directory that contains the locale data. It defaults to 'framework/i18n/data'.
	 * @since 1.1.0
	 */
	
	// 返回包含本地化的数据目录
	// 默认是 framework/i18n/data
	public function getLocaleDataPath()
	{
		return CLocale::$dataPath===null ? Yii::getPathOfAlias('system.i18n.data') : CLocale::$dataPath;
	}

	/**
	 * Sets the directory that contains the locale data.
	 * @param string $value the directory that contains the locale data.
	 * @since 1.1.0
	 */
	
	// 设置包含本地化的数据目录
	public function setLocaleDataPath($value)
	{
		CLocale::$dataPath=$value;
	}

	/**
	 * @return CNumberFormatter the locale-dependent number formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	
	// 返回本地环境化的数字格式化程序
	public function getNumberFormatter()
	{
		return $this->getLocale()->getNumberFormatter();
	}

	/**
	 * Returns the locale-dependent date formatter.
	 * @return CDateFormatter the locale-dependent date formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	
	// 返回本地环境化的日期格式化程序
	public function getDateFormatter()
	{
		return $this->getLocale()->getDateFormatter();
	}

	/**
	 * Returns the database connection component.
	 * @return CDbConnection the database connection
	 */
	
	// 返回数据库连接组件
	public function getDb()
	{
		return $this->getComponent('db');
	}

	/**
	 * Returns the error handler component.
	 * @return CErrorHandler the error handler application component.
	 */
	
	// 返回错误处理程序组件
	public function getErrorHandler()
	{
		return $this->getComponent('errorHandler');
	}

	/**
	 * Returns the security manager component.
	 * @return CSecurityManager the security manager application component.
	 */
	
	// 返回安全管理器组件
	public function getSecurityManager()
	{
		return $this->getComponent('securityManager');
	}

	/**
	 * Returns the state persister component.
	 * @return CStatePersister the state persister application component.
	 */
	
	// 返回状态持续程序组件
	public function getStatePersister()
	{
		return $this->getComponent('statePersister');
	}

	/**
	 * Returns the cache component.
	 * @return CCache the cache application component. Null if the component is not enabled.
	 */
	
	// 返回缓存组件
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * Returns the core message translations component.
	 * @return CPhpMessageSource the core message translations
	 */
	
	// 返回核心信息翻译组件
	public function getCoreMessages()
	{
		return $this->getComponent('coreMessages');
	}

	/**
	 * Returns the application message translations component.
	 * @return CMessageSource the application message translations
	 */
	
	// 返回普通信息翻译组件。
	public function getMessages()
	{
		return $this->getComponent('messages');
	}

	/**
	 * Returns the request component.
	 * @return CHttpRequest the request component
	 */
	
	// 返回处理请求信息的组件
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the URL manager component.
	 * @return CUrlManager the URL manager component
	 */
	
	// 返回URL管理器组件
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
	}

	/**
	 * @return CController the currently active controller. Null is returned in this base class.
	 * @since 1.1.8
	 */
	
	// 返回当前活动控制器。为Null返回它的基类
	public function getController()
	{
		return null;
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	
	// 创建一个基于给定控制器和动作（信息）相对的URL。
	// $route URL路由。它的格式应该是：‘ControllerID/ActionID’。
	// $params 附加的GET参数（name=>value）。名称和值会被URL-encoded（URL编码）。
	// $ampersand URL中name-value对的分隔标记，默认是 & 符号
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		return $this->getUrlManager()->createUrl($route,$params,$ampersand);
	}

	/**
	 * Creates an absolute URL based on the given controller and action information.
	 * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	
	// 创建一个基于给定控制器和动作（信息）绝对的URL。
	public function createAbsoluteUrl($route,$params=array(),$schema='',$ampersand='&')
	{
		$url=$this->createUrl($route,$params,$ampersand);
		if(strpos($url,'http')===0)
			return $url;
		else
			return $this->getRequest()->getHostInfo($schema).$url;
	}

	/**
	 * Returns the relative URL for the application.
	 * This is a shortcut method to {@link CHttpRequest::getBaseUrl()}.
	 * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
	 * @return string the relative URL for the application
	 * @see CHttpRequest::getBaseUrl()
	 */
	
	// 返回应用程序的相对URL
	public function getBaseUrl($absolute=false)
	{
		return $this->getRequest()->getBaseUrl($absolute);
	}

	/**
	 * @return string the homepage URL
	 */
	
	// 返回主页URL
	public function getHomeUrl()
	{
		if($this->_homeUrl===null)
		{
			if($this->getUrlManager()->showScriptName)
				return $this->getRequest()->getScriptUrl();
			else
				return $this->getRequest()->getBaseUrl().'/';
		}
		else
			return $this->_homeUrl;
	}

	/**
	 * @param string $value the homepage URL
	 */
	
	// 设置主页URL
	public function setHomeUrl($value)
	{
		$this->_homeUrl=$value;
	}

	/**
	 * Returns a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * @param string $key the name of the value to be returned
	 * @param mixed $defaultValue the default value. If the named global value is not found, this will be returned instead.
	 * @return mixed the named global value
	 * @see setGlobalState
	 */
	
	// 返回一个全局值
	// 一个全局值，持续在用户的sessions与requests之间。
	// $key 要返回的值的名称
	// $defaultValue 默认值。如果该名称的全局值没有找到，将返回默认值。
	public function getGlobalState($key,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		if(isset($this->_globalState[$key]))
			return $this->_globalState[$key];
		else
			return $defaultValue;
	}

	/**
	 * Sets a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * Make sure that the value is serializable and unserializable.
	 * @param string $key the name of the value to be saved
	 * @param mixed $value the global value to be saved. It must be serializable.
	 * @param mixed $defaultValue the default value. If the named global value is the same as this value, it will be cleared from the current storage.
	 * @see getGlobalState
	 */
	
	// 设置一个全局值 注意：确保这个值可以序例化和反序例化
	// $key 名称
	// $value 值
	// $defaultValue 默认值。如果这个值与全局值相同，它将清除当前存储。
	public function setGlobalState($key,$value,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();

		$changed=$this->_stateChanged;
		if($value===$defaultValue)
		{
			if(isset($this->_globalState[$key]))
			{
				unset($this->_globalState[$key]);
				$this->_stateChanged=true;
			}
		}
		elseif(!isset($this->_globalState[$key]) || $this->_globalState[$key]!==$value)
		{
			$this->_globalState[$key]=$value;
			$this->_stateChanged=true;
		}

		if($this->_stateChanged!==$changed)
			$this->attachEventHandler('onEndRequest',array($this,'saveGlobalState'));
	}

	/**
	 * Clears a global value.
	 *
	 * The value cleared will no longer be available in this request and the following requests.
	 * @param string $key the name of the value to be cleared
	 */
	
	// 清除一个全局值
	// 被清除的值在这次请求或随后的请求中不可使用
	public function clearGlobalState($key)
	{
		$this->setGlobalState($key,true,true);
	}

	/**
	 * Loads the global state data from persistent storage.
	 * @see getStatePersister
	 * @throws CException if the state persister is not available
	 */
	
	// 从持久存储加载全局状态数据
	public function loadGlobalState()
	{
		$persister=$this->getStatePersister();
		if(($this->_globalState=$persister->load())===null)
			$this->_globalState=array();
		$this->_stateChanged=false;
		$this->detachEventHandler('onEndRequest',array($this,'saveGlobalState'));
	}

	/**
	 * Saves the global state data into persistent storage.
	 * @see getStatePersister
	 * @throws CException if the state persister is not available
	 */
	public function saveGlobalState()
	{
		if($this->_stateChanged)
		{
			$this->_stateChanged=false;
			$this->detachEventHandler('onEndRequest',array($this,'saveGlobalState'));
			$this->getStatePersister()->save($this->_globalState);
		}
	}

	/**
	 * Handles uncaught PHP exceptions.
	 *
	 * This method is implemented as a PHP exception handler. It requires
	 * that constant YII_ENABLE_EXCEPTION_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onException} event.
	 * If the exception is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the exception.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param Exception $exception exception that is not caught
	 */
	// 未捕获的异常处理
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors
		// 禁用错误捕获以免造成递归
		// 
		// 手册上说restore_error_handler在handler内部是不起作用的，这里为什么？？
		restore_error_handler();
		restore_exception_handler();

		$category='exception.'.get_class($exception);
		if($exception instanceof CHttpException)
			$category.='.'.$exception->statusCode;
		// php <5.2 doesn't support string conversion auto-magically
		$message=$exception->__toString();
		if(isset($_SERVER['REQUEST_URI']))
			$message.="\nREQUEST_URI=".$_SERVER['REQUEST_URI'];
		if(isset($_SERVER['HTTP_REFERER']))
			$message.="\nHTTP_REFERER=".$_SERVER['HTTP_REFERER'];
		$message.="\n---";
		Yii::log($message,CLogger::LEVEL_ERROR,$category);

		try
		{
			$event=new CExceptionEvent($this,$exception);
			$this->onException($event); // 发起异常事件
			if(!$event->handled) // 事件被执行完，没有被阻止
			{
				// try an error handler
				if(($handler=$this->getErrorHandler())!==null)
					$handler->handle($event);
				else
					$this->displayException($exception);
			}
		}
		catch(Exception $e)
		{
			$this->displayException($e);
		}

		try
		{
			$this->end(1);
		}
		catch(Exception $e)
		{
			// use the most primitive way to log error
			$msg = get_class($e).': '.$e->getMessage().' ('.$e->getFile().':'.$e->getLine().")\n";
			$msg .= $e->getTraceAsString()."\n";
			$msg .= "Previous exception:\n";
			$msg .= get_class($exception).': '.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().")\n";
			$msg .= $exception->getTraceAsString()."\n";
			$msg .= '$_SERVER='.var_export($_SERVER,true);
			error_log($msg);
			exit(1);
		}
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is implemented as a PHP error handler. It requires
	 * that constant YII_ENABLE_ERROR_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onError} event.
	 * If the error is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the error.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 */
	// （自定义的）处理处理
	public function handleError($code,$message,$file,$line)
	{
		if($code & error_reporting())
		{
			// disable error capturing to avoid recursive errors
			restore_error_handler();
			restore_exception_handler();

			$log="$message ($file:$line)\nStack trace:\n";
			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				$log.="#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					$log.=get_class($t['object']).'->';
				$log.="{$t['function']}()\n";
			}
			if(isset($_SERVER['REQUEST_URI']))
				$log.='REQUEST_URI='.$_SERVER['REQUEST_URI'];
			Yii::log($log,CLogger::LEVEL_ERROR,'php');

			try
			{
				Yii::import('CErrorEvent',true);
				$event=new CErrorEvent($this,$code,$message,$file,$line);
				$this->onError($event); // 发起错误事件
				if(!$event->handled)
				{
					// try an error handler
					if(($handler=$this->getErrorHandler())!==null)
						$handler->handle($event);
					else
						$this->displayError($code,$message,$file,$line);
				}
			}
			catch(Exception $e)
			{
				$this->displayException($e);
			}

			try
			{
				$this->end(1);
			}
			catch(Exception $e)
			{
				// use the most primitive way to log error
				$msg = get_class($e).': '.$e->getMessage().' ('.$e->getFile().':'.$e->getLine().")\n";
				$msg .= $e->getTraceAsString()."\n";
				$msg .= "Previous error:\n";
				$msg .= $log."\n";
				$msg .= '$_SERVER='.var_export($_SERVER,true);
				error_log($msg);
				exit(1);
			}
		}
	}

	/**
	 * Raised when an uncaught PHP exception occurs.
	 *
	 * An event handler can set the {@link CExceptionEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CExceptionEvent $event event parameter
	 */
	// 异常事件
	public function onException($event)
	{
		$this->raiseEvent('onException',$event);
	}

	/**
	 * Raised when a PHP execution error occurs.
	 *
	 * An event handler can set the {@link CErrorEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CErrorEvent $event event parameter
	 */
	// 错误事件
	public function onError($event)
	{
		$this->raiseEvent('onError',$event);
	}

	/**
	 * Displays the captured PHP error.
	 * This method displays the error in HTML when there is
	 * no active error handler.
	 * @param integer $code error code
	 * @param string $message error message
	 * @param string $file error file
	 * @param string $line error line
	 */
	// 显示捕获的php错误
	public function displayError($code,$message,$file,$line)
	{
		if(YII_DEBUG)
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';

			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				echo "{$t['function']}()\n";
			}

			echo '</pre>';
		}
		else
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message</p>\n";
		}
	}

	/**
	 * Displays the uncaught PHP exception.
	 * This method displays the exception in HTML when there is
	 * no active error handler.
	 * @param Exception $exception the uncaught exception
	 */
	// 显示未捕获的异常信息
	// 当没有设置errorHandler的时候，这个方法用以显示html格式的错误信息
	public function displayException($exception)
	{
		if(YII_DEBUG)
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
			echo '<pre>'.$exception->getTraceAsString().'</pre>';
		}
		else
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().'</p>';
		}
	}

	/**
	 * Initializes the class autoloader and error handlers.
	 */
	
	// 初始化错误处理程序
	protected function initSystemHandlers()
	{
		if(YII_ENABLE_EXCEPTION_HANDLER)
			set_exception_handler(array($this,'handleException')); // 异常处理
		if(YII_ENABLE_ERROR_HANDLER)
			set_error_handler(array($this,'handleError'),error_reporting()); // 错误处理
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	
	// 注册核心组件
	protected function registerCoreComponents()
	{
		$components=array(
			// 核心消息组件 仅框架自身使用 设置了翻译数据目录为框架下的messages目录
			'coreMessages'=>array(
				'class'=>'CPhpMessageSource',
				'language'=>'en_us',
				'basePath'=>YII_PATH.DIRECTORY_SEPARATOR.'messages',
			),
			// 数据库组件
			'db'=>array(
				'class'=>'CDbConnection',
			),
			// 消息组件
			'messages'=>array(
				'class'=>'CPhpMessageSource',
			),
			// 用来处理未捕获的PHP错误和异常
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
			),
			// 提供了私有密钥，哈希和加密功能
			'securityManager'=>array(
				'class'=>'CSecurityManager',
			),
			// 实现一个基于文件的持久数据存储
			'statePersister'=>array(
				'class'=>'CStatePersister',
			),
			// URL管理组件
			'urlManager'=>array(
				'class'=>'CUrlManager',
			),
			// 用于处理请求
			'request'=>array(
				'class'=>'CHttpRequest',
			),
			// 格式化
			'format'=>array(
				'class'=>'CFormatter',
			),
		);

		$this->setComponents($components);
	}
}
