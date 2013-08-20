<?php
/**
 * This file contains classes implementing list feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CList implements an integer-indexed collection class.
 *
 * You can access, append, insert, remove an item by using
 * {@link itemAt}, {@link add}, {@link insertAt}, {@link remove}, and {@link removeAt}.
 * To get the number of the items in the list, use {@link getCount}.
 * CList can also be used like a regular array as follows,
 * <pre>
 * $list[]=$item;  // append at the end
 * $list[$index]=$item; // $index must be between 0 and $list->Count
 * unset($list[$index]); // remove the item at $index
 * if(isset($list[$index])) // if the list has an item at $index
 * foreach($list as $index=>$item) // traverse each item in the list
 * $n=count($list); // returns the number of items in the list
 * </pre>
 *
 * To extend CList by doing additional operations with each addition or removal
 * operation (e.g. performing type check), override {@link insertAt()}, and {@link removeAt()}.
 *
 * @property boolean $readOnly Whether this list is read-only or not. Defaults to false.
 * @property Iterator $iterator An iterator for traversing the items in the list.
 * @property integer $count The number of items in the list.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.collections
 * @since 1.0
 */

// CList实现了一个有序的集合类
// 键名是数字，且唯一
// 值可以不唯一
class CList extends CComponent implements IteratorAggregate,ArrayAccess,Countable
{
	/**
	 * @var array internal data storage
	 */
	private $_d=array(); // 数据
	/**
	 * @var integer number of items
	 */
	private $_c=0; // 数据总数目
	/**
	 * @var boolean whether this list is read-only
	 */
	private $_r=false; // 是否只读

	/**
	 * Constructor.
	 * Initializes the list with an array or an iterable object.
	 * @param array $data the initial data. Default is null, meaning no initialization.
	 * @param boolean $readOnly whether the list is read-only
	 * @throws CException If data is not null and neither an array nor an iterator.
	 */
	// 构造函数
	// 使用数组或可迭代对象填充列表
	public function __construct($data=null,$readOnly=false)
	{
		if($data!==null)
			$this->copyFrom($data);
		$this->setReadOnly($readOnly);
	}

	/**
	 * @return boolean whether this list is read-only or not. Defaults to false.
	 */
	
	// 返回是否只读
	public function getReadOnly()
	{
		return $this->_r;
	}

	/**
	 * @param boolean $value whether this list is read-only or not
	 */
	
	// 设置是否只读
	protected function setReadOnly($value)
	{
		$this->_r=$value;
	}

	/**
	 * Returns an iterator for traversing the items in the list.
	 * This method is required by the interface IteratorAggregate.
	 * @return Iterator an iterator for traversing the items in the list.
	 */
	
	// 返回这个列表的迭代器
	public function getIterator()
	{
		return new CListIterator($this->_d);
	}

	/**
	 * Returns the number of items in the list.
	 * This method is required by Countable interface.
	 * @return integer number of items in the list.
	 */
	
	// 计算集合数据总数
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the list.
	 * @return integer the number of items in the list
	 */
	
