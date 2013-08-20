<?php
/**
 * CUrlManager class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUrlManager manages the URLs of Yii Web applications.
 *
 * It provides URL construction ({@link createUrl()}) as well as parsing ({@link parseUrl()}) functionality.
 *
 * URLs managed via CUrlManager can be in one of the following two formats,
 * by setting {@link setUrlFormat urlFormat} property:
 * <ul>
 * <li>'path' format: /path/to/EntryScript.php/name1/value1/name2/value2...</li>
 * <li>'get' format:  /path/to/EntryScript.php?name1=value1&name2=value2...</li>
 * </ul>
 *
 * When using 'path' format, CUrlManager uses a set of {@link setRules rules} to:
 * <ul>
 * <li>parse the requested URL into a route ('ControllerID/ActionID') and GET parameters;</li>
 * <li>create URLs based on the given route and GET parameters.</li>
 * </ul>
 *
 * A rule consists of a route and a pattern. The latter is used by CUrlManager to determine
 * which rule is used for parsing/creating URLs. A pattern is meant to match the path info
 * part of a URL. It may contain named parameters using the syntax '&lt;ParamName:RegExp&gt;'.
 *
 * When parsing a URL, a matching rule will extract the named parameters from the path info
 * and put them into the $_GET variable; when creating a URL, a matching rule will extract
 * the named parameters from $_GET and put them into the path info part of the created URL.
 *
 * If a pattern ends with '/*', it means additional GET parameters may be appended to the path
 * info part of the URL; otherwise, the GET parameters can only appear in the query string part.
 *
 * To specify URL rules, set the {@link setRules rules} property as an array of rules (pattern=>route).
 * For example,
 * <pre>
 * array(
 *     'articles'=>'article/list',
 *     'article/<id:\d+>/*'=>'article/read',
 * )
 * </pre>
 * Two rules are specified in the above:
 * <ul>
 * <li>The first rule says that if the user requests the URL '/path/to/index.php/articles',
 *   it should be treated as '/path/to/index.php/article/list'; and vice versa applies
 *   when constructing such a URL.</li>
 * <li>The second rule contains a named parameter 'id' which is specified using
 *   the &lt;ParamName:RegExp&gt; syntax. It says that if the user requests the URL
 *   '/path/to/index.php/article/13', it should be treated as '/path/to/index.php/article/read?id=13';
 *   and vice versa applies when constructing such a URL.</li>
 * </ul>
 *
 * The route part may contain references to named parameters defined in the pattern part.
 * This allows a rule to be applied to different routes based on matching criteria.
 * For example,
 * <pre>
 * array(
 *      '<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>'=>'<_c>/<_a>',
 *      '<_c:(post|comment)>/<id:\d+>'=>'<_c>/view',
 *      '<_c:(post|comment)>s/*'=>'<_c>/list',
 * )
 * </pre>
 * In the above, we use two named parameters '<_c>' and '<_a>' in the route part. The '<_c>'
 * parameter matches either 'post' or 'comment', while the '<_a>' parameter matches an action ID.
 *
 * Like normal rules, these rules can be used for both parsing and creating URLs.
 * For example, using the rules above, the URL '/index.php/post/123/create'
 * would be parsed as the route 'post/create' with GET parameter 'id' being 123.
 * And given the route 'post/list' and GET parameter 'page' being 2, we should get a URL
 * '/index.php/posts/page/2'.
 *
 * It is also possible to include hostname into the rules for parsing and creating URLs.
 * One may extract part of the hostname to be a GET parameter.
 * For example, the URL <code>http://admin.example.com/en/profile</code> may be parsed into GET parameters
 * <code>user=admin</code> and <code>lang=en</code>. On the other hand, rules with hostname may also be used to
 * create URLs with parameterized hostnames.
 *
 * In order to use parameterized hostnames, simply declare URL rules with host info, e.g.:
 * <pre>
 * array(
 *     'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
 * )
 * </pre>
 *
 * Starting from version 1.1.8, one can write custom URL rule classes and use them for one or several URL rules.
 * For example,
 * <pre>
 * array(
 *   // a standard rule
 *   '<action:(login|logout)>' => 'site/<action>',
 *   // a custom rule using data in DB
 *   array(
 *     'class' => 'application.components.MyUrlRule',
 *     'connectionID' => 'db',
 *   ),
 * )
 * </pre>
 * Please note that the custom URL rule class should extend from {@link CBaseUrlRule} and
 * implement the following two methods,
 * <ul>
 *    <li>{@link CBaseUrlRule::createUrl()}</li>
 *    <li>{@link CBaseUrlRule::parseUrl()}</li>
 * </ul>
 *
 * CUrlManager is a default application component that may be accessed via
 * {@link CWebApplication::getUrlManager()}.
 *
 * @property string $baseUrl The base URL of the application (the part after host name and before query string).
 * If {@link showScriptName} is true, it will include the script name part.
 * Otherwise, it will not, and the ending slashes are stripped off.
 * @property string $urlFormat The URL format. Defaults to 'path'. Valid values include 'path' and 'get'.
 * Please refer to the guide for more details about the difference between these two formats.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */

