<?php

/**
 * 购物车商品行为类
 */
class SShoppingCartItemBehavior extends CActiveRecordBehavior
{
	/**
	 * 商品数量
	 * @var integer
	 */
	private $quantity = 0;

	/**
	 * 是否反序列化时刷新模型数据
	 * @var boolean
	 */
	private $refresh = TRUE;

	/**
	 * 这个商品总优惠金额
	 * @var float
	 */
	private $discountPrice = 0.00;

	public function __wakeup()
	{
		// 初始化时将优惠清零，再重新计算
		$this->discountPrice = 0.00;

		if ($this->refresh === true && method_exists($this->getOwner(), 'refresh'))
		{
			$this->getOwner()->refresh();
		}
	}

	/**
	 * 返回此商品总金额
	 * @param  boolean $withDiscount 是否将优惠金额计入
	 * @return float 商品总金额
	 */
	public function getSumPrice($withDiscount = TRUE)
	{
		$totalPrice = $this->getOwner()->getPrice() * $this->quantity;
		if ($withDiscount)
		{
			$totalPrice -= $this->discountPrice;
		}
		return $totalPrice;
	}

	/**
	 * 返回商品数量
	 * @return integer 返回商品数量
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * 设置商品数量
	 * @param integer $quantity 设置商品数量
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * 设置是否刷新模型
	 * @param boolean $refresh 设置是否刷新模型
	 */
	public function setRefresh($refresh)
	{
		$this->refresh = $refresh;
	}

	/**
	 * 增加优惠金额
	 * @param float $price 增加的优惠金额
	 */
	public function addDiscountPrice($price)
	{
		$this->discountPrice += $price;
	}

	/**
	 * 设置优惠金额
	 * @param float $price 优惠金额
	 */
	public function setDiscountPrice($price)
	{
		$this->discountPrice = $price;
	}

	/**
	 * 返回优惠金额
	 * @return float 优惠金额
	 */
	public function getDiscountPrice()
	{
		return $this->discountPrice;
	}

	/**
	 * 返回是否已经有优惠
	 * @return boolean 是否已经有优惠
	 */
	public function getHasDiscount()
	{
		return (boolean)$this->discountPrice;
	}
}