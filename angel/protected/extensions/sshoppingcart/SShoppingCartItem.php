<?php

/**
 * 购物车商品接口
 */
interface SShoppingCartItem
{
	/**
	 * 商品唯一标识，用于作为购物车键名
	 * @return mixed 返回商品唯一标识
	 */
	public function getId();

	/**
	 * 商品单价
	 * @return float 返回商品单价
	 */
	public function getPrice();
}