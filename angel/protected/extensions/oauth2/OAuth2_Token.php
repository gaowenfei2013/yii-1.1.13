<?php

/**
 * oauth2 token
 */
class OAuth2_Token extends OAuth2_ArrayAccess
{

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

	/**
	 * original token
	 * @var array
	 */
	public $original;

}

