<?php
/**
 * This file contains core interfaces for Yii framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * IApplicationComponent is the interface that all application components must implement.
 *
 * After the application completes configuration, it will invoke the {@link init()}
 * method of every loaded application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 每个应用组件必须实现的接口
// 当应用配置完成后，应用将调用所有应用组件的init方法
interface IApplicationComponent
{
	/**
	 * Initializes the application component.
	 * This method is invoked after the application completes configuration.
	 */
	
	// 初始化这个应用组件
	public function init();
	/**
	 * @return boolean whether the {@link init()} method has been invoked.
	 */
	
	// 返回应用组件是否初始化
	public function getIsInitialized();
}

/**
 * ICache is the interface that must be implemented by cache components.
 *
 * This interface must be implemented by classes supporting caching feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching
 * @since 1.0
 */

// 所有缓存组件必须实现的接口
interface ICache
{
	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	
	// 返回缓存数据
	public function get($id);
	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * Some caches (such as memcache, apc) allow retrieving multiple cached values at one time,
	 * which may improve the performance since it reduces the communication cost.
	 * In case a cache doesn't support this feature natively, it will be simulated by this method.
	 * @param array $ids list of keys identifying the cached values
	 * @return array list of cached values corresponding to the specified keys. The array
	 * is returned in terms of (key,value) pairs.
	 * If a value is not cached or expired, the corresponding array value will be false.
	 */
	
	// 返回多个缓存数据，$ids必须是一个key数组（比如memcache, apc可以同时返回多个缓存数据）
	// 如array('key1', 'key2') 返回数据 array('key1' => 'value1', 'key2' => 'value2')
	// 如果key1缓存不存在或已经失效 这对应的key1值为false
	public function mget($ids);
	/**
	 * Stores a value identified by a key into cache.
	 * If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	
	// 设置缓存不管这个缓存是否存在
	// 第三个参数表示依赖项，如果依赖项的值改变，那么这个缓存将失效
	public function set($id,$value,$expire=0,$dependency=null);
	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	
	// 同上，有点却别。在缓存存在时，将不会设置这个缓存
	public function add($id,$value,$expire=0,$dependency=null);
	/**
	 * Deletes a value with the specified key from cache
	 * @param string $id the key of the value to be deleted
	 * @return boolean whether the deletion is successful
	 */
	
	// 删除缓存
	public function delete($id);
	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 * @return boolean whether the flush operation was successful.
	 */
	
	// 清空所有缓存 使用这个方法需谨慎 如多个应用使用了同一个缓存
	public function flush();
}

/**
 * ICacheDependency is the interface that must be implemented by cache dependency classes.
 *
 * This interface must be implemented by classes meant to be used as
 * cache dependencies.
 *
 * Objects implementing this interface must be able to be serialized and unserialized.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching
 * @since 1.0
 */

// 缓存依赖项类必须实现的接口
interface ICacheDependency
{
	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	
	// 计算依赖项值，并保存 此方法在缓存写入之前被调用
	public function evaluateDependency();
	/**
	 * @return boolean whether the dependency has changed.
	 */
	
	// 返回依赖项是否改变
	public function getHasChanged();
}


/**
 * IStatePersister is the interface that must be implemented by state persister classes.
 *
 * This interface must be implemented by all state persister classes (such as
 * {@link CStatePersister}.
 *
 * @package system.base
 * @since 1.0
 */

// 所有持久状态类必须实现的接口
interface IStatePersister
{
	/**
	 * Loads state data from a persistent storage.
	 * @return mixed the state
	 */
	
	// 从永久存储体中加载数据
	public function load();
	/**
	 * Saves state data into a persistent storage.
	 * @param mixed $state the state to be saved
	 */
	
	// 保存状态数据到永久存储体
	public function save($state);
}


/**
 * IFilter is the interface that must be implemented by action filters.
 *
 * @package system.base
 * @since 1.0
 */

// 所有过滤器类必须实现的接口
interface IFilter
{
	/**
	 * Performs the filtering.
	 * This method should be implemented to perform actual filtering.
	 * If the filter wants to continue the action execution, it should call
	 * <code>$filterChain->run()</code>.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	
	// 执行过滤
	// 如果这个过滤器需要继续执行这个动作，必须调用$filterChain->run();
	// $filterChain表示过滤器链
	public function filter($filterChain);
}


/**
 * IAction is the interface that must be implemented by controller actions.
 *
 * @package system.base
 * @since 1.0
 */

// 控制器动作必须实现的接口（当你需要将控制器单独写成一个类的时候，这个类必须实现这个接口）
interface IAction
{
	/**
	 * @return string id of the action
	 */
	