// URL管理器
class CUrlManager extends CApplicationComponent
{
	// 记录规则信息到缓存中使用的key
	// 为什么需要缓存，就是为了速度
	// 首先我们要知道main.php中定义的rules会在CUrlManager的processRules方法中被依次用来创建CUrlRule对象
	// 如果每次都要创建规则对象，显然效率低了，所以这里引入了缓存
	const CACHE_KEY='Yii.CUrlManager.rules';
	const GET_FORMAT='get';
	const PATH_FORMAT='path';

	/**
	 * @var array the URL rules (pattern=>route).
	 */
	public $rules=array(); // url规则 有模式->路由 组成的键值对数组
	/**
	 * @var string the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Defaults to empty.
	 */
	public $urlSuffix=''; // URL后缀
	/**
	 * @var boolean whether to show entry script name in the constructed URL. Defaults to true.
	 */
	public $showScriptName=true; // 在构造url的时候是否显示脚本名称 比如index.php这个咯
	/**
	 * @var boolean whether to append GET parameters to the path info part. Defaults to true.
	 * This property is only effective when {@link urlFormat} is 'path' and is mainly used when
	 * creating URLs. When it is true, GET parameters will be appended to the path info and
	 * separate from each other using slashes. If this is false, GET parameters will be in query part.
	 */
	
	// 是否将GET参数附加到路径里
	// 这个属性只当urlFormat等于path时有效，主要用于创建URL
	// 当这个值为true时，GET参数附加到路径信息，并使用斜线分开
	// 如果为false, GET参数将以query string显示
	public $appendParams=true;
	/**
	 * @var string the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar='r'; // GET数组中表示路由的变量名
	/**
	 * @var boolean whether routes are case-sensitive. Defaults to true. By setting this to false,
	 * the route in the incoming request will be turned to lower case first before further processing.
	 * As a result, you should follow the convention that you use lower case when specifying
	 * controller mapping ({@link CWebApplication::controllerMap}) and action mapping
	 * ({@link CController::actions}). Also, the directory names for organizing controllers should
	 * be in lower case.
	 */
	public $caseSensitive=true; // 路由是否区分大小写
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in a rule before using it to create a URL. Defaults to false, meaning
	 * a rule will be used for creating a URL only if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	
	// 当创建url时，
	public $matchValue=false;
	/**
	 * @var string the ID of the cache application component that is used to cache the parsed URL rules.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching URL rules.
	 */
	
	// 缓存组件的ID值 用于缓存url规则
	public $cacheID='cache';
	/**
	 * @var boolean whether to enable strict URL parsing.
	 * This property is only effective when {@link urlFormat} is 'path'.
	 * If it is set true, then an incoming URL must match one of the {@link rules URL rules}.
	 * Otherwise, it will be treated as an invalid request and trigger a 404 HTTP exception.
	 * Defaults to false.
	 */
	
	// 是否启用严格的URL解析
	// 仅仅当urlFormat等于path时有效
	// 如果为true，传入的url必须匹配一个url规则，否则会提示404错误
	public $useStrictParsing=false;
	/**
	 * @var string the class name or path alias for the URL rule instances. Defaults to 'CUrlRule'.
	 * If you change this to something else, please make sure that the new class must extend from
	 * {@link CBaseUrlRule} and have the same constructor signature as {@link CUrlRule}.
	 * It must also be serializable and autoloadable.
	 * @since 1.1.8
	 */
	
	// URL规则实例的类名或路径别名 一般不需要改变
	// 如果要制定为自定义的 那么你的类必须继承CBaseUrlRule，和实现CUrlRule一样的构造签名 还必须可以序列化（因为要存储到缓存中）
	public $urlRuleClass='CUrlRule';

