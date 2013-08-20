<?php
/**
 * This file contains classes implementing the queue feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CQueue implements a queue.
 *
 * The typical queue operations are implemented, which include
 * {@link enqueue()}, {@link dequeue()} and {@link peek()}. In addition,
 * {@link contains()} can be used to check if an item is contained
 * in the queue. To obtain the number of the items in the queue,
 * check the {@link getCount Count} property.
 *
 * Items in the queue may be traversed using foreach as follows,
 * <pre>
 * foreach($queue as $item) ...
 * </pre>
 *
 * @property Iterator $iterator An iterator for traversing the items in the queue.
 * @property integer $count The number of items in the queue.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.collections
 * @since 1.0
 */

// CQueue实现了一个队列类
class CQueue extends CComponent implements IteratorAggregate,Countable
{
	/**
	 * internal data storage
	 * @var array
	 */
	private $_d=array(); // 保存的数据
	/**
	 * number of items
	 * @var integer
	 */
	private $_c=0; // 数据总数

	/**
	 * Constructor.
	 * Initializes the queue with an array or an iterable object.
	 * @param array $data the intial data. Default is null, meaning no initialization.
	 * @throws CException If data is not null and neither an array nor an iterator.
	 */
	public function __construct($data=null)
	{
		if($data!==null)
			$this->copyFrom($data);
	}

	/**
	 * @return array the list of items in queue
	 */
	
	// 返回队列的数组形式
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the queue.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or object implementing Traversable
	 * @throws CException If data is neither an array nor a Traversable.
	 */
	
	// 复制数据到队列中 首先会清空队列
	public function copyFrom($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			$this->clear();
			foreach($data as $item)
			{
				$this->_d[]=$item;
				++$this->_c;
			}
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','Queue data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Removes all items in the queue.
	 */
	
	// 清空整个队列
	public function clear()
	{
		$this->_c=0;
		$this->_d=array();
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the queue contains the item
	 */
	
	// 判断队列中是否包含指定名称的数据 （名称严格判断类型）
	public function contains($item)
	{
		return array_search($item,$this->_d,true)!==false;
	}

	/**
	 * Returns the item at the top of the queue.
	 * @return mixed item at the top of the queue
	 * @throws CException if the queue is empty
	 */
	
	// 返回处于队列头部的项目
	public function peek()
	{
		if($this->_c===0)
			throw new CException(Yii::t('yii','The queue is empty.'));
		else
			return $this->_d[0];
	}

	/**
	 * Removes and returns the object at the beginning of the queue.
	 * @return mixed the item at the beginning of the queue
	 * @throws CException if the queue is empty
	 */
	
	// 出列
	public function dequeue()
	{
		if($this->_c===0)
			throw new CException(Yii::t('yii','The queue is empty.'));
		else
		{
			--$this->_c;
			return array_shift($this->_d);
		}
	}

	/**
	 * Adds an object to the end of the queue.
	 * @param mixed $item the item to be appended into the queue
	 */
	
	// 进列
	public function enqueue($item)
	{
		++$this->_c;
		$this->_d[]=$item;
	}

	/**
	 * Returns an iterator for traversing the items in the queue.
	 * This method is required by the interface IteratorAggregate.
	 * @return Iterator an iterator for traversing the items in the queue.
	 */
	
	// 返回队列的迭代器对象
	public function getIterator()
	{
		return new CQueueIterator($this->_d);
	}

	/**
	 * Returns the number of items in the queue.
	 * @return integer the number of items in the queue
	 */
	
	// 返回队列中项目总数
	public function getCount()
	{
		return $this->_c;
	}

	/**
	 * Returns the number of items in the queue.
	 * This method is required by Countable interface.
	 * @return integer number of items in the queue.
	 */
	
	// 实现CountAble接口 可以count($queue) 来返回队列中项目总数
	public function count()
	{
		return $this->getCount();
	}
}
