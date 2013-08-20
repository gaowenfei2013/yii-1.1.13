<?php

/**
 * oauth2 userinfo
 */
class OAuth2_UserInfo extends OAuth2_ArrayAccess
{

	const MAN = 1;
	
	const WOMEN = 2;
	
	const SECRET = 0;

	/**
	 * via
	 * @var string
	 */
	public $via;

	/**
	 * uid
	 * @var string
	 */
	public $uid;
	
	/**
	 * screen_name
	 * @var [type]
	 */
	public $screen_name;

	/**
	 * avatar
	 * @var string
	 */
	public $avatar;

	/**
	 * gender
	 * @var integer
	 */
	public $gender = self::SECRET;

	/**
	 * email
	 * @var string
	 */
	public $email;

	/**
	 * birthday, such as 1999-01-01
	 * @var string
	 */
	public $birthday;

	/**
	 * location, separated by a space
	 * @var string
	 */
	public $location;

	/**
	 * access_token
	 * @var string
	 */
	public $access_token;

	/**
	 * expires_in
	 * @var integer
	 */
	public $expires_in;

	/**
	 * refresh_token
	 * @var string
	 */
	public $refresh_token;

	public function __construct($config)
	{
		if ($config)
		{
			foreach ($config as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

}