	// url如何格式化，这里默认使用get，如果我们需要用path模式，我们需在main.php配置中，配置urlManager的时候，指定urlFormat=>path
	private $_urlFormat=self::GET_FORMAT;
	private $_rules=array(); // 保存CUrlRule对象
	private $_baseUrl; // 应用程序的基URL


	/**
	 * Initializes the application component.
	 */
	
	// 初始化这个应用组件
	public function init()
	{
		parent::init();
		$this->processRules(); // 处理url规则
	}

	/**
	 * Processes the URL rules.
	 */
	
	// 处理url规则
	// 将rules规则实例化为CUrlRule对象
	protected function processRules()
	{
		// 这里判断规则必须有 而且必须指定了path模式 否则不做处理
		// 也就是说只有在path模式下，路由规则才会启作用
		if(empty($this->rules) || $this->getUrlFormat()===self::GET_FORMAT)
			return;

		// 如果配置了缓存组件
		// CUrlManager默认指定缓存组件ID名称为cache
		if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$hash=md5(serialize($this->rules)); // 计算路由规则hash值，以便确定缓存的数据是否失效
			// 从缓存取出 如缓存存在 判断hash值是否改变
			if(($data=$cache->get(self::CACHE_KEY))!==false && isset($data[1]) && $data[1]===$hash)
			{
				$this->_rules=$data[0]; // 从缓存中取出数据
				return;
			}
		}

		// 使用每个规则创建CUrlRule对象 对象保存在$this->_rules数组中
		foreach($this->rules as $pattern=>$route)
			$this->_rules[]=$this->createUrlRule($route,$pattern);