	// 返回动作ID值
	public function getId();
	/**
	 * @return CController the controller instance
	 */
	
	// 返回控制器实例
	public function getController();
}


/**
 * IWebServiceProvider interface may be implemented by Web service provider classes.
 *
 * If this interface is implemented, the provider instance will be able
 * to intercept the remote method invocation (e.g. for logging or authentication purpose).
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// Web service提供类必须实现的接口
// 如果实现接口类实例将可以拦截远程方法调用 （例如, 日志或验证）
interface IWebServiceProvider
{
	/**
	 * This method is invoked before the requested remote method is invoked.
	 * @param CWebService $service the currently requested Web service.
	 * @return boolean whether the remote method should be executed.
	 */
	
	// 这个方法在请求远程方法前调用
	public function beforeWebMethod($service);
	/**
	 * This method is invoked after the requested remote method is invoked.
	 * @param CWebService $service the currently requested Web service.
	 */
	
	// 这个方法在请求远程方法后调用
	public function afterWebMethod($service);
}


/**
 * IViewRenderer interface is implemented by a view renderer class.
 *
 * A view renderer is {@link CWebApplication::viewRenderer viewRenderer}
 * application component whose wants to replace the default view rendering logic
 * implemented in {@link CBaseController}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 视图渲染类必须实现的接口
interface IViewRenderer
{
	/**
	 * Renders a view file.
	 * @param CBaseController $context the controller or widget who is rendering the view file.
	 * @param string $file the view file path
	 * @param mixed $data the data to be passed to the view
	 * @param boolean $return whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	
	// 渲染视图文件
	public function renderFile($context,$file,$data,$return);
}


/**
 * IUserIdentity interface is implemented by a user identity class.
 *
 * An identity represents a way to authenticate a user and retrieve
 * information needed to uniquely identity the user. It is normally
 * used with the {@link CWebApplication::user user application component}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 用户验证类必须实现的接口
interface IUserIdentity
{
	/**
	 * Authenticates the user.
	 * The information needed to authenticate the user
	 * are usually provided in the constructor.
	 * @return boolean whether authentication succeeds.
	 */
	
	// 验证用户身份 返回true or false
	public function authenticate();
	/**
	 * Returns a value indicating whether the identity is authenticated.
	 * @return boolean whether the identity is valid.
	 */
	
	// 是否验证成功
	public function getIsAuthenticated();
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	
	// 返回一个值，该值代表唯一的身份（唯一标识）
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	
	// 返回标识的显示名称（如用户名）
	public function getName();
	/**
	 * Returns the additional identity information that needs to be persistent during the user session.
	 * @return array additional identity information that needs to be persistent during the user session (excluding {@link id}).
	 */
	
	// 返回额外的身份信息，需要用在用户会话期间的持久性存储
	public function getPersistentStates();
}


/**
 * IWebUser interface is implemented by a {@link CWebApplication::user user application component}.
 *
 * A user application component represents the identity information
 * for the current user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 用户（应用）组件必须实现的接口
interface IWebUser
{
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	
	// 返回一个代表唯一身份的值 通常是用户id
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	
	// 返回身份的显示名字（例如，用户名）
	public function getName();
	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the user is a guest (not authenticated)
	 */
	
	// 返回当前用户是否为访客（即未验证）
	public function getIsGuest();
	/**
	 * Performs access check for this user.
	 * @param string $operation the name of the operation that need access check.
	 * @param array $params name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by this user.
	 */
	
	// 检查用户执行权限
	// $operation 操作名
	// $params 分配给用户的任务或权限（键值对）
	public function checkAccess($operation,$params=array());
	/**
	 * Redirects the user browser to the login page.
	 * Before the redirection, the current URL (if it's not an AJAX url) will be
	 * kept in {@link returnUrl} so that the user browser may be redirected back
	 * to the current page after successful login. Make sure you set {@link loginUrl}
	 * so that the user browser can be redirected to the specified login URL after
	 * calling this method.
	 * After calling this method, the current request processing will be terminated.
	 */
	
	// 将页面重定向到登陆页面
	// 在重定向执行前，当前页面的url（如果不是ajax请求的url）需要被存入returnUrl，所以在登陆后我们可以返回到需要登陆的页面
	public function loginRequired();
}


