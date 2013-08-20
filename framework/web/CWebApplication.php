<?php
/**
 * CWebApplication class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebApplication extends CApplication by providing functionalities specific to Web requests.
 *
 * CWebApplication manages the controllers in MVC pattern, and provides the following additional
 * core application components:
 * <ul>
 * <li>{@link urlManager}: provides URL parsing and constructing functionality;</li>
 * <li>{@link request}: encapsulates the Web request information;</li>
 * <li>{@link session}: provides the session-related functionalities;</li>
 * <li>{@link assetManager}: manages the publishing of private asset files.</li>
 * <li>{@link user}: represents the user session information.</li>
 * <li>{@link themeManager}: manages themes.</li>
 * <li>{@link authManager}: manages role-based access control (RBAC).</li>
 * <li>{@link clientScript}: manages client scripts (javascripts and CSS).</li>
 * <li>{@link widgetFactory}: creates widgets and supports widget skinning.</li>
 * </ul>
 *
 * User requests are resolved as controller-action pairs and additional parameters.
 * CWebApplication creates the requested controller instance and let it to handle
 * the actual user request. If the user does not specify controller ID, it will
 * assume {@link defaultController} is requested (which defaults to 'site').
 *
 * Controller class files must reside under the directory {@link getControllerPath controllerPath}
 * (defaults to 'protected/controllers'). The file name and the class name must be
 * the same as the controller ID with the first letter in upper case and appended with 'Controller'.
 * For example, the controller 'article' is defined by the class 'ArticleController'
 * which is in the file 'protected/controllers/ArticleController.php'.
 *
 * @property IAuthManager $authManager The authorization manager component.
 * @property CAssetManager $assetManager The asset manager component.
 * @property CHttpSession $session The session component.
 * @property CWebUser $user The user session information.
 * @property IViewRenderer $viewRenderer The view renderer.
 * @property CClientScript $clientScript The client script manager.
 * @property IWidgetFactory $widgetFactory The widget factory.
 * @property CThemeManager $themeManager The theme manager.
 * @property CTheme $theme The theme used currently. Null if no theme is being used.
 * @property CController $controller The currently active controller.
 * @property string $controllerPath The directory that contains the controller classes. Defaults to 'protected/controllers'.
 * @property string $viewPath The root directory of view files. Defaults to 'protected/views'.
 * @property string $systemViewPath The root directory of system view files. Defaults to 'protected/views/system'.
 * @property string $layoutPath The root directory of layout files. Defaults to 'protected/views/layouts'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CWebApplication extends CApplication
{
	/**
	 * @return string the route of the default controller, action or module. Defaults to 'site'.
	 */
	public $defaultController='site'; // 默认控制器
	/**
	 * @var mixed the application-wide layout. Defaults to 'main' (relative to {@link getLayoutPath layoutPath}).
	 * If this is false, then no layout will be used.
	 */
	public $layout='main'; // 默认布局
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Each name-value pair specifies the configuration for a single controller.
	 * A controller configuration can be either a string or an array.
	 * If the former, the string should be the class name or
	 * {@link YiiBase::getPathOfAlias class path alias} of the controller.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the controller's class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * The rest name-value pairs in the array are used to initialize
	 * the corresponding controller properties. For example,
	 * <pre>
	 * array(
	 *   'post'=>array(
	 *      'class'=>'path.to.PostController',
	 *      'pageTitle'=>'something new',
	 *   ),
	 *   'user'=>'path.to.UserController',,
	 * )
	 * </pre>
	 *
	 * Note, when processing an incoming request, the controller map will first be
	 * checked to see if the request can be handled by one of the controllers in the map.
	 * If not, a controller will be searched for under the {@link getControllerPath default controller path}.
	 */
	// 控制器映射
	public $controllerMap=array();
	/**
	 * @var array the configuration specifying a controller which should handle
	 * all user requests. This is mainly used when the application is in maintenance mode
	 * and we should use a controller to handle all incoming requests.
	 * The configuration specifies the controller route (the first element)
	 * and GET parameters (the rest name-value pairs). For example,
	 * <pre>
	 * array(
	 *     'offline/notice',
	 *     'param1'=>'value1',
	 *     'param2'=>'value2',
	 * )
	 * </pre>
	 * Defaults to null, meaning catch-all is not effective.
	 */
	
	// 配置指定的控制器来处理用户的所有请求
	// 这主要用在当应用处于维护时，我们定义一个控制器来处理所有用户请求，比如显示在维护中
	public $catchAllRequest;

	/**
	 * @var string Namespace that should be used when loading controllers.
	 * Default is to use global namespace.
	 * @since 1.1.11
	 */
	// 控制器的命名空间
	public $controllerNamespace;

	private $_controllerPath; // 控制器的目录
	private $_viewPath; // 视图目录
	private $_systemViewPath; // 系统视图目录
	private $_layoutPath; // 布局目录
	private $_controller; // 控制器
	private $_theme; // 主题


	/**
	 * Processes the current request.
	 * It first resolves the request into controller and action,
	 * and then creates the controller to perform the action.
	 */
	
	// 处理请求
	public function processRequest()
	{
		// 先判断是否启用了维护模式
		if(is_array($this->catchAllRequest) && isset($this->catchAllRequest[0]))
		{
			$route=$this->catchAllRequest[0];
			foreach(array_splice($this->catchAllRequest,1) as $name=>$value)
				$_GET[$name]=$value;
		}
		else
			$route=$this->getUrlManager()->parseUrl($this->getRequest());

		// 控制器跑起来
		$this->runController($route);
	}

	/**
	 * Registers the core application components.
	 * This method overrides the parent implementation by registering additional core components.
	 * @see setComponents
	 */
	
	// 注册核心应用组件
	protected function registerCoreComponents()
	{
		parent::registerCoreComponents();

		$components=array(
			'session'=>array(
				'class'=>'CHttpSession',
			),
			'assetManager'=>array(
				'class'=>'CAssetManager',
			),
			'user'=>array(
				'class'=>'CWebUser',
			),
			'themeManager'=>array(
				'class'=>'CThemeManager',
			),
			'authManager'=>array(
				'class'=>'CPhpAuthManager',
			),
			'clientScript'=>array(
				'class'=>'CClientScript',
			),
			'widgetFactory'=>array(
				'class'=>'CWidgetFactory',
			),
		);

		$this->setComponents($components);
	}

	/**
	 * @return IAuthManager the authorization manager component
	 */
	
	// 返回授权管理组件
	public function getAuthManager()
	{
		return $this->getComponent('authManager');
	}

	/**
	 * @return CAssetManager the asset manager component
	 */
	
	// 返回asset文件管理组件
	public function getAssetManager()
	{
		return $this->getComponent('assetManager');
	}

	/**
	 * @return CHttpSession the session component
	 */
	
	// 返回session组件
	public function getSession()
	{
		return $this->getComponent('session');
	}

	/**
	 * @return CWebUser the user session information
	 */
	
	// 返回用户组件
	public function getUser()
	{
		return $this->getComponent('user');
	}

	/**
	 * Returns the view renderer.
	 * If this component is registered and enabled, the default
	 * view rendering logic defined in {@link CBaseController} will
	 * be replaced by this renderer.
	 * @return IViewRenderer the view renderer.
	 */
	
	// 返回视图渲染组件（就好像smarty）
	public function getViewRenderer()
	{
		return $this->getComponent('viewRenderer');
	}

	/**
	 * Returns the client script manager.
	 * @return CClientScript the client script manager
	 */
	
	// 返回客户端脚本管理组件
	public function getClientScript()
	{
		return $this->getComponent('clientScript');
	}

	/**
	 * Returns the widget factory.
	 * @return IWidgetFactory the widget factory
	 * @since 1.1
	 */
	
	// 返回部件工厂组件
	public function getWidgetFactory()
	{
		return $this->getComponent('widgetFactory');
	}

	/**
	 * @return CThemeManager the theme manager.
	 */
	
	// 返回主题管理组件
	public function getThemeManager()
	{
		return $this->getComponent('themeManager');
	}

	/**
	 * @return CTheme the theme used currently. Null if no theme is being used.
	 */
	
	// 返回当前主题（CTheme对象）
	public function getTheme()
	{
		if(is_string($this->_theme))
			$this->_theme=$this->getThemeManager()->getTheme($this->_theme);
		return $this->_theme;
	}

	/**
	 * @param string $value the theme name
	 */
	
	// 设置主题
	public function setTheme($value)
	{
		$this->_theme=$value;
	}

	/**
	 * Creates the controller and performs the specified action.
	 * @param string $route the route of the current request. See {@link createController} for more details.
	 * @throws CHttpException if the controller could not be created.
	 */
	
	// 创建控制器和执行指定的动作
	public function runController($route)
	{
		if(($ca=$this->createController($route))!==null)
		{
			// $ca是一个数组，array(控制器对象，动作名称)
			list($controller,$actionID)=$ca;
			$oldController=$this->_controller;
			$this->_controller=$controller;
			$controller->init();
			$controller->run($actionID);
			$this->_controller=$oldController;
		}
		else
			throw new CHttpException(404,Yii::t('yii','Unable to resolve the request "{route}".',
				array('{route}'=>$route===''?$this->defaultController:$route)));
	}

	/**
	 * Creates a controller instance based on a route.
	 * The route should contain the controller ID and the action ID.
	 * It may also contain additional GET variables. All these must be concatenated together with slashes.
	 *
	 * This method will attempt to create a controller in the following order:
	 * <ol>
	 * <li>If the first segment is found in {@link controllerMap}, the corresponding
	 * controller configuration will be used to create the controller;</li>
	 * <li>If the first segment is found to be a module ID, the corresponding module
	 * will be used to create the controller;</li>
	 * <li>Otherwise, it will search under the {@link controllerPath} to create
	 * the corresponding controller. For example, if the route is "admin/user/create",
	 * then the controller will be created using the class file "protected/controllers/admin/UserController.php".</li>
	 * </ol>
	 * @param string $route the route of the request.
	 * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
	 * instance is the owner.
	 * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
	 */
	
	// 根据指定的路由信息创建控制器
	// route必须包含控制器和动作，也可以包含a=123&b=456类似的请求信息
	// 这个方法将以下面的顺序创建一个控制器
	// 1、如果第一个片段值在controllerMap变量中找到，相应的配置信息将用来创建这个控制器
	// 2、如果第一个片段值可以找到个模块，那么相应的模块配置信息将用来创建这个控制器
	// 3、否则就就会查找controllerPath（控制器目录），假设route = admin/user/create 那么就会查找protected/controllers/admin/UserController.php这个文件
	public function createController($route,$owner=null)
	{
		if($owner===null)
			$owner=$this;
		// 如果没有路由，设置为默认控制器
		if(($route=trim($route,'/'))==='')
			$route=$owner->defaultController;

		$caseSensitive=$this->getUrlManager()->caseSensitive; // 是否区分大小写

		$route.='/'; // 例如 site/index
		while(($pos=strpos($route,'/'))!==false)
		{
			$id=substr($route,0,$pos);
			if(!preg_match('/^\w+$/',$id))
				return null;
			if(!$caseSensitive)
				$id=strtolower($id);
			$route=(string)substr($route,$pos+1);

			// 如果是第一次（循环到）
			if(!isset($basePath))  // first segment
			{
				// 如果在控制器映射表中找到
				if(isset($owner->controllerMap[$id]))
				{
					return array(
						Yii::createComponent($owner->controllerMap[$id],$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}

				// 如果是个模块
				if(($module=$owner->getModule($id))!==null)
					return $this->createController($route,$module);

				$basePath=$owner->getControllerPath(); // 控制器存放路径
				$controllerID='';
			}
			else
				$controllerID.='/';
			$className=ucfirst($id).'Controller';
			$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';

			if($owner->controllerNamespace!==null)
				$className=$owner->controllerNamespace.'\\'.$className;

			if(is_file($classFile))
			{
				if(!class_exists($className,false))
					require($classFile);
				if(class_exists($className,false) && is_subclass_of($className,'CController'))
				{
					$id[0]=strtolower($id[0]);
					return array(
						new $className($controllerID.$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}
				return null;
			}

			// 更深层次的控制器路径
			// 例如路由 abc/site/index 在 protected/controllers/abc目录下有个site控制器
			$controllerID.=$id;
			$basePath.=DIRECTORY_SEPARATOR.$id;
		}
	}

	/**
	 * Parses a path info into an action ID and GET variables.
	 * @param string $pathInfo path info
	 * @return string action ID
	 */
	
	// 解析一个路径信息到一个动作ID，获取$_GET变量
	protected function parseActionParams($pathInfo)
	{
		if(($pos=strpos($pathInfo,'/'))!==false)
		{
			$manager=$this->getUrlManager();
			$manager->parsePathInfo((string)substr($pathInfo,$pos+1));
			$actionID=substr($pathInfo,0,$pos);
			return $manager->caseSensitive ? $actionID : strtolower($actionID);
		}
		else
			return $pathInfo;
	}

	/**
	 * @return CController the currently active controller
	 */
	
	// 返回当前控制器
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * @param CController $value the currently active controller
	 */
	
	// 设置当前控制器
	public function setController($value)
	{
		$this->_controller=$value;
	}

	/**
	 * @return string the directory that contains the controller classes. Defaults to 'protected/controllers'.
	 */
	
	// 返回存放控制器文件的目录 默认 protected/controllers
	public function getControllerPath()
	{
		if($this->_controllerPath!==null)
			return $this->_controllerPath;
		else
			return $this->_controllerPath=$this->getBasePath().DIRECTORY_SEPARATOR.'controllers';
	}

	/**
	 * @param string $value the directory that contains the controller classes.
	 * @throws CException if the directory is invalid
	 */
	
	// 设置存放控制器文件的目录
	public function setControllerPath($value)
	{
		if(($this->_controllerPath=realpath($value))===false || !is_dir($this->_controllerPath))
			throw new CException(Yii::t('yii','The controller path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string the root directory of view files. Defaults to 'protected/views'.
	 */
	
	// 返回存放视图文件的目录 默认 protected/views
	public function getViewPath()
	{
		if($this->_viewPath!==null)
			return $this->_viewPath;
		else
			return $this->_viewPath=$this->getBasePath().DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @param string $path the root directory of view files.
	 * @throws CException if the directory does not exist.
	 */
	
	// 设置存放视图文件的目录
	public function setViewPath($path)
	{
		if(($this->_viewPath=realpath($path))===false || !is_dir($this->_viewPath))
			throw new CException(Yii::t('yii','The view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of system view files. Defaults to 'protected/views/system'.
	 */
	
	// 返回存放系统视图文件的目录 默认 protected/views/system
	public function getSystemViewPath()
	{
		if($this->_systemViewPath!==null)
			return $this->_systemViewPath;
		else
			return $this->_systemViewPath=$this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * @param string $path the root directory of system view files.
	 * @throws CException if the directory does not exist.
	 */
	
	// 设置存放系统视图文件的目录
	public function setSystemViewPath($path)
	{
		if(($this->_systemViewPath=realpath($path))===false || !is_dir($this->_systemViewPath))
			throw new CException(Yii::t('yii','The system view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of layout files. Defaults to 'protected/views/layouts'.
	 */
	
	// 返回存放布局文件的目录 默认 protected/views/layouts
	public function getLayoutPath()
	{
		if($this->_layoutPath!==null)
			return $this->_layoutPath;
		else
			return $this->_layoutPath=$this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * @param string $path the root directory of layout files.
	 * @throws CException if the directory does not exist.
	 */
	
	// 设置存放布局文件的目录
	public function setLayoutPath($path)
	{
		if(($this->_layoutPath=realpath($path))===false || !is_dir($this->_layoutPath))
			throw new CException(Yii::t('yii','The layout path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action and all its filters
	 * are executed. You may override this method with logic that needs to be done
	 * before all controller actions.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @return boolean whether the action should be executed.
	 */
	
	// 控制器操作的前置过滤器
	// 这个方法将在动作之前所有过滤执行完后调用
	// 你可以覆盖次方法来做写自己想要的处理
	public function beforeControllerAction($controller,$action)
	{
		return true;
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action and all its filters
	 * are executed. You may override this method with logic that needs to be done
	 * after all controller actions.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 */
	
	// 控制器操作的后置过滤器
	// 这个方法在控制器动作和所有过滤器被执行后调用
	public function afterControllerAction($controller,$action)
	{
	}

	/**
	 * Do not call this method. This method is used internally to search for a module by its ID.
	 * @param string $id module ID
	 * @return CWebModule the module that has the specified ID. Null if no module is found.
	 */
	
	// 不要调用这个方法 这个方法是内部调用，用来查找指定id指定模块
	public function findModule($id)
	{
		if(($controller=$this->getController())!==null && ($module=$controller->getModule())!==null)
		{
			do
			{
				if(($m=$module->getModule($id))!==null)
					return $m;
			} while(($module=$module->getParentModule())!==null);
		}
		if(($m=$this->getModule($id))!==null)
			return $m;
	}

	/**
	 * Initializes the application.
	 * This method overrides the parent implementation by preloading the 'request' component.
	 */
	protected function init()
	{
		parent::init();
		// preload 'request' so that it has chance to respond to onBeginRequest event.
		// 预先即在request组件，以用在onBeginRequest事件中
		$this->getRequest();
	}
}