		// 将规则写入缓存
		if(isset($cache))
			$cache->set(self::CACHE_KEY,array($this->_rules,$hash));
	}

	/**
	 * Adds new URL rules.
	 * In order to make the new rules effective, this method must be called BEFORE
	 * {@link CWebApplication::processRequest}.
	 * @param array $rules new URL rules (pattern=>route).
	 * @param boolean $append whether the new URL rules should be appended to the existing ones. If false,
	 * they will be inserted at the beginning.
	 * @since 1.1.4
	 */
	
	// 添加一个新的url规则
	// 为了是新添加的规则有效，这个方法必须在CWebApplication::processRequest前调用
	// $rules url规则 模式-》路由 键值对 数组
	// $append 表示新的规则是否要追加到现有规则后面 如果为false则添加到前面
	public function addRules($rules,$append=true)
	{
		if ($append) // 添加到后面
		{
			foreach($rules as $pattern=>$route)
				$this->_rules[]=$this->createUrlRule($route,$pattern);
		}
		else
		{
			$rules=array_reverse($rules); // 返回倒序的数组
			foreach($rules as $pattern=>$route)
				array_unshift($this->_rules, $this->createUrlRule($route,$pattern));
		}
	}

	/**
	 * Creates a URL rule instance.
	 * The default implementation returns a CUrlRule object.
	 * @param mixed $route the route part of the rule. This could be a string or an array
	 * @param string $pattern the pattern part of the rule
	 * @return CUrlRule the URL rule instance
	 * @since 1.1.0
	 */
	
	// 创建一个URL规则实例
	// 默认的实现返回一个CUrlRule对象
	protected function createUrlRule($route,$pattern)
	{
		// 如果是个数组，并且存在class字段，我们认为这个规则对象 是外部提供的一个类所支持的
		// 在遍历$this->_rules的时候，我们看到是个数组，我们会根据这个数组创建组件对象
		if(is_array($route) && isset($route['class']))
			return $route;
		else
			return new $this->urlRuleClass($route,$pattern); // urlRuleClass 是变量 就是CUrlRule
	}

	/**
	 * Constructs a URL.
	 * @param string $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * If the name is '#', the corresponding value will be treated as an anchor
	 * and will be appended at the end of the URL.
	 * @param string $ampersand the token separating name-value pairs in the URL. Defaults to '&'.
	 * @return string the constructed URL
	 */
	
	// 构造一个url
	// $route 路由 例如 article/read
	// $params 添加的请求参数 如果名称为#则为锚
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		// 从$params中删除掉变量名为r的值 r默认是保存路由信息的 如 r=article/update
		unset($params[$this->routeVar]);

		// 将null值的都同意为 '' 空字符串
		foreach($params as $i=>$param)
			if($param===null)
				$params[$i]='';

		// 确定锚链接，如果在$params中存在#字段，那么就是他了，默认为 '' 空字符串
		if(isset($params['#']))
		{
			$anchor='#'.$params['#'];
			unset($params['#']);
		}
		else
			$anchor='';

		$route=trim($route,'/'); // 取出路由左右的 / 字符，这个字符是多余的

		// 遍历规则对象 看是否那个对象适用于创建url
		foreach($this->_rules as $i=>$rule)
		{
			// $rule是个数组 便认为是个外部组件提供的规则对象
			if(is_array($rule))
				$this->_rules[$i]=$rule=Yii::createComponent($rule);

			// 如果这个rule对象创建出了url
			if(($url=$rule->createUrl($this,$route,$params,$ampersand))!==false)
			{
				// 如果需要添加主机信息 就是 http / https
				if($rule->hasHostInfo)
					return $url==='' ? '/'.$anchor : $url.$anchor;
				else
					return $this->getBaseUrl().'/'.$url.$anchor;
			}
		}

		// 没有rule对象适合的话，直接根据参数创建url
		return $this->createUrlDefault($route,$params,$ampersand).$anchor;
	}

	/**
	 * Creates a URL based on default settings.
	 * @param string $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	
	// 基于默认设置创建一个url
	// $route 路由 例如 article/read
	// $params 添加的附加请求参数
	// $ampersand 请求字符串的分隔符 举例 name=a&age=10 中的 & 符号
	protected function createUrlDefault($route,$params,$ampersand)
	{
		// 如果是path模式
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$url=rtrim($this->getBaseUrl().'/'.$route,'/');

			// 如果需要将$_GET里的数据添加到url路径
			// 比如$_GET = array('a' => 123, 'b' => '456');
			// 会将/a/123/b/456添加到url路径后面
			// 否则会以?a=123&b=456形势添加
			if($this->appendParams)
			{
				$url=rtrim($url.'/'.$this->createPathInfo($params,'/','/'),'/');
				return $route==='' ? $url : $url.$this->urlSuffix;
			}
			else
			{
				if($route!=='')
					$url.=$this->urlSuffix;
				$query=$this->createPathInfo($params,'=',$ampersand);
				return $query==='' ? $url : $url.'?'.$query;
			}
		}
		// get模式
		else
		{
			$url=$this->getBaseUrl();
			if(!$this->showScriptName)
				$url.='/';
			if($route!=='')
			{
				$url.='?'.$this->routeVar.'='.$route;
				if(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
					$url.=$ampersand.$query;
			}
			elseif(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
				$url.='?'.$query;
			return $url;
		}
	}

	/**
	 * Parses the user request.
	 * @param CHttpRequest $request the request application component
	 * @return string the route (controllerID/actionID) and perhaps GET parameters in path format.
	 */
	
	// 解析用户请求
	// $request CHttpRequest请求对象
	public function parseUrl($request)
	{
		// 如果是path模式
		if($this->getUrlFormat()===self::PATH_FORMAT)
		{
			$rawPathInfo=$request->getPathInfo(); // path部分
			$pathInfo=$this->removeUrlSuffix($rawPathInfo,$this->urlSuffix); // 去除url后缀
			foreach($this->_rules as $i=>$rule)
			{
				if(is_array($rule))
					$this->_rules[$i]=$rule=Yii::createComponent($rule);
				if(($r=$rule->parseUrl($this,$request,$pathInfo,$rawPathInfo))!==false)
					return isset($_GET[$this->routeVar]) ? $_GET[$this->routeVar] : $r;
			}

			// 如果必须匹配一个url规则
			if($this->useStrictParsing)
				throw new CHttpException(404,Yii::t('yii','Unable to resolve the request "{route}".',
					array('{route}'=>$pathInfo)));
			else
				return $pathInfo;
		}
		// get 请求
		elseif(isset($_GET[$this->routeVar]))
			return $_GET[$this->routeVar];
		// post 请求
		elseif(isset($_POST[$this->routeVar]))
			return $_POST[$this->routeVar];
		else
			return '';
	}

	/**
	 * Parses a path info into URL segments and saves them to $_GET and $_REQUEST.
	 * @param string $pathInfo path info
	 */
	
	// 解析一个路径信息到url片段并保存到$_GET和$_REQUEST中
	public function parsePathInfo($pathInfo)
	{
		if($pathInfo==='')
			return;
		$segs=explode('/',$pathInfo.'/');
		$n=count($segs);
		for($i=0;$i<$n-1;$i+=2)
		{
			$key=$segs[$i];
			if($key==='') continue;
			$value=$segs[$i+1];
			if(($pos=strpos($key,'['))!==false && ($m=preg_match_all('/\[(.*?)\]/',$key,$matches))>0)
			{
				$name=substr($key,0,$pos);
				for($j=$m-1;$j>=0;--$j)
				{
					if($matches[1][$j]==='')
						$value=array($value);
					else
						$value=array($matches[1][$j]=>$value);
				}
				if(isset($_GET[$name]) && is_array($_GET[$name]))
					$value=CMap::mergeArray($_GET[$name],$value);
				$_REQUEST[$name]=$_GET[$name]=$value;
			}
			else
				$_REQUEST[$key]=$_GET[$key]=$value;
		}
	}

	/**
	 * Creates a path info based on the given parameters.
	 * @param array $params list of GET parameters
	 * @param string $equal the separator between name and value
	 * @param string $ampersand the separator between name-value pairs
	 * @param string $key this is used internally.
	 * @return string the created path info
	 */
	
	// 基于给定的参数创建一个路径信息
	public function createPathInfo($params,$equal,$ampersand, $key=null)
	{
		$pairs = array();
		foreach($params as $k => $v)
		{
			if ($key!==null)
				$k = $key.'['.$k.']';

			if (is_array($v))
				$pairs[]=$this->createPathInfo($v,$equal,$ampersand, $k);
			else
				$pairs[]=urlencode($k).$equal.urlencode($v);
		}
		return implode($ampersand,$pairs);
	}

	/**
	 * Removes the URL suffix from path info.
	 * @param string $pathInfo path info part in the URL
	 * @param string $urlSuffix the URL suffix to be removed
	 * @return string path info with URL suffix removed.
	 */
	
	// 从路径信息中去除url后缀
	public function removeUrlSuffix($pathInfo,$urlSuffix)
	{
		if($urlSuffix!=='' && substr($pathInfo,-strlen($urlSuffix))===$urlSuffix)
			return substr($pathInfo,0,-strlen($urlSuffix));
		else
			return $pathInfo;
	}

	/**
	 * Returns the base URL of the application.
	 * @return string the base URL of the application (the part after host name and before query string).
	 * If {@link showScriptName} is true, it will include the script name part.
	 * Otherwise, it will not, and the ending slashes are stripped off.
	 */
	
	// 返回应用程序的基URL host信息后面、query string前面的那段
	// 比如应用放在根目录 / 或者 /index.php
	// 放在/a/目录下  就是 /a/ 或者 /a/index.php
	public function getBaseUrl()
	{
		// 如果指定了
		if($this->_baseUrl!==null)
			return $this->_baseUrl;
		else
		{
			if($this->showScriptName) // 是否要显示脚本名称 直观点 如 index.php
				$this->_baseUrl=Yii::app()->getRequest()->getScriptUrl();
			else
				$this->_baseUrl=Yii::app()->getRequest()->getBaseUrl();
			return $this->_baseUrl;
		}
	}

	/**
	 * Sets the base URL of the application (the part after host name and before query string).
	 * This method is provided in case the {@link baseUrl} cannot be determined automatically.
	 * The ending slashes should be stripped off. And you are also responsible to remove the script name
	 * if you set {@link showScriptName} to be false.
	 * @param string $value the base URL of the application
	 * @since 1.1.1
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=$value;
	}

	/**
	 * Returns the URL format.
	 * @return string the URL format. Defaults to 'path'. Valid values include 'path' and 'get'.
	 * Please refer to the guide for more details about the difference between these two formats.
	 */
	
	// 返回url格式化 默认path 可选 path 和 get
	public function getUrlFormat()
	{
		return $this->_urlFormat;
	}

	/**
	 * Sets the URL format.
	 * @param string $value the URL format. It must be either 'path' or 'get'.
	 */
	
	// 设置url format
	public function setUrlFormat($value)
	{
		if($value===self::PATH_FORMAT || $value===self::GET_FORMAT)
			$this->_urlFormat=$value;
		else
			throw new CException(Yii::t('yii','CUrlManager.UrlFormat must be either "path" or "get".'));
	}
}


