<?php

/**
 * a base class for array access
 */
abstract Class OAuth2_ArrayAccess implements ArrayAccess
{

	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	public function offsetSet($offset,$item)
	{
		$this->$offset = $item;
	}

	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

}