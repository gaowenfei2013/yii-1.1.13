<?php

/**
 * oauth2 exception class
 */
class OAuth2_Exception extends Exception
{

	public function __construct(Array $result)
	{
		foreach ($result as $key => $value)
		{
			if (is_numeric($value) && ! $this->code)
			{
				$this->code = $value;
				unset($result[$key]);
			}
		}

		if (isset($result['message']))
		{
			$this->message = $result['message'];
		}
		elseif (isset($result['error_description']))
		{
			$this->message = $result['error_description'];
		}
		elseif (isset($result['error']))
		{
			$this->message = $result['error'];
		}
		elseif (isset($result['msg']))
		{
			$this->message = $result['msg'];
		}
		elseif (isset($result['error_msg']))
		{
			$this->message = $result['error_msg'];
		}
	}

}