/**
 * CBaseUrlRule is the base class for a URL rule class.
 *
 * Custom URL rule classes should extend from this class and implement two methods:
 * {@link createUrl} and {@link parseUrl}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1.8
 */

// url规则类的基类
abstract class CBaseUrlRule extends CComponent
{
	/**
	 * @var boolean whether this rule will also parse the host info part. Defaults to false.
	 */
	public $hasHostInfo=false; // 表示该规则是否也将解析主机信息的部分
	/**
	 * Creates a URL based on this rule.
	 * @param CUrlManager $manager the manager
	 * @param string $route the route
	 * @param array $params list of parameters (name=>value) associated with the route
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return mixed the constructed URL. False if this rule does not apply.
	 */
	
	// 基于这条规则创建一个url
	abstract public function createUrl($manager,$route,$params,$ampersand);
	/**
	 * Parses a URL based on this rule.
	 * @param CUrlManager $manager the URL manager
	 * @param CHttpRequest $request the request object
	 * @param string $pathInfo path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
	 * @param string $rawPathInfo path info that contains the potential URL suffix
	 * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
	 */
	
	// 基于这条规则接卸一个url
	abstract public function parseUrl($manager,$request,$pathInfo,$rawPathInfo);
}

/**
 * CUrlRule represents a URL formatting/parsing rule.
 *
 * It mainly consists of two parts: route and pattern. The former classifies
 * the rule so that it only applies to specific controller-action route.
 * The latter performs the actual formatting and parsing role. The pattern
 * may have a set of named parameters.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */

