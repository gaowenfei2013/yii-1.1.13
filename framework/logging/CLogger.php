<?php
/**
 * CLogger class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogger records log messages in memory.
 *
 * CLogger implements the methods to retrieve the messages with
 * various filter conditions, including log levels and log categories.
 *
 * @property array $logs List of messages. Each array element represents one message
 * with the following structure:
 * array(
 *   [0] => message (string)
 *   [1] => level (string)
 *   [2] => category (string)
 *   [3] => timestamp (float, obtained by microtime(true));.
 * @property float $executionTime The total time for serving the current request.
 * @property integer $memoryUsage Memory usage of the application (in bytes).
 * @property array $profilingResults The profiling results.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.logging
 * @since 1.0
 */

// 记录日志信息到内存中（也就是保存在变量中）
class CLogger extends CComponent
{
	// 一些级别
	const LEVEL_TRACE='trace';
	const LEVEL_WARNING='warning';
	const LEVEL_ERROR='error';
	const LEVEL_INFO='info';
	const LEVEL_PROFILE='profile';

	/**
	 * @var integer how many messages should be logged before they are flushed to destinations.
	 * Defaults to 10,000, meaning for every 10,000 messages, the {@link flush} method will be
	 * automatically invoked once. If this is 0, it means messages will never be flushed automatically.
	 * @since 1.1.0
	 */
	
	// 当信息数达到多少条时应当被刷新到目的地。
	// 默认10000，就是说每10000条信息，flush信息将被调用一次。如果为0，意味着永远不会被刷新到目的地
	public $autoFlush=10000;
	/**
	 * @var boolean this property will be passed as the parameter to {@link flush()} when it is
	 * called in {@link log()} due to the limit of {@link autoFlush} being reached.
	 * By default, this property is false, meaning the filtered messages are still kept in the memory
	 * by each log route after calling {@link flush()}. If this is true, the filtered messages
	 * will be written to the actual medium each time {@link flush()} is called within {@link log()}.
	 * @since 1.1.8
	 */
	public $autoDump=false;
	/**
	 * @var array log messages
	 */
	private $_logs=array(); // 所有日志信息
	/**
	 * @var integer number of log messages
	 */
	private $_logCount=0; // 日志信息总数
	/**
	 * @var array log levels for filtering (used when filtering)
	 */
	private $_levels; // 需要过滤掉的日志级别
	/**
	 * @var array log categories for filtering (used when filtering)
	 */
	private $_categories; // 需要过滤掉的日志类别
	/**
	 * @var array log categories for excluding from filtering (used when filtering)
	 */
	private $_except=array(); // 不需要过滤的日志类别
	/**
	 * @var array the profiling results (category, token => time in seconds)
	 */
	private $_timings; // 分析的结果
	/**
	* @var boolean if we are processing the log or still accepting new log messages
	* @since 1.1.9
	*/
	private $_processing=false; // 是否我们正在处理日志或者接受一个日志

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved back via {@link getLogs}.
	 * @param string $message message to be logged
	 * @param string $level level of the message (e.g. 'Trace', 'Warning', 'Error'). It is case-insensitive.
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 * @see getLogs
	 */
	
	// 记录一个日志信息
	// 通过这个方法记录的日志可以通过getLogs检索出来
	public function log($message,$level='info',$category='application')
	{
		$this->_logs[]=array($message,$level,$category,microtime(true)); // 保存每条日志信息到_logs变量中
		$this->_logCount++; // 记录日志总数

		// 如果authFlush大于0，并且日志总数大于autoFlush，并且不在处理中
		if($this->autoFlush>0 && $this->_logCount>=$this->autoFlush && !$this->_processing)
		{
			$this->_processing=true; // 设置正在处理中
			$this->flush($this->autoDump); // 刷新到目的地
			$this->_processing=false; // 设置处理结束
		}
	}