/**
 * IAuthManager interface is implemented by an auth manager application component.
 *
 * An auth manager is mainly responsible for providing role-based access control (RBAC) service.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 验证组件必须实现的接口
interface IAuthManager
{
	/**
	 * Performs access check for the specified user.
	 * @param string $itemName the name of the operation that we are checking access to
	 * @param mixed $userId the user ID. This should be either an integer or a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	
	// 检查指定用户的执行权限
	// $itemName 需要权限检查的授权项名称
	// $userId 用户ID。它应该是一个整数或一个字符串， 代表用户的唯一标识，参见IWebUser::getId。
	// $params 分配给用户的任务或角色
	public function checkAccess($itemName,$userId,$params=array());

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CException if an item with the same name already exists
	 */
	
	// 建立一个授权项
	// $name 授权项名称 必须是唯一标识
	// $type 受权限类型 0操作 1任务 2角色】
	// $description 受权限描述信息
	// $bizRule 授权项关联的业务规则 这是一段php代码 当调用checkAccess方法时 被执行
	// $data 授权项附加数据
	// 返回这个授权项 CAuthItem对象 如果这个授权项已经存在 则抛出异常
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null);
	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	
	// 删除授权项
	public function removeAuthItem($name);
	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	
	// 返回指定类型和用户的授权项
	public function getAuthItems($type=null,$userId=null);
	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	
	// 返回指定名称的授权项
	public function getAuthItem($name);
	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	
	// 保存授权项到持久存储
	// $oldName 授权项名称，如果为null，意味着授权项名没有改变。（个人感觉这里应该是newName会通顺点）
	public function saveAuthItem($item,$oldName=null);

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	
	// 添加一个授权项作为另一个授权项的子授权项
	public function addItemChild($itemName,$childName);
	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	
	// 从一个授权项中删除一个子授权项
	public function removeItemChild($itemName,$childName);
	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	
	// 检查一个授权项中是否含有子授权项
	public function hasItemChild($itemName,$childName);
	/**
	 * Returns the children of the specified item.
	 * @param mixed $itemName the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return array all child items of the parent
	 */
	
	// 返回指定授权项的子授权项
	// $itemName 可以是授权项名称或者授权项数组（多个）
	public function getItemChildren($itemName);

	/**
	 * Assigns an authorization item to a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CException if the item does not exist or if the item has already been assigned to the user
	 */
	
	// 为用户分配一个授权项
	// $itemName 授权项名称
	// $userId 用户ID（参见 IWebUser::getId）
	// $bizRule 当调用checkAccess时 授权项关联的业务规则
	// $data 附加数据
	public function assign($itemName,$userId,$bizRule=null,$data=null);
	/**
	 * Revokes an authorization assignment from a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	
	// 撤销一个用户分配的授权项
	public function revoke($itemName,$userId);
	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	
	// 判断是否分配给用户指定授权项
	public function isAssigned($itemName,$userId);
	/**
	 * Returns the item assignment information.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	
	// 返回授权项的分配信息
	// 如果没有分配给$userId用户$itemName授权项，则返回null
	// 否则返回授权项信息 CAuthAssignment对象
	public function getAuthAssignment($itemName,$userId);
	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	
	// 返回指定用户的授权项分配信息
	public function getAuthAssignments($userId);
	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	
	// 保存修改的授权信息
	public function saveAuthAssignment($assignment);

	/**
	 * Removes all authorization data.
	 */
	
	// 移除所有授权数据
	public function clearAll();
	/**
	 * Removes all authorization assignments.
	 */
	
	// 移除所有授权分配信息
	public function clearAuthAssignments();

	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	// 保存授权数据到持久存储。 如果授权数据有任何改变， 请确保你调用的这个方法能将改变数据保存到持久存储
	public function save();

	/**
	 * Executes a business rule.
	 * A business rule is a piece of PHP code that will be executed when {@link checkAccess} is called.
	 * @param string $bizRule the business rule to be executed.
	 * @param array $params additional parameters to be passed to the business rule when being executed.
	 * @param mixed $data additional data that is associated with the corresponding authorization item or assignment
	 * @return boolean whether the execution returns a true value.
	 * If the business rule is empty, it will also return true.
	 */
	
	// 执行业务规则。 业务规则是一块PHP代码当调用checkAccess时将被执行。
	// 一个业务规则是一段php代码
	// $bizRule 业务规则代码
	// $params 传递给业务规则的附加数据
	// $data 分配的授权项的相关附加数据
	public function executeBizRule($bizRule,$params,$data);
}


/**
 * IBehavior interfaces is implemented by all behavior classes.
 *
 * A behavior is a way to enhance a component with additional methods that
 * are defined in the behavior class and not available in the component class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */

// 所有行为类必须实现的接口
interface IBehavior
{
	/**
	 * Attaches the behavior object to the component.
	 * @param CComponent $component the component that this behavior is to be attached to.
	 */
	
