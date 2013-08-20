<?php
/**
 * CDbConnection class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbConnection represents a connection to a database.
 *
 * CDbConnection works together with {@link CDbCommand}, {@link CDbDataReader}
 * and {@link CDbTransaction} to provide data access to various DBMS
 * in a common set of APIs. They are a thin wrapper of the {@link http://www.php.net/manual/en/ref.pdo.php PDO}
 * PHP extension.
 *
 * To establish a connection, set {@link setActive active} to true after
 * specifying {@link connectionString}, {@link username} and {@link password}.
 *
 * The following example shows how to create a CDbConnection instance and establish
 * the actual connection:
 * <pre>
 * $connection=new CDbConnection($dsn,$username,$password);
 * $connection->active=true;
 * </pre>
 *
 * After the DB connection is established, one can execute an SQL statement like the following:
 * <pre>
 * $command=$connection->createCommand($sqlStatement);
 * $command->execute();   // a non-query SQL statement execution
 * // or execute an SQL query and fetch the result set
 * $reader=$command->query();
 *
 * // each $row is an array representing a row of data
 * foreach($reader as $row) ...
 * </pre>
 *
 * One can do prepared SQL execution and bind parameters to the prepared SQL:
 * <pre>
 * $command=$connection->createCommand($sqlStatement);
 * $command->bindParam($name1,$value1);
 * $command->bindParam($name2,$value2);
 * $command->execute();
 * </pre>
 *
 * To use transaction, do like the following:
 * <pre>
 * $transaction=$connection->beginTransaction();
 * try
 * {
 *    $connection->createCommand($sql1)->execute();
 *    $connection->createCommand($sql2)->execute();
 *    //.... other SQL executions
 *    $transaction->commit();
 * }
 * catch(Exception $e)
 * {
 *    $transaction->rollback();
 * }
 * </pre>
 *
 * CDbConnection also provides a set of methods to support setting and querying
 * of certain DBMS attributes, such as {@link getNullConversion nullConversion}.
 *
 * Since CDbConnection implements the interface IApplicationComponent, it can
 * be used as an application component and be configured in application configuration,
 * like the following,
 * <pre>
 * array(
 *     'components'=>array(
 *         'db'=>array(
 *             'class'=>'CDbConnection',
 *             'connectionString'=>'sqlite:path/to/dbfile',
 *         ),
 *     ),
 * )
 * </pre>
 *
 * @property boolean $active Whether the DB connection is established.
 * @property PDO $pdoInstance The PDO instance, null if the connection is not established yet.
 * @property CDbTransaction $currentTransaction The currently active transaction. Null if no active transaction.
 * @property CDbSchema $schema The database schema for the current connection.
 * @property CDbCommandBuilder $commandBuilder The command builder.
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the sequence object.
 * @property mixed $columnCase The case of the column names.
 * @property mixed $nullConversion How the null and empty strings are converted.
 * @property boolean $autoCommit Whether creating or updating a DB record will be automatically committed.
 * @property boolean $persistent Whether the connection is persistent or not.
 * @property string $driverName Name of the DB driver.
 * @property string $clientVersion The version information of the DB driver.
 * @property string $connectionStatus The status of the connection.
 * @property boolean $prefetch Whether the connection performs data prefetching.
 * @property string $serverInfo The information of DBMS server.
 * @property string $serverVersion The version information of DBMS server.
 * @property integer $timeout Timeout settings for the connection.
 * @property array $attributes Attributes (name=>value) that are previously explicitly set for the DB connection.
 * @property array $stats The first element indicates the number of SQL statements executed,
 * and the second element the total time spent in SQL execution.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db
 * @since 1.0
 */

// CDbConnection 实现了连接数据库
class CDbConnection extends CApplicationComponent
{
	/**
	 * @var string The Data Source Name, or DSN, contains the information required to connect to the database.
	 * @see http://www.php.net/manual/en/function.PDO-construct.php
	 *
	 * Note that if you're using GBK or BIG5 then it's highly recommended to
	 * update to PHP 5.3.6+ and to specify charset via DSN like
	 * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
	 */
	
