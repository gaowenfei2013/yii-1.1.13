<?php

require_once dirname(__FILE__) . '/OAuth2_ArrayAccess.php';
require_once dirname(__FILE__) . '/OAuth2_Exception.php';
require_once dirname(__FILE__) . '/OAuth2_Token.php';
require_once dirname(__FILE__) . '/OAuth2_UserInfo.php';
require_once dirname(__FILE__) . '/OAuth2_Provider.php';

/**
 * oauth2 factory
 */
class OAuth2
{
	/**
	 * create provider instance
	 * @param  string $provider provider name
	 * @param  array $config config for provider
	 * @return object provider instance
	 */
	public static function create($provider, $config)
	{
		$provider = strtolower($provider);

		if (isset($config['enabled']) && ! $config['enabled'])
		{
			throw new OAuth2_Exception(array('message' => 'Provider ' . $provider . ' unavailable.'));
		}

		$class    = 'OAuth2_' . ucfirst($provider);

		$classFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'providers' . DIRECTORY_SEPARATOR . $class . '.php';

		if ( ! is_file($classFile))
		{
			throw new OAuth2_Exception(array('message' => 'OAuth2 provider ' . $provider . ' not found.'));
		}

		require_once $classFile;

		return new $class($config);
	}

}