	/**
	 * Retrieves log messages.
	 *
	 * Messages may be filtered by log levels and/or categories.
	 * A level filter is specified by a list of levels separated by comma or space
	 * (e.g. 'trace, error'). A category filter is similar to level filter
	 * (e.g. 'system, system.web'). A difference is that in category filter
	 * you can use pattern like 'system.*' to indicate all categories starting
	 * with 'system'.
	 *
	 * If you do not specify level filter, it will bring back logs at all levels.
	 * The same applies to category filter.
	 *
	 * Level filter and category filter are combinational, i.e., only messages
	 * satisfying both filter conditions will be returned.
	 *
	 * @param string $levels level filter
	 * @param string $categories category filter
	 * @return array list of messages. Each array element represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	
	// 检索日志消息 可以通过日志级别和日志类别进行过滤 返回指定级别和类别的日志信息
	// 一个日志级别过滤器是以逗号分隔的级别字符串 如 'trace,error'
	// 一个类别过滤器是指逗号分割的分类字符串 如'system,system.web'
	// 你可以使用system.*过滤器返回所有以system开头的类别
	// 如果你没指定日志级别过滤器，将返回所有级别的日志，同理类别过滤器也一样
	// 同时满足日志级别过滤器和类别过滤器的才会被返回
	public function getLogs($levels='',$categories=array(), $except=array())
	{
		// 记录需要过滤的日志级别 数组形式 如array('trace', 'error')
		$this->_levels=preg_split('/[\s,]+/',strtolower($levels),-1,PREG_SPLIT_NO_EMPTY);

		// 记录需要过滤的分类 可以是一个数组 或 逗号分割的字符串
		if (is_string($categories))
			$this->_categories=preg_split('/[\s,]+/',strtolower($categories),-1,PREG_SPLIT_NO_EMPTY);
		else
			$this->_categories=array_filter(array_map('strtolower',$categories));

		// 记录不需要过滤的分类 比如我们定义了system.* 这就要返回所有以system开头的类别日志
		// 但是我们不想返回system.a 那么这个就有用了 我们可以设置此值为 array('system.a') 告诉不要不要返回这个类别的
		if (is_string($except))
			$this->_except=preg_split('/[\s,]+/',strtolower($except),-1,PREG_SPLIT_NO_EMPTY);
		else
			$this->_except=array_filter(array_map('strtolower',$except));

		$ret=$this->_logs; // 复制一份过滤前所有的日志

		// 日志级别过滤
		if(!empty($levels))
			$ret=array_values(array_filter($ret,array($this,'filterByLevel')));

		// 日志分类过滤
		if(!empty($this->_categories) || !empty($this->_except))
			$ret=array_values(array_filter($ret,array($this,'filterByCategory')));

		return $ret; // 返回过滤后的日志信息（数组）
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array $value element to be filtered
	 * @return boolean true if valid log, false if not.
	 */
	
	// getLogs中使用的过滤函数 日志类别过滤
	private function filterByCategory($value)
	{
		return $this->filterAllCategories($value, 2);
	}

	/**
	 * Filter function used by {@link getProfilingResults}
	 * @param array $value element to be filtered
	 * @return boolean true if valid timing entry, false if not.
	 */
	
	// getProfilingResults中使用的过滤函数
	private function filterTimingByCategory($value)
	{
		return $this->filterAllCategories($value, 1);
	}

	/**
	 * Filter function used to filter included and excluded categories
	 * @param array $value element to be filtered
	 * @param integer index of the values array to be used for check
	 * @return boolean true if valid timing entry, false if not.
	 */
	
