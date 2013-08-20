<?php

class IsHelper
{

	/**
	 * 是否邮箱
	 * @param  string $str 待验证的邮箱字符串
	 * @return boolean 是邮箱返回TRUE，否则返回FALSE
	 */
	public static function email($str)
	{
		$p = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
		return preg_match($p, $str) ? TRUE : FALSE;
	}

	/**
	 * 是有邮编
	 * @param  string $str 待验证的邮编字符串
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function zip($str)
	{
		$p = '/^\d{6}$/';
		return preg_match($p, $str) ? TRUE : FALSE;
	}

	/**
	 * 是否固定电话
	 * @param  string $str 待验证的电话字符串
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function tel($str)
	{
		$p = '/^0\d{2-3}\d{7,8}$/';
		return preg_match($p, $str) ? TRUE : FALSE;
	}

	/**
	 * 是否手机号码
	 * @param  string $str 待验证的手机号
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function phone($str)
	{
		$p = '/^1[3,4,5,8]\d{9}$/'; // 京东商城的
		return preg_match($p, $str) ? TRUE : FALSE;
	}

	/**
	 * 是否手机号码
	 * @param  string $str 待验证的手机号
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function mobile($str)
	{
		return self::phone($str);
	}

	/**
	 * 是否IPv4
	 * @param  string $str 待验证的ip
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function ipv4($str)
	{
		if (function_exists('filter_var'))
		{
			return (boolean)filter_var($str, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4));
		}
		return preg_match('/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}$/', $str) ? TRUE : FALSE;
	}

	/**
	 * 是否IPv6
	 * @param  string $str 待验证的ip
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function ipv6($str)
	{
		if (function_exists('filter_var'))
		{
			return (boolean)filter_var($str, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV6));
		}
		$p = '/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|(25[0-5]|(2[0-4]|1\d|[1-9])?\d)(\.(?7)){3})$/i';
		return preg_match($p, $str) ? TRUE : FALSE;
	}

	/**
	 * 是否ip（不区分v4、v6）
	 * @param  string $str 待验证的ip
	 * @param  integer $version ip版本号，4或6
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public function ip($str)
	{
		return self::ipv4($str) || self::ipv6($str);
	}

	/**
	 * 是否全中文
	 * @param  string $str 待验证的字符串
	 * @return boolean 是返回TRUE，否则返回FALSE
	 */
	public static function cn($str)
	{
		return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str) ? TRUE : FALSE;
	}

	/**
	 * 是否闰年，能被4整除不能被100整除或能被400整除
	 * @param  string $str 待验证的字符串
	 * @return boolean 是返回TURE，否则返回FALSE
	 */
	public static function leapyear($str)
	{
		$year = intval($str);
		return ($year%4 == 0 && $year%100 != 0) || $year%400 == 0;
	}

	/**
	 * 是否正确的日期格式（例如：2012-01-01 或者 2012-1-1）
	 * @param string $str 需要验证的日期
	 * @param boolean $standard 是否必须标准的日期
	 * @return boolean 正确返回TRUE，否则FALSE
	 */
	public static function date($str, $standard = FALSE)
	{
		if (sscanf($str, '%d-%d-%d', $year, $month, $day) == 3)
		{
			$result = checkdate($month, $day, $year);

			return $standard ? $result && preg_match('/^\d{4,}-\d{2}-\d{2}$/', $str) : $result;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 是否正确的时间格式（例如 01:01:01 或者 1:1:1）
	 * @param string $str 需要验证的时间
	 * @param boolean $standard 是否必须标准的时间
	 * @return boolean 正确返回TRUE，否则FALSE
	 */
	public static function time($str, $standard = FALSE)
	{
		if (sscanf($str, '%d:%d:%d', $hour, $minute, $second) == 3)
		{
			$result = ($hour >= 0 && $hour <= 24 && min(array($minute, $second, 0)) == 0 && max(array($minute, $second, 59)) == 59) ? TRUE : FALSE;
			return $standard ? $result && strlen($str) == 8 : $result;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 是否日期时间格式（例如：2012-01-01 00:00:00）
	 * @param string $str 需要验证的日期时间
	 * @param boolean $standard 是否必须标准的日期时间
	 * @return boolean 正确返回TRUE，否则FALSE
	 */
	public static function datetime($str, $standard = FALSE)
	{
		$arr = explode(' ', $str);
		if (count($arr) == 2)
		{
			return is_date($arr[0], $standard) && is_time($arr[1], $standard);
		}
		else
		{
			return FALSE;
		}
	}


}