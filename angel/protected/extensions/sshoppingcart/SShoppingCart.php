<?php

/**
 * 购物车
 */
class SShoppingCart extends CMap
{

	/**
	 * 用户购物车缓存键前缀
	 */
	const SShoppingCART_USER_CART_PREFIX = 'SShoppingCART_USER_CART_PREFIX';

	/**
	 * 购物车保存的 session key
	 * @var [type]
	 */
	public $cartId = __CLASS__;

	/**
	 * 是否在恢复购物车时刷新模型
	 * @var boolean
	 */
	public $refresh = TRUE;

	/**
	 * 缓存组件名
	 * @var string
	 */
	public $cacheID = 'cache';

	/**
	 * 应用的优惠
	 * @var array
	 */
	public $discounts = array();

	/**
	 * 购物车优惠金额（不同于商品优惠金额）
	 * @var float
	 */
	public $discountPrice = 0.0;

	private $_cache;

	/**
	 * 初始化购物车
	 */
	public function init()
	{
		$this->restoreFromSession();
		$this->restoreFromCache();
		$this->applyDiscounts();
	}

	/**
	 * 从session中恢复购物车信息
	 */
	protected function restoreFromSession()
	{
		$data = unserialize(Yii::app()->getUser()->getState($this->cartId));
		if (is_array($data) || $data instanceof Traversable)
		{
			foreach ($data as $key => $item)
			{
				if ($item instanceof SShoppingCartItem)
				{
					parent::add($key, $item);
				}
			}
		}
	}

	/**
	 * 从缓存中恢复
	 */
	protected function restoreFromCache()
	{
		$this->_cache = Yii::app()->getComponent($this->cacheID);
		if( ! ($this->_cache instanceof ICache))
		{
			return;
		}

		if ( ! Yii::app()->getUser()->getIsGuest())
		{
			$data = unserialize($this->_cache->get(SShoppingCART_USER_CART_PREFIX . Yii::app()->getUser()->getId()));
			if (is_array($data) || $data instanceof Traversable)
			{
				foreach ($data as $key => $item)
				{
					if ( ! ($item instanceof SShoppingCartItem))
					{
						continue;
					}

					if ($this->contains($key))
					{
						$this->itemAt($key)->setQuantity($item->getQuantity());
					}
					else
					{
						parent::add($key, $item);
					}
				}
			}
		}
	}

	/**
	 * 应用折扣
	 */
	protected function applyDiscounts()
	{
		foreach ($this->discounts as $discount)
		{
			$discount = Yii::createComponent($discount);
			if ( ! ($discount instanceof SDiscount))
			{
				throw new CException('折扣类' . get_class($discount) . '必须继承与SDiscount');
			}
			$discount->setShoppingCart($this);
			$discount->apply();
		}
	}

	/**
	 * 保存购物车
	 */
	public function save()
	{
		$data = serialize($this->toArray());

		// 如果登录状态，将其保存到用户的购物车缓存中，并将session中的删除掉
		// 否则保存入session中
		if ( ! Yii::app()->getUser()->getIsGuest())
		{
			Yii::app()->getUser()->setState($this->cartId, NULL, NULL);
			$this->_cache->set(SShoppingCART_USER_CART_PREFIX . Yii::app()->getUser()->getId());
		}
		else
		{
			Yii::app()->getUser()->setState($this->cartId, $data);
		}
    }

    /**
     * 返回购物车总金额
     * @param  boolean $withDiscount 是否将折扣算入
     * @return float 购物车总金额
     */
    public function getTotalPrice($withDiscount = TRUE)
    {
    	$totalPrice = 0.00;

		foreach ($this as $item)
		{
			$totalPrice += $item->getSumPrice($withDiscount);
		}

		if($withDiscount)
		{
			$totalPrice -= $this->discountPrice;
		}

		return $totalPrice;
    }

    /**
     * 返回总商品数量
     * @return integer 商品总数
     */
    public function getItemsCount()
    {
		$count = 0;
		foreach ($this as $item)
		{
			$count += $item->getQuantity();
		}
		return $count;
    }

	/**
	 * 加入购物车
	 * @param  SShoppingCartItem $item 购物车商品
	 * @param  integer $quantity 数量
	 * @return boolean 返回是否加入成功
	 */
	public function put(SShoppingCartItem $item, $quantity = 1)
	{
		$key = $item->getId();

		// 如果存在，更新数量
		if ($this->contains($key))
		{
			$item    = $this->itemAt($key);
			$oldQuantity = $item->getQuantity();
			$quantity    += $oldQuantity;
		}

		$this->update($item, $quantity);
	}

	/**
	 * 从购物车删除
	 * @param string $key 购物车商品记录KEY
	 * @return boolean 返回是否删除成功
	 */
	public function remove($key)
	{
		parent::remove($key);
		$this->applyDiscounts();
		$this->onRemoveItem(new CEvent($this));
		$this->save();
	}

	/**
	 * 更新购物车商品
	 * @param  SShoppingCartItem $item 购物车商品
	 * @param  integer $quantity 更新的数量
	 * @return boolean 返回是否更新成功
	 */
	public function update(SShoppingCartItem $item, $quantity = 1)
	{
		if ( ! ($item instanceof CActiveRecord))
		{
			throw new InvalidArgumentException('第一个参数必须是活动记录！');
		}
		
		$key = $item->getId();
		$item->detachBehavior("SShoppingCartItemBehavior");
        $item->attachBehavior("SShoppingCartItemBehavior", new SShoppingCartItemBehavior());
        $item->setRefresh($this->refresh);
		$item->setQuantity($quantity);

		if ($item->getQuantity() < 1)
		{
			$this->remove($key);
		}
		else
		{
			parent::add($key, $item);
		}

		$this->applyDiscounts();
		$this->onUpdateItem(new CEvent($this));
		$this->save();
	}

	/**
	 * 清空购物车
	 * @param $save 是否清空后自动保存
	 * @return boolean 返回是否清空成功
	 */
	public function clear($save = TRUE)
	{
		parent::clear();
		if ($save)
		{
			$this->save();
		}
	}

	/**
	 * 购物车所有商品
	 * @return array 购物车所有商品
	 */
	public function getItems()
	{
		return $this->toArray();
	}

	/**
	 * 购物车是否空的
	 * @return boolean 返回购物车是否为空
	 */
	public function getIsEmpty()
	{
		return !(boolean)$this->getCount();
	}

	/**
	 * 设置购物车优惠金额
	 */
	public function setDiscountPrice($discountPrice)
	{
		$this->discountPrice = $discountPrice;
	}

	/**
	 * 返回购物车优惠金额
	 * @return float 购物车优惠金额
	 */
	public function getDiscountPrice()
	{
		return $this->discountPrice;
	}

	/**
	 * 更新购物车商品事件
	 * @param  CEvent $event 事件
	 */
	public function onUpdateItem($event)
	{
		$this->raiseEvent('onUpdateItem', $event);
	}

	/**
	 * 删除购物车商品事件
	 * @param  CEvent $event 事件
	 */
	public function onRemoveItem($event)
	{
		$this->raiseEvent('onRemoveItem', $event);
	}

}