// CUrlRule实现了一个url的格式化和解析
// 主要由两个部分组成：路由和模式。
// 前者分类规则，所以它只适用于特定的controller-action组合
// 后者执行实际的格式化和解析规则。
// 模式可能含有一组命名参数。
class CUrlRule extends CBaseUrlRule
{
	/**
	 * @var string the URL suffix used for this rule.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * Defaults to null, meaning using the value of {@link CUrlManager::urlSuffix}.
	 */
	
	// 用于此规则的url后缀
	// 例如.html 这样可以让url看起来是个静态的html页面 默认null 将适用CUrlManager::urlSuffix的值
	public $urlSuffix;
	/**
	 * @var boolean whether the rule is case sensitive. Defaults to null, meaning
	 * using the value of {@link CUrlManager::caseSensitive}.
	 */
	public $caseSensitive; // 规则是否大小写敏感 默认为null 将适用CUrlManager::caseSensitive
	/**
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 */
	public $defaultParams=array(); // 这个规则的默认附加请求参数
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in the rule when creating a URL. Defaults to null, meaning using the value
	 * of {@link CUrlManager::matchValue}. When this property is false, it means
	 * a rule will be used for creating a URL if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	// 在创建URL的时候，是否需要$_GET参数的值匹配规则中相应的子模式
	// 默认值为NULl，表示使用CUrlManager的matchValue。
	// 当值为false的时候，表示当路由和参数名称匹配这个规则将被用于创建url
	// 如果为true，给定的参数值必须匹配相应的子模式
	// 注意，这个属性数值为true，将降低性能
	public $matchValue;
	/**
	 * @var string the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
	 * If this rule can match multiple verbs, please separate them with commas.
	 * If this property is not set, the rule can match any verb.
	 * Note that this property is only used when parsing a request. It is ignored for URL creation.
	 * @since 1.1.7
	 */
	
	// http请求方式 例如GET,POST,DELETE
	// 可以同时提供多个请求方式，用逗号隔开
	// 只作用在解析请求时候，在创建url时无效
	public $verb;
	/**
	 * @var boolean whether this rule is only used for request parsing.
	 * Defaults to false, meaning the rule is used for both URL parsing and creation.
	 * @since 1.1.7
	 */
	
	// 该规则是否只是用于请求解析
	// 默认为false，这意味着此规则用于URL解析和创建
	public $parsingOnly=false;
	/**
	 * @var string the controller/action pair
	 */
	public $route; // 保存路由部分 路由路径的格式
	/**
	 * @var array the mapping from route param name to token name (e.g. _r1=><1>)
	 */
	
	// 路由路径中哪些字段来源与输入的url相映射，在解析阶段使用
	public $references=array();
	/**
	 * @var string the pattern used to match route
	 */
	
	// 用于对路由进行配置的正则表达式，在创建url阶段使用 也就是在createUrl方法中用到
	public $routePattern;
	/**
	 * @var string regular expression used to parse a URL
	 */
	
	// 用于对url进行匹配的正则表达式，在解析url阶段使用 也即是在parseUrl方法中使用到
	public $pattern;
	/**
	 * @var string template used to construct a URL
	 */
	
	// 记录url有哪些字段组成，是创建url的模板，在创建url阶段使用
	// 是要将这些字段填上值，就可以得到需要的url了
	public $template;
	/**
	 * @var array list of parameters (name=>regular expression)
	 */
	