	// 过滤函数用于过滤包括和排除类别
	// 返回true 表示通过过滤器过滤了 false表示没通过
	private function filterAllCategories($value, $index)
	{
		$cat=strtolower($value[$index]); // 这个日志消息的类别
		$ret=empty($this->_categories); // 如果类别过滤器为空 肯定通过啊
		foreach($this->_categories as $category)
		{
			// 类别过滤通过 设置$ret = true
			if($cat===$category || (($c=rtrim($category,'.*'))!==$category && strpos($cat,$c)===0))
				$ret=true;
		}

		// 上面的通过了
		// 过滤我们需要去除的类别
		if($ret)
		{
			foreach($this->_except as $category)
			{
				if($cat===$category || (($c=rtrim($category,'.*'))!==$category && strpos($cat,$c)===0))
					$ret=false;
			}
		}
		return $ret;
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array $value element to be filtered
	 * @return boolean true if valid log, false if not.
	 */
	
	// 日志级别过滤
	private function filterByLevel($value)
	{
		return in_array(strtolower($value[1]),$this->_levels);
	}

	/**
	 * Returns the total time for serving the current request.
	 * This method calculates the difference between now and the timestamp
	 * defined by constant YII_BEGIN_TIME.
	 * To estimate the execution time more accurately, the constant should
	 * be defined as early as possible (best at the beginning of the entry script.)
	 * @return float the total time for serving the current request.
	 */
	
	// 返回总执行时间
	// 如果需要时间更准确 需要尽早的设置YII_BEGIN_TIME
	public function getExecutionTime()
	{
		return microtime(true)-YII_BEGIN_TIME;
	}

	/**
	 * Returns the memory usage of the current application.
	 * This method relies on the PHP function memory_get_usage().
	 * If it is not available, the method will attempt to use OS programs
	 * to determine the memory usage. A value 0 will be returned if the
	 * memory usage can still not be determined.
	 * @return integer memory usage of the application (in bytes).
	 */
	
	// 返回应用消耗的内存
	public function getMemoryUsage()
	{
		if(function_exists('memory_get_usage'))
			return memory_get_usage();
		else
		{
			$output=array();
			if(strncmp(PHP_OS,'WIN',3)===0) // windows服务器
			{
				exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST',$output);
				return isset($output[5])?preg_replace('/[\D]/','',$output[5])*1024 : 0;
			}
			else
			{
				$pid=getmypid();
				exec("ps -eo%mem,rss,pid | grep $pid", $output);
				$output=explode("  ",$output[0]);
				return isset($output[1]) ? $output[1]*1024 : 0;
			}
		}
	}

	/**
	 * Returns the profiling results.
	 * The results may be filtered by token and/or category.
	 * If no filter is specified, the returned results would be an array with each element
	 * being array($token,$category,$time).
	 * If a filter is specified, the results would be an array of timings.
	 *
	 * Since 1.1.11, filtering results by category supports the same format used for filtering logs in
	 * {@link getLogs}, and similarly supports filtering by multiple categories and wildcard.
	 * @param string $token token filter. Defaults to null, meaning not filtered by token.
	 * @param string $categories category filter. Defaults to null, meaning not filtered by category.
	 * @param boolean $refresh whether to refresh the internal timing calculations. If false,
	 * only the first time calling this method will the timings be calculated internally.
	 * @return array the profiling results.
	 */
	
	// 返回分析结果 
	// 返回分析结果。 此结果可能被令牌and/or类别过滤。 如果没有指定过滤器,返回结果将是每个元素都是数组的数组($token,$category,$time)。
	// $token 令牌过滤器。默认为null，这意味着没有令牌过滤。
	// $categories 类别过滤器。默认为null，这意味着没有类别过滤。
	// $refresh 是否刷新内部的时间计算。 如果为false，仅仅第一次调用这个方法进行内部的时间计算。
	public function getProfilingResults($token=null,$categories=null,$refresh=false)
	{
		if($this->_timings===null || $refresh)
			$this->calculateTimings();
		if($token===null && $categories===null)
			return $this->_timings;

		$timings = $this->_timings;
		if($categories!==null) {
			$this->_categories=preg_split('/[\s,]+/',strtolower($categories),-1,PREG_SPLIT_NO_EMPTY);
			$timings=array_filter($timings,array($this,'filterTimingByCategory'));
		}

		$results=array();
		foreach($timings as $timing)
		{
			if($token===null || $timing[0]===$token)
				$results[]=$timing[2];
		}
		return $results;
	}

	private function calculateTimings()
	{
		$this->_timings=array();

		$stack=array();
		foreach($this->_logs as $log)
		{
			if($log[1]!==CLogger::LEVEL_PROFILE)
				continue;
			list($message,$level,$category,$timestamp)=$log;
			if(!strncasecmp($message,'begin:',6))
			{
				$log[0]=substr($message,6);
				$stack[]=$log;
			}
			elseif(!strncasecmp($message,'end:',4))
			{
				$token=substr($message,4);
				if(($last=array_pop($stack))!==null && $last[0]===$token)
				{
					$delta=$log[3]-$last[3];
					$this->_timings[]=array($message,$category,$delta);
				}
				else
					throw new CException(Yii::t('yii','CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.',
						array('{token}'=>$token)));
			}
		}

		$now=microtime(true);
		while(($last=array_pop($stack))!==null)
		{
			$delta=$now-$last[3];
			$this->_timings[]=array($last[0],$last[2],$delta);
		}
	}

	/**
	 * Removes all recorded messages from the memory.
	 * This method will raise an {@link onFlush} event.
	 * The attached event handlers can process the log messages before they are removed.
	 * @param boolean $dumpLogs whether to process the logs immediately as they are passed to log route
	 * @since 1.1.0
	 */
	
	// 从内存从删除所有日志信息
	// 这个方法将触发onFlush事件
	public function flush($dumpLogs=false)
	{
		$this->onFlush(new CEvent($this, array('dumpLogs'=>$dumpLogs)));
		// 清空
		$this->_logs=array();
		$this->_logCount=0;
	}

	/**
	 * Raises an <code>onFlush</code> event.
	 * @param CEvent $event the event parameter
	 * @since 1.1.0
	 */
	// 刷新给日志路由
	// 让他们记录或者显示
	// 这个在 CLogRouter 里面向这个组件注册了事件
	public function onFlush($event)
	{
		$this->raiseEvent('onFlush', $event);
	}
}
