<?php

/**
 * 测试的优惠
 * 如果购买数量大于1，就打指定的折扣
 */
class TestDiscount extends SDiscount
{

	/**
	 * 优惠几折
	 * @var integer
	 */
	public $rate = 15;

	public function apply()
	{
		foreach ($this->shoppingCart as $item)
		{
			$quantity = $item->getQuantity();
			if ($quantity > 1)
			{
				$discountPrice = $this->rate * $item->getPrice() * $quantity / 100;
				$item->setDiscountPrice($discountPrice);
			}
		}
	}

}