	// url中哪些字段需要添加到$_GET中取，在解析url阶段使用
	public $params=array();
	/**
	 * @var boolean whether the URL allows additional parameters at the end of the path info.
	 */
	public $append; // 该网址是否允许额外的参数添加到路径后面
	/**
	 * @var boolean whether host info should be considered for this rule
	 */
	public $hasHostInfo; // 主机信息是否应该考虑对这条规则

	/**
	 * Constructor.
	 * @param string $route the route of the URL (controller/action)
	 * @param string $pattern the pattern for matching the URL
	 */
	
	// 在这里会为几个重要的参数赋值
	// pattern,routePattern,template,route,references,params
	// $route url路由
	// $pattern 模式匹配的URL
	public function __construct($route,$pattern)
	{
		// 例如这么个 $rules '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>'
		// $route '<controller>/<action>'
		// $pattern '<controller:\w+>/<action:\w+>/<id:\d+>'

		if(is_array($route))
		{
			foreach(array('urlSuffix', 'caseSensitive', 'defaultParams', 'matchValue', 'verb', 'parsingOnly') as $name)
			{
				if(isset($route[$name]))
					$this->$name=$route[$name];
			}
			if(isset($route['pattern']))
				$pattern=$route['pattern'];
			$route=$route[0];
		}
		$this->route=trim($route,'/');

		$tr2['/']=$tr['/']='\\/';

		// 判断是否有映射
		if(strpos($route,'<')!==false && preg_match_all('/<(\w+)>/',$route,$matches2))
		{
			foreach($matches2[1] as $name)
				$this->references[$name]="<$name>";
		}

		// 如果模式中包含http:// 或 https:// 就设置这条规则需要考虑主机信息
		$this->hasHostInfo=!strncasecmp($pattern,'http://',7) || !strncasecmp($pattern,'https://',8);

		// 如果$this->verb不为null 那么格式化为全部大写的数组格式比如 提供的get,post => array('GET', 'POST')
		if($this->verb!==null)
			$this->verb=preg_split('/[\s,]+/',strtoupper($this->verb),-1,PREG_SPLIT_NO_EMPTY);

		if(preg_match_all('/<(\w+):?(.*?)?>/',$pattern,$matches))
		{
			$tokens=array_combine($matches[1],$matches[2]);
			foreach($tokens as $name=>$value)
			{
				if($value==='')
					$value='[^\/]+';

				$tr["<$name>"]="(?P<$name>$value)";
				if(isset($this->references[$name])) // 如果名称在映射表中
					$tr2["<$name>"]=$tr["<$name>"];
				else
					$this->params[$name]=$value;
			}
		}

		$p=rtrim($pattern,'*'); // 去掉模式前后的*符号
		$this->append=$p!==$pattern; // 设置$this->append值 = 是否$p不恒等于$pattern

		$p=trim($p,'/');
		$this->template=preg_replace('/<(\w+):?.*?>/','<$1>',$p);
		$this->pattern='/^'.strtr($this->template,$tr).'\/';
		if($this->append)
			$this->pattern.='/u';
		else
			$this->pattern.='$/u';

		if($this->references!==array())
			$this->routePattern='/^'.strtr($this->route,$tr2).'$/u';

		// pattern = '/^(?P<controller>\w+)\/(?P<action>\w+)\/(?P<id>\d+)\/$/u'
		// routePattern = '/^(?P<controller>\w+)\/(?P<action>\w+)$/u'
		// template = '<controller>/<action>/<id>'
		// route = '<controller>/<action>'
		// references = array('controller' => '<controller>', 'action' => '<action>')
		// params = array('id' => '\d+')

		if(YII_DEBUG && @preg_match($this->pattern,'test')===false)
			throw new CException(Yii::t('yii','The URL pattern "{pattern}" for route "{route}" is not a valid regular expression.',
				array('{route}'=>$route,'{pattern}'=>$pattern)));
	}

