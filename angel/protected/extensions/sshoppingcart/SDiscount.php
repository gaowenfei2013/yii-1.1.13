<?php

/**
 * 优惠基类
 */
abstract class SDiscount
{
	protected $shoppingCart;

	public function setShoppingCart(SShoppingCart $shoppingCart)
	{
		$this->shoppingCart = $shoppingCart;
	}

	/**
	 * 应用优惠
	 */
	abstract public function apply();
}