	// 数据源名称（DSN）
	public $connectionString;
	/**
	 * @var string the username for establishing DB connection. Defaults to empty string.
	 */
	
	// 数据库用户名
	public $username='';
	/**
	 * @var string the password for establishing DB connection. Defaults to empty string.
	 */
	
	// 数据库用户密码
	public $password='';
	/**
	 * @var integer number of seconds that table metadata can remain valid in cache.
	 * Use 0 or negative value to indicate not caching schema.
	 * If greater than 0 and the primary cache is enabled, the table metadata will be cached.
	 * @see schemaCachingExclude
	 */
	
	// 缓存表元数据的时间 如果为0 不缓存
	public $schemaCachingDuration=0;
	/**
	 * @var array list of tables whose metadata should NOT be cached. Defaults to empty array.
	 * @see schemaCachingDuration
	 */
	
	// 定义哪些表的元数据不需要缓存
	public $schemaCachingExclude=array();
	/**
	 * @var string the ID of the cache application component that is used to cache the table metadata.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching table metadata.
	 */
	
	// 缓存表元数据使用的缓存组件ID值
	public $schemaCacheID='cache';
	/**
	 * @var integer number of seconds that query results can remain valid in cache.
	 * Use 0 or negative value to indicate not caching query results (the default behavior).
	 *
	 * In order to enable query caching, this property must be a positive
	 * integer and {@link queryCacheID} must point to a valid cache component ID.
	 *
	 * The method {@link cache()} is provided as a convenient way of setting this property
	 * and {@link queryCachingDependency} on the fly.
	 *
	 * @see cache
	 * @see queryCachingDependency
	 * @see queryCacheID
	 * @since 1.1.7
	 */
	
	// 缓存查询结果的时间（秒），如果为0 不缓存
	public $queryCachingDuration=0;
	/**
	 * @var CCacheDependency the dependency that will be used when saving query results into cache.
	 * @see queryCachingDuration
	 * @since 1.1.7
	 */
	
	// 缓存查询数据是用到的依赖项
	public $queryCachingDependency;
	/**
	 * @var integer the number of SQL statements that need to be cached next.
	 * If this is 0, then even if query caching is enabled, no query will be cached.
	 * Note that each time after executing a SQL statement (whether executed on DB server or fetched from
	 * query cache), this property will be reduced by 1 until 0.
	 * @since 1.1.7
	 */
	
	// 下次将被缓存的SQL语句的数目。 如果它是 0，即使查询缓存被启用，查询也不会被缓存。 注意每次执行一条SQL语句之后 (是否在DB服务器上执行或从查询缓存中获取)， 这个属性将被减少1至0。
	public $queryCachingCount=0;
	/**
	 * @var string the ID of the cache application component that is used for query caching.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable query caching.
	 * @since 1.1.7
	 */
	
	// 缓存查询数据使用的缓存组件ID值
	public $queryCacheID='cache';
	/**
	 * @var boolean whether the database connection should be automatically established
	 * the component is being initialized. Defaults to true. Note, this property is only
	 * effective when the CDbConnection object is used as an application component.
	 */
	
	// 是否在组件初始化时自动建立数据库链接，默认为true
	// 注意：这个属性只有当CDbConnection对象用作应用程序组件时才有效
	public $autoConnect=true;
	/**
	 * @var string the charset used for database connection. The property is only used
	 * for MySQL and PostgreSQL databases. Defaults to null, meaning using default charset
	 * as specified by the database.
	 *
	 * Note that if you're using GBK or BIG5 then it's highly recommended to
	 * update to PHP 5.3.6+ and to specify charset via DSN like
	 * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
	 */
	