	/**
	 * Creates a URL based on this rule.
	 * @param CUrlManager $manager the manager
	 * @param string $route the route
	 * @param array $params list of parameters
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return mixed the constructed URL or false on error
	 */
	public function createUrl($manager,$route,$params,$ampersand)
	{
		// 只供解析用
		if($this->parsingOnly)
			return false;

		// 区分大小写
		if($manager->caseSensitive && $this->caseSensitive===null || $this->caseSensitive)
			$case='';
		else
			$case='i';

		$tr=array();

		// 例如 $route = 'site/index';
		// $this->route = '<controller>/view';
		if($route!==$this->route)
		{
			if($this->routePattern!==null && preg_match($this->routePattern.$case,$route,$matches))
			{
				foreach($this->references as $key=>$name)
					$tr[$name]=$matches[$key];
			}
			else
				return false;
		}
		
		// 例如 
		// $this->defaultParams = array('a' => 1, 'b' => 2);
		// $params = array('a' => 3, 'b' => 2);
		// 这么将返回false
		foreach($this->defaultParams as $key=>$value)
		{
			if(isset($params[$key])) // 如果$this->defaultParams和$params中有同名的参数
			{
				if($params[$key]==$value) // 如果值相同，删除掉$params中的
					unset($params[$key]);
				else
					return false; // 否则返回false，这个rule不适合
			}
		}

		// 必须保存经过上面审核后
		// $this->params中的参数还能在$params中找到
		foreach($this->params as $key=>$value)
			if(!isset($params[$key]))
				return false;

		// 如果要配置值
		// 循环验证值
		// 例如$this->params = array('id' => '\d+');
		if($manager->matchValue && $this->matchValue===null || $this->matchValue)
		{
			foreach($this->params as $key=>$value)
			{
				// \A 类似于 ^ ，匹配开头，但是不受多行选项的影响
				// \z 类似于 $ ，匹配结尾，但是不受多行选项的影响
				// 这里直接使用 ^ 和 $ 就可以了，因为没有使用 m 修饰符
				if(!preg_match('/\A'.$value.'\z/u'.$case,$params[$key]))
					return false;
			}
		}

		foreach($this->params as $key=>$value)
		{
			$tr["<$key>"]=urlencode($params[$key]);
			unset($params[$key]);
		}

		$suffix=$this->urlSuffix===null ? $manager->urlSuffix : $this->urlSuffix;

		$url=strtr($this->template,$tr);

		if($this->hasHostInfo)
		{
			$hostInfo=Yii::app()->getRequest()->getHostInfo();
			if(stripos($url,$hostInfo)===0)
				$url=substr($url,strlen($hostInfo));
		}

		if(empty($params))
			return $url!=='' ? $url.$suffix : $url;

		if($this->append)
			$url.='/'.$manager->createPathInfo($params,'/','/').$suffix;
		else
		{
			if($url!=='')
				$url.=$suffix;
			$url.='?'.$manager->createPathInfo($params,'=',$ampersand);
		}

		return $url;
	}

	/**
	 * Parses a URL based on this rule.
	 * @param CUrlManager $manager the URL manager
	 * @param CHttpRequest $request the request object
	 * @param string $pathInfo path info part of the URL
	 * @param string $rawPathInfo path info that contains the potential URL suffix
	 * @return mixed the route that consists of the controller ID and action ID or false on error
	 */
	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if($this->verb!==null && !in_array($request->getRequestType(), $this->verb, true))
			return false;

		if($manager->caseSensitive && $this->caseSensitive===null || $this->caseSensitive)
			$case='';
		else
			$case='i';

		if($this->urlSuffix!==null)
			$pathInfo=$manager->removeUrlSuffix($rawPathInfo,$this->urlSuffix);

		// URL suffix required, but not found in the requested URL
		if($manager->useStrictParsing && $pathInfo===$rawPathInfo)
		{
			$urlSuffix=$this->urlSuffix===null ? $manager->urlSuffix : $this->urlSuffix;
			if($urlSuffix!='' && $urlSuffix!=='/')
				return false;
		}

		if($this->hasHostInfo)
			$pathInfo=strtolower($request->getHostInfo()).rtrim('/'.$pathInfo,'/');

		$pathInfo.='/';

		if(preg_match($this->pattern.$case,$pathInfo,$matches))
		{
			foreach($this->defaultParams as $name=>$value)
			{
				if(!isset($_GET[$name]))
					$_REQUEST[$name]=$_GET[$name]=$value;
			}
			$tr=array();
			foreach($matches as $key=>$value)
			{
				if(isset($this->references[$key]))
					$tr[$this->references[$key]]=$value;
				elseif(isset($this->params[$key]))
					$_REQUEST[$key]=$_GET[$key]=$value;
			}
			if($pathInfo!==$matches[0]) // there're additional GET params
				$manager->parsePathInfo(ltrim(substr($pathInfo,strlen($matches[0])),'/'));
			if($this->routePattern!==null)
				return strtr($this->route,$tr);
			else
				return $this->route;
		}
		else
			return false;
	}
}