	// 同上
	public function getCount()
	{
		return $this->_c;
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is exactly the same as {@link offsetGet}.
	 * @param integer $index the index of the item
	 * @return mixed the item at the index
	 * @throws CException if the index is out of the range
	 */
	
	// 返回指定位置的项
	// 这个方法同 offsetGet 完全一样
	public function itemAt($index)
	{
		if(isset($this->_d[$index]))
			return $this->_d[$index];
		elseif($index>=0 && $index<$this->_c) // in case the value is null
			return $this->_d[$index];
		else
			throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
				array('{index}'=>$index)));
	}

	/**
	 * Appends an item at the end of the list.
	 * @param mixed $item new item
	 * @return integer the zero-based index at which the item is added
	 */
	
	// 向列表最后添加一个项
	// 返回添加项索引
	public function add($item)
	{
		$this->insertAt($this->_c,$item);
		return $this->_c-1;
	}

	/**
	 * Inserts an item at the specified position.
	 * Original item at the position and the next items
	 * will be moved one step towards the end.
	 * @param integer $index the specified position.
	 * @param mixed $item new item
	 * @throws CException If the index specified exceeds the bound or the list is read-only
	 */
	
	// 向列表指定位置添加项
	// 原始位置的数据包括后面的数据将向后移动一个位置
	public function insertAt($index,$item)
	{
		if(!$this->_r)
		{
			if($index===$this->_c)
				$this->_d[$this->_c++]=$item;
			elseif($index>=0 && $index<$this->_c)
			{
				array_splice($this->_d,$index,0,array($item));
				$this->_c++;
			}
			else
				throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
					array('{index}'=>$index)));
		}
		else
			throw new CException(Yii::t('yii','The list is read only.'));
	}

	/**
	 * Removes an item from the list.
	 * The list will first search for the item.
	 * The first item found will be removed from the list.
	 * @param mixed $item the item to be removed.
	 * @return integer the index at which the item is being removed
	 * @throws CException If the item does not exist
	 */
	
	// 从列表中删除指定值的项
	// 从列表的开头开始搜索，查找到的第一个匹配项将被删除
	// 删除成功返回那个项的索引，否则返回false
	public function remove($item)
	{
		if(($index=$this->indexOf($item))>=0)
		{
			$this->removeAt($index);
			return $index;
		}
		else
			return false;
	}

	/**
	 * Removes an item at the specified position.
	 * @param integer $index the index of the item to be removed.
	 * @return mixed the removed item.
	 * @throws CException If the index specified exceeds the bound or the list is read-only
	 */
	
	// 删除指定位置的项 如果成功返回删除项的值
	public function removeAt($index)
	{
		if(!$this->_r)
		{
			if($index>=0 && $index<$this->_c)
			{
				$this->_c--;
				if($index===$this->_c)
					return array_pop($this->_d);
				else
				{
					$item=$this->_d[$index];
					array_splice($this->_d,$index,1);
					return $item;
				}
			}
			else
				throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
					array('{index}'=>$index)));
		}
		else
			throw new CException(Yii::t('yii','The list is read only.'));
	}

	/**
	 * Removes all items in the list.
	 */
	
	// 清空整个列表
	public function clear()
	{
		for($i=$this->_c-1;$i>=0;--$i)
			$this->removeAt($i);
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the list contains the item
	 */
	
	// 返回列表是否包含值为$item的项
	public function contains($item)
	{
		return $this->indexOf($item)>=0;
	}

	/**
	 * @param mixed $item the item
	 * @return integer the index of the item in the list (0 based), -1 if not found.
	 */
	
	// 返回指定项在列表中的位置 找不到返回 -1
	public function indexOf($item)
	{
		if(($index=array_search($item,$this->_d,true))!==false)
			return $index;
		else
			return -1;
	}

	/**
	 * @return array the list of items in array
	 */
	
	// 返回列表的数组形式
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the list.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or object implementing Traversable
	 * @throws CException If data is neither an array nor a Traversable.
	 */
	
	// 复制数据到列表
	// 注意，首先列表中数据将被清空
	// $data 必须是一个数组或者是一个实现了Traversable接口的对象
	public function copyFrom($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			if($this->_c>0)
				$this->clear();
			if($data instanceof CList)
				$data=$data->_d;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','List data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Merges iterable data into the map.
	 * New data will be appended to the end of the existing data.
	 * @param mixed $data the data to be merged with, must be an array or object implementing Traversable
	 * @throws CException If data is neither an array nor an iterator.
	 */
	
	// 合并数据
	// $data 必须是一个数组或者是一个实现了Traversable接口的对象
	public function mergeWith($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			if($data instanceof CList)
				$data=$data->_d;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','List data must be an array or an object implementing Traversable.'));
	}

	// 下面offset开头的方法实现ArrayAccess接口 可以通过数组方法来判断、访问、设置、删除列表元素

	/**
	 * Returns whether there is an item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to check on
	 * @return boolean
	 */
	
	// 返回指定偏移是否存在
	public function offsetExists($offset)
	{
		return ($offset>=0 && $offset<$this->_c);
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve item.
	 * @return mixed the item at the offset
	 * @throws CException if the offset is invalid
	 */
	
	// 返回指定偏移的值
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	/**
	 * Sets the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set item
	 * @param mixed $item the item value
	 */
	
	// 设置指定偏移项的值
	public function offsetSet($offset,$item)
	{
		if($offset===null || $offset===$this->_c)
			$this->insertAt($this->_c,$item);
		else
		{
			$this->removeAt($offset);
			$this->insertAt($offset,$item);
		}
	}

	/**
	 * Unsets the item at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to unset item
	 */
	
	// 删除指定偏移项的值
	public function offsetUnset($offset)
	{
		$this->removeAt($offset);
	}
}