	// 将此行为绑定到$component组件上
	public function attach($component);
	/**
	 * Detaches the behavior object from the component.
	 * @param CComponent $component the component that this behavior is to be detached from.
	 */
	
	// 将次行为从组件$component中去除
	public function detach($component);
	/**
	 * @return boolean whether this behavior is enabled
	 */
	
	// 返回是否启用行为
	public function getEnabled();
	/**
	 * @param boolean $value whether this behavior is enabled
	 */
	
	// 返回是否禁用行为
	public function setEnabled($value);
}

/**
 * IWidgetFactory is the interface that must be implemented by a widget factory class.
 *
 * When calling {@link CBaseController::createWidget}, if a widget factory is available,
 * it will be used for creating the requested widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1
 */

// 挂件工厂类必须实现的接口
interface IWidgetFactory
{
	/**
	 * Creates a new widget based on the given class name and initial properties.
	 * @param CBaseController $owner the owner of the new widget
	 * @param string $className the class name of the widget. This can also be a path alias (e.g. system.web.widgets.COutputCache)
	 * @param array $properties the initial property values (name=>value) of the widget.
	 * @return CWidget the newly created widget whose properties have been initialized with the given values.
	 */
	
	// 基于给定的类名和初始属性创建一个新挂件
	// $owner 挂件的所属者 控制器对象
	// $className 挂件的类名。它也可以是一个路径别名（例如，system.web.widgets.COutputCache）
	// $properties 初始化挂件的属性值（name=>value）
	// 返回挂件 CWidget对象
	public function createWidget($owner,$className,$properties=array());
}

/**
 * IDataProvider is the interface that must be implemented by data provider classes.
 *
 * Data providers are components that can feed data for widgets such as data grid, data list.
 * Besides providing data, they also support pagination and sorting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1
 */

// 数据提供者类必须实现的接口
// 数据提供者组件能够为挂件，如data grid，data list提供数据。 除了提供数据，它也支持分页与排序
interface IDataProvider
{
	/**
	 * @return string the unique ID that identifies the data provider from other data providers.
	 */
	
	// 返回唯一ID值 来区分数据提供者
	public function getId();
	/**
	 * Returns the number of data items in the current page.
	 * This is equivalent to <code>count($provider->getData())</code>.
	 * When {@link pagination} is set false, this returns the same value as {@link totalItemCount}.
	 * @param boolean $refresh whether the number of data items should be re-calculated.
	 * @return integer the number of data items in the current page.
	 */
	
	// 返回当页数据项的数量 相当于count($provider->getData())
	// $refresh 是否重新计算数据项中的数量
	public function getItemCount($refresh=false);
	/**
	 * Returns the total number of data items.
	 * When {@link pagination} is set false, this returns the same value as {@link itemCount}.
	 * @param boolean $refresh whether the total number of data items should be re-calculated.
	 * @return integer total number of possible data items.
	 */
	
	// 返回数据项总数量。 当pagination设置为false，它与itemCount返回同样的值。
	public function getTotalItemCount($refresh=false);
	/**
	 * Returns the data items currently available.
	 * @param boolean $refresh whether the data should be re-fetched from persistent storage.
	 * @return array the list of data items currently available in this data provider.
	 */
	
	// 返回当前可用数据项
	public function getData($refresh=false);
	/**
	 * Returns the key values associated with the data items.
	 * @param boolean $refresh whether the keys should be re-calculated.
	 * @return array the list of key values corresponding to {@link data}. Each data item in {@link data}
	 * is uniquely identified by the corresponding key value in this array.
	 */
	
	// 返回数据项对应的键名数组
	public function getKeys($refresh=false);
	/**
	 * @return CSort the sorting object. If this is false, it means the sorting is disabled.
	 */
	
	// 返回排序对象 如果返回false表示禁用排序
	public function getSort();
	/**
	 * @return CPagination the pagination object. If this is false, it means the pagination is disabled.
	 */
	
	// 返回分页对象 如果返回false表示禁用分页
	public function getPagination();
}


/**
 * ILogFilter is the interface that must be implemented by log filters.
 *
 * A log filter preprocesses the logged messages before they are handled by a log route.
 * You can attach classes that implement ILogFilter to {@link CLogRoute::$filter}.
 *
 * @package system.logging
 * @since 1.1.11
 */

// 日志过滤器类必须实现的接口
interface ILogFilter
{
	/**
	 * This method should be implemented to perform actual filtering of log messages
	 * by working on the array given as the first parameter.
	 * Implementation might reformat, remove or add information to logged messages.
	 * @param array $logs list of messages. Each array element represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	public function filter(&$logs);
}