	// 数据库使用的编码 这个属性只适用于mysql和PostgreSQL。
	// 默认使用数据库默认编码
	// 注意，如果你使用gbk或者big5编码，强烈建议升级php到5.3.5以上，然后在DSN中制定编码
	public $charset;
	/**
	 * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
	 * will use the native prepare support if available. For some databases (such as MySQL),
	 * this may need to be set true so that PDO can emulate the prepare support to bypass
	 * the buggy native prepare support. Note, this property is only effective for PHP 5.1.3 or above.
	 * The default value is null, which will not change the ATTR_EMULATE_PREPARES value of PDO.
	 */
	
	// 是否打开准备模拟。默认为 false， 意味着PDO将准备使用本地预备支持，如果可用。对于某些数据库 (如 MySQL), 这将需要设置为true 以至于 PDO 能模拟该预备支持 绕过buggy本地预备支持。注意，这个属性仅仅在PHP 5.1.3以上版本
	public $emulatePrepare;
	/**
	 * @var boolean whether to log the values that are bound to a prepare SQL statement.
	 * Defaults to false. During development, you may consider setting this property to true
	 * so that parameter values bound to SQL statements are logged for debugging purpose.
	 * You should be aware that logging parameter values could be expensive and have significant
	 * impact on the performance of your application.
	 */
	
	// 是否记录的值绑定到一个准备的SQL语句。 默认为 false。在开发阶段，你应该考虑设置这个属性为true 以至于参数值能被绑定到SQL语句以记录用于高度目的。 你应该知道记录参数值代价是高昂的， 将很大程度影响你的应用程序的性能。
	public $enableParamLogging=false;
	/**
	 * @var boolean whether to enable profiling the SQL statements being executed.
	 * Defaults to false. This should be mainly enabled and used during development
	 * to find out the bottleneck of SQL executions.
	 */
	
	// 正在执行的SQL语句是否启用分析。 默认为 false。这个主要 启用它主要用于开发阶段找出SQL执行的瓶颈。
	public $enableProfiling=false;
	/**
	 * @var string the default prefix for table names. Defaults to null, meaning no table prefix.
	 * By setting this property, any token like '{{tableName}}' in {@link CDbCommand::text} will
	 * be replaced by 'prefixTableName', where 'prefix' refers to this property value.
	 * @since 1.1.0
	 */
	
	// 表前缀
	public $tablePrefix;
	/**
	 * @var array list of SQL statements that should be executed right after the DB connection is established.
	 * @since 1.1.1
	 */
	
	// 在建立好数据库链接后执行的sql语句
	public $initSQLs;
	/**
	 * @var array mapping between PDO driver and schema class name.
	 * A schema class can be specified using path alias.
	 * @since 1.1.6
	 */
	
	// PDO驱动程序和schema类名之间的映射。 使用路径别名指定一个 schema 类。
	public $driverMap=array(
		'pgsql'=>'CPgsqlSchema',    // PostgreSQL
		'mysqli'=>'CMysqlSchema',   // MySQL
		'mysql'=>'CMysqlSchema',    // MySQL
		'sqlite'=>'CSqliteSchema',  // sqlite 3
		'sqlite2'=>'CSqliteSchema', // sqlite 2
		'mssql'=>'CMssqlSchema',    // Mssql driver on windows hosts
		'dblib'=>'CMssqlSchema',    // dblib drivers on linux (and maybe others os) hosts
		'sqlsrv'=>'CMssqlSchema',   // Mssql
		'oci'=>'COciSchema',        // Oracle driver
	);

	/**
	 * @var string Custom PDO wrapper class.
	 * @since 1.1.8
	 */
	
	// 自定义的PDO封装类
	public $pdoClass = 'PDO';

	private $_attributes=array();
	private $_active=false;
	private $_pdo;
	private $_transaction;
	private $_schema;


	/**
	 * Constructor.
	 * Note, the DB connection is not established when this connection
	 * instance is created. Set {@link setActive active} property to true
	 * to establish the connection.
	 * @param string $dsn The Data Source Name, or DSN, contains the information required to connect to the database.
	 * @param string $username The user name for the DSN string.
	 * @param string $password The password for the DSN string.
	 * @see http://www.php.net/manual/en/function.PDO-construct.php
	 */
	
	// 初始化
	// 注意，实例化时不会建立数据库链接的
	public function __construct($dsn='',$username='',$password='')
	{
		$this->connectionString=$dsn;
		$this->username=$username;
		$this->password=$password;
	}

	/**
	 * Close the connection when serializing.
	 * @return array
	 */
	
	// 序列化时关闭数据库连接
	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}

	/**
	 * Returns a list of available PDO drivers.
	 * @return array list of available PDO drivers
	 * @see http://www.php.net/manual/en/function.PDO-getAvailableDrivers.php
	 */
	
	// 返回PDO所支持的数据库驱动 比如 返回值 array('mysql', 'sqlite') 表示支持这2个数据库
	public static function getAvailableDrivers()
	{
		return PDO::getAvailableDrivers();
	}

	/**
	 * Initializes the component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application
	 * when the CDbConnection is used as an application component.
	 * If you override this method, make sure to call the parent implementation
	 * so that the component can be marked as initialized.
	 */
	
	// 初始化这个组件
	public function init()
	{
		parent::init();
		// 如果设置了初始化组件时，建立数据库链接
		if($this->autoConnect)
			$this->setActive(true);
	}

	/**
	 * Returns whether the DB connection is established.
	 * @return boolean whether the DB connection is established
	 */
	
	// 返回数据库链接是否已经建立
	public function getActive()
	{
		return $this->_active;
	}

	/**
	 * Open or close the DB connection.
	 * @param boolean $value whether to open or close DB connection
	 * @throws CException if connection fails
	 */
	
	// 打开或关闭数据库链接 $value = true 打开 false 关闭
	public function setActive($value)
	{
		// 值改变了
		if($value!=$this->_active)
		{
			if($value)
				$this->open(); // 打开数据库链接
			else
				$this->close(); // 关闭数据库链接
		}
	}

	/**
	 * Sets the parameters about query caching.
	 * This method can be used to enable or disable query caching.
	 * By setting the $duration parameter to be 0, the query caching will be disabled.
	 * Otherwise, query results of the new SQL statements executed next will be saved in cache
	 * and remain valid for the specified duration.
	 * If the same query is executed again, the result may be fetched from cache directly
	 * without actually executing the SQL statement.
	 * @param integer $duration the number of seconds that query results may remain valid in cache.
	 * If this is 0, the caching will be disabled.
	 * @param CCacheDependency $dependency the dependency that will be used when saving the query results into cache.
	 * @param integer $queryCount number of SQL queries that need to be cached after calling this method. Defaults to 1,
	 * meaning that the next SQL query will be cached.
	 * @return CDbConnection the connection instance itself.
	 * @since 1.1.7
	 */
	
	// 设置查询缓存的参数
	// 这个方法用来开启或禁用查询缓存
	// 通过设置$duration为0来禁用查询缓存
	// 要不然， 新的SQL语句执行的查询结果将被保存在缓存中， 并且在指定时间段内有效。 如果同样的查询再次执行，结果将直接从缓存中读取， 而不是执行实际的SQL语句。
	// $duration 查询结果保持在缓存中有效的秒数。 如果它是0，缓存将被禁用
	// $dependency 当查询结果保存到缓存时，使用的依赖。
	// $queryCount 在调用此方法后，需要缓存的SQL查询的数目。 默认为 1，意味着下一条SQL查询将被缓存。
	public function cache($duration, $dependency=null, $queryCount=1)
	{
		$this->queryCachingDuration=$duration;
		$this->queryCachingDependency=$dependency;
		$this->queryCachingCount=$queryCount;
		return $this;
	}

	/**
	 * Opens DB connection if it is currently not
	 * @throws CException if connection fails
	 */
	
	// 打开数据库连接
	protected function open()
	{
		// 如果没有pdo实例，即没有连接
		if($this->_pdo===null)
		{
			if(empty($this->connectionString))
				throw new CDbException('CDbConnection.connectionString cannot be empty.');
			try
			{
				Yii::trace('Opening DB connection','system.db.CDbConnection');
				$this->_pdo=$this->createPdoInstance(); // 创建pdo实例
				$this->initConnection($this->_pdo); // 初始化数据库连接
				$this->_active=true; // 标志已经建立好数据库连接
			}
			catch(PDOException $e)
			{
				if(YII_DEBUG)
				{
					throw new CDbException('CDbConnection failed to open the DB connection: '.
						$e->getMessage(),(int)$e->getCode(),$e->errorInfo);
				}
				else
				{
					Yii::log($e->getMessage(),CLogger::LEVEL_ERROR,'exception.CDbException');
					throw new CDbException('CDbConnection failed to open the DB connection.',(int)$e->getCode(),$e->errorInfo);
				}
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	
	// 关闭当前活动的数据库连接
	protected function close()
	{
		Yii::trace('Closing DB connection','system.db.CDbConnection');
		$this->_pdo=null;
		$this->_active=false;
		$this->_schema=null;
	}

	/**
	 * Creates the PDO instance.
	 * When some functionalities are missing in the pdo driver, we may use
	 * an adapter class to provides them.
	 * @return PDO the pdo instance
	 */
	
	// 创建一个PDO实例
	// 创建一个 PDO 实例。 pdo驱动程序中缺少某些功能时， 我们可以使用一个适配器类，以提供它们。
	protected function createPdoInstance()
	{
		$pdoClass=$this->pdoClass;
		if(($pos=strpos($this->connectionString,':'))!==false)
		{
			$driver=strtolower(substr($this->connectionString,0,$pos));
			if($driver==='mssql' || $driver==='dblib')
				$pdoClass='CMssqlPdoAdapter';
			elseif($driver==='sqlsrv')
				$pdoClass='CMssqlSqlsrvPdoAdapter';
		}

		// _attributes保存了初始化PDO时的一些参数设置
		return new $pdoClass($this->connectionString,$this->username,
									$this->password,$this->_attributes);
	}

	/**
	 * Initializes the open db connection.
	 * This method is invoked right after the db connection is established.
	 * The default implementation is to set the charset for MySQL and PostgreSQL database connections.
	 * @param PDO $pdo the PDO instance
	 */
	
	// 初始化打开的数据库连接
	// 这个方法在数据库链接建立后执行
	// 设置数据库的编码
	protected function initConnection($pdo)
	{
		// 设置当出错时，抛出异常
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// 设置开启、禁用模拟预处理语句
		// 这可以确保SQL语句和相应的值在传递到mysql服务器之前是不会被PHP解析的（禁止了所有可能的恶意SQL注入攻击）
		// $this->emulatePrepare 可以是 true or false
		if($this->emulatePrepare!==null && constant('PDO::ATTR_EMULATE_PREPARES'))
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,$this->emulatePrepare);
		
		// 设置编码
		if($this->charset!==null)
		{
			// 只有pgsql mysql mysqli 这3个驱动可以设置编码
			$driver=strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
			if(in_array($driver,array('pgsql','mysql','mysqli')))
				$pdo->exec('SET NAMES '.$pdo->quote($this->charset));
		}

		// 执行初始化后需要执行的语句
		if($this->initSQLs!==null)
		{
			foreach($this->initSQLs as $sql)
				$pdo->exec($sql);
		}
	}

	/**
	 * Returns the PDO instance.
	 * @return PDO the PDO instance, null if the connection is not established yet
	 */
	
	// 返回PDO实例
	public function getPdoInstance()
	{
		return $this->_pdo;
	}

	/**
	 * Creates a command for execution.
	 * @param mixed $query the DB query to be executed. This can be either a string representing a SQL statement,
	 * or an array representing different fragments of a SQL statement. Please refer to {@link CDbCommand::__construct}
	 * for more details about how to pass an array as the query. If this parameter is not given,
	 * you will have to call query builder methods of {@link CDbCommand} to build the DB query.
	 * @return CDbCommand the DB command
	 */
	
	// 创建用于执行的命令
	public function createCommand($query=null)
	{
		$this->setActive(true);
		return new CDbCommand($this,$query);
	}

	/**
	 * Returns the currently active transaction.
	 * @return CDbTransaction the currently active transaction. Null if no active transaction.
	 */
	
	// 返回当前活动的事务
	// 如果没有活动事务，返回null
	public function getCurrentTransaction()
	{
		if($this->_transaction!==null)
		{
			if($this->_transaction->getActive())
				return $this->_transaction;
		}
		return null;
	}

	/**
	 * Starts a transaction.
	 * @return CDbTransaction the transaction initiated
	 */
	
	// 开始事务
	public function beginTransaction()
	{
		Yii::trace('Starting transaction','system.db.CDbConnection');
		$this->setActive(true);
		$this->_pdo->beginTransaction();
		return $this->_transaction=new CDbTransaction($this);
	}

	/**
	 * Returns the database schema for the current connection
	 * @return CDbSchema the database schema for the current connection
	 */
	
	// 返回当前连接数据库的模式
	public function getSchema()
	{
		if($this->_schema!==null)
			return $this->_schema;
		else
		{
			$driver=$this->getDriverName();
			if(isset($this->driverMap[$driver]))
				return $this->_schema=Yii::createComponent($this->driverMap[$driver], $this);
			else
				throw new CDbException(Yii::t('yii','CDbConnection does not support reading schema for {driver} database.',
					array('{driver}'=>$driver)));
		}
	}

	/**
	 * Returns the SQL command builder for the current DB connection.
	 * @return CDbCommandBuilder the command builder
	 */
	
	// 返回当前数据库连接SQL命令构建器
	public function getCommandBuilder()
	{
		return $this->getSchema()->getCommandBuilder();
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string $sequenceName name of the sequence object (required by some DBMS)
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
	 */
	
	// 返回最后次插入行的ID值
	public function getLastInsertID($sequenceName='')
	{
		$this->setActive(true);
		return $this->_pdo->lastInsertId($sequenceName);
	}

	/**
	 * Quotes a string value for use in a query.
	 * @param string $str string to be quoted
	 * @return string the properly quoted string
	 * @see http://www.php.net/manual/en/function.PDO-quote.php
	 */
	// 引号一个字符串
	public function quoteValue($str)
	{
		if(is_int($str) || is_float($str))
			return $str;

		$this->setActive(true);
		if(($value=$this->_pdo->quote($str))!==false)
			return $value;
		else  // the driver doesn't support quote (e.g. oci)
			// oracle 需要并不是 \ 来转义单引号，而是使用 单引号 转义 单引号
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
	}

	/**
	 * Quotes a table name for use in a query.
	 * If the table name contains schema prefix, the prefix will also be properly quoted.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	// 引号一个表名， 例如 user 引号后 `user`
	public function quoteTableName($name)
	{
		return $this->getSchema()->quoteTableName($name);
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains prefix, the prefix will also be properly quoted.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	// 引号一个列名，例如 username => `username` ， user.username => `user`.`username`
	public function quoteColumnName($name)
	{
		return $this->getSchema()->quoteColumnName($name);
	}

	/**
	 * Determines the PDO type for the specified PHP type.
	 * @param string $type The PHP type (obtained by gettype() call).
	 * @return integer the corresponding PDO type
	 */
	// 根据php类型返回对应的PDO类型
	public function getPdoType($type)
	{
		static $map=array
		(
			'boolean'=>PDO::PARAM_BOOL,
			'integer'=>PDO::PARAM_INT,
			'string'=>PDO::PARAM_STR,
			'resource'=>PDO::PARAM_LOB,
			'NULL'=>PDO::PARAM_NULL,
		);
		return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
	}

	/**
	 * Returns the case of the column names
	 * @return mixed the case of the column names
	 * @see http://www.php.net/manual/en/pdo.setattribute.php
	 */
	// 返回列名大小写情况，默认 PDO::CASE_NATURAL
	// 
	// PDO::CASE_LOWER：强制列名小写。
	// PDO::CASE_NATURAL：保留数据库驱动返回的列名。
	// PDO::CASE_UPPER：强制列名大写。
	public function getColumnCase()
	{
		return $this->getAttribute(PDO::ATTR_CASE);
	}

	/**
	 * Sets the case of the column names.
	 * @param mixed $value the case of the column names
	 * @see http://www.php.net/manual/en/pdo.setattribute.php
	 */
	// 设置列名的大小写
	public function setColumnCase($value)
	{
		$this->setAttribute(PDO::ATTR_CASE,$value);
	}

	/**
	 * Returns how the null and empty strings are converted.
	 * @return mixed how the null and empty strings are converted
	 * @see http://www.php.net/manual/en/pdo.setattribute.php
	 */
	// 返回 null 和 空字符串 的转换情况，默认 PDO::NULL_NATURAL
	// 
	// PDO::NULL_NATURAL: 不转换。
	// PDO::NULL_EMPTY_STRING： 将空字符串转换成 NULL。
	// PDO::NULL_TO_STRING: 将 NULL 转换成空字符串。
	public function getNullConversion()
	{
		return $this->getAttribute(PDO::ATTR_ORACLE_NULLS);
	}

	/**
	 * Sets how the null and empty strings are converted.
	 * @param mixed $value how the null and empty strings are converted
	 * @see http://www.php.net/manual/en/pdo.setattribute.php
	 */
	// 设置 null 和 空字符串 的转换情况
	public function setNullConversion($value)
	{
		$this->setAttribute(PDO::ATTR_ORACLE_NULLS,$value);
	}

	/**
	 * Returns whether creating or updating a DB record will be automatically committed.
	 * Some DBMS (such as sqlite) may not support this feature.
	 * @return boolean whether creating or updating a DB record will be automatically committed.
	 */
	// 返回是否自动提交
	public function getAutoCommit()
	{
		return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
	}

	/**
	 * Sets whether creating or updating a DB record will be automatically committed.
	 * Some DBMS (such as sqlite) may not support this feature.
	 * @param boolean $value whether creating or updating a DB record will be automatically committed.
	 */
	// 设置是否自动提交
	public function setAutoCommit($value)
	{
		$this->setAttribute(PDO::ATTR_AUTOCOMMIT,$value);
	}

	/**
	 * Returns whether the connection is persistent or not.
	 * Some DBMS (such as sqlite) may not support this feature.
	 * @return boolean whether the connection is persistent or not
	 */
	// 返回这个连接是否持久连接
	public function getPersistent()
	{
		return $this->getAttribute(PDO::ATTR_PERSISTENT);
	}

	/**
	 * Sets whether the connection is persistent or not.
	 * Some DBMS (such as sqlite) may not support this feature.
	 * @param boolean $value whether the connection is persistent or not
	 */
	// 设置持久连接状态
	public function setPersistent($value)
	{
		return $this->setAttribute(PDO::ATTR_PERSISTENT,$value);
	}

	/**
	 * Returns the name of the DB driver
	 * @return string name of the DB driver
	 */
	
	// 返回数据库驱动名称，例如 mysql
	public function getDriverName()
	{
		if(($pos=strpos($this->connectionString, ':'))!==false)
			return strtolower(substr($this->connectionString, 0, $pos));
		// return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Returns the version information of the DB driver.
	 * @return string the version information of the DB driver
	 */
	
	// 返回数据库驱动的版本信息
	public function getClientVersion()
	{
		return $this->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	/**
	 * Returns the status of the connection.
	 * Some DBMS (such as sqlite) may not support this feature.
	 * @return string the status of the connection
	 */
	
	// 返回数据库连接的状态，例如 "Localhost via UNIX socket"
	// 比如 sqlite 可能不支持这个特性
	public function getConnectionStatus()
	{
		return $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	/**
	 * Returns whether the connection performs data prefetching.
	 * @return boolean whether the connection performs data prefetching
	 */
	// 返回是否支持数据预取，不明白！！！
	public function getPrefetch()
	{
		return $this->getAttribute(PDO::ATTR_PREFETCH);
	}

	/**
	 * Returns the information of DBMS server.
	 * @return string the information of DBMS server
	 */
	
	// 返回数据库信息
	public function getServerInfo()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_INFO);
	}

	/**
	 * Returns the version information of DBMS server.
	 * @return string the version information of DBMS server
	 */
	
	// 返回数据库版本信息
	public function getServerVersion()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Returns the timeout settings for the connection.
	 * @return integer timeout settings for the connection
	 */
	
	// 返回数据库连接的超时时间
	public function getTimeout()
	{
		return $this->getAttribute(PDO::ATTR_TIMEOUT);
	}

	/**
	 * Obtains a specific DB connection attribute information.
	 * @param integer $name the attribute to be queried
	 * @return mixed the corresponding attribute information
	 * @see http://www.php.net/manual/en/function.PDO-getAttribute.php
	 */
	
	// 获得一个特定的数据库链接属性信息
	public function getAttribute($name)
	{
		$this->setActive(true);
		return $this->_pdo->getAttribute($name);
	}

	/**
	 * Sets an attribute on the database connection.
	 * @param integer $name the attribute to be set
	 * @param mixed $value the attribute value
	 * @see http://www.php.net/manual/en/function.PDO-setAttribute.php
	 */
	
	// 设置数据库连接上的一个属性
	// $name 要设置的属性
	// $value 要设置的属性值
	public function setAttribute($name,$value)
	{
		// 设置pdo的属性
		if($this->_pdo instanceof PDO)
			$this->_pdo->setAttribute($name,$value);
		else
			$this->_attributes[$name]=$value;
	}

	/**
	 * Returns the attributes that are previously explicitly set for the DB connection.
	 * @return array attributes (name=>value) that are previously explicitly set for the DB connection.
	 * @see setAttributes
	 * @since 1.1.7
	 */
	
	// 返回先前为数据库连接显式设置的属性。
	// 就是你手动设置的一些属性信息
	public function getAttributes()
	{
		return $this->_attributes;
	}

	/**
	 * Sets a set of attributes on the database connection.
	 * @param array $values attributes (name=>value) to be set.
	 * @see setAttribute
	 * @since 1.1.7
	 */
	
	// 设置一组数据库链接属性
	public function setAttributes($values)
	{
		foreach($values as $name=>$value)
			$this->_attributes[$name]=$value;
	}

	/**
	 * Returns the statistical results of SQL executions.
	 * The results returned include the number of SQL statements executed and
	 * the total time spent.
	 * In order to use this method, {@link enableProfiling为true} has to be set true.
	 * @return array the first element indicates the number of SQL statements executed,
	 * and the second element the total time spent in SQL execution.
	 */
	
	// 返回sql执行的统计结果
	// 这个结果包括执行sql的数量和总共花了多少时间
	// 如果要使用这个方法，必须设置 enableProfiling 为 true ，这个在 CDbCommand.php 里会判断是否记录 profile 日志
	public function getStats()
	{
		$logger=Yii::getLogger(); // 获取日志组件
		$timings=$logger->getProfilingResults(null,'system.db.CDbCommand.query');
		$count=count($timings);
		$time=array_sum($timings);
		$timings=$logger->getProfilingResults(null,'system.db.CDbCommand.execute');
		$count+=count($timings);
		$time+=array_sum($timings);
		return array($count,$time);
	}
}
