<?php

class SOAuth2_Kaixin extends SOAuth2
{

	public $authorizeUrl = 'http://api.kaixin001.com/oauth2/authorize';

	public $accessTokenUrl = 'https://api.kaixin001.com/oauth2/access_token';

	public $userInfoUrl = 'https://api.kaixin001.com/users/me.json';

	public $requestType = self::POST;

	public $display;

	public $forcelogin;

	public $oauthClient;

	protected function authorizeParams()
	{
		$method = __FUNCTION__;
		$params = parent::$method();

		if (isset($this->display))
		{
			$params['display'] = $this->display;
		}
		
		if (isset($this->forcelogin))
		{
			$params['forcelogin'] = $this->forcelogin;
		}

		if (isset($this->oauthClient))
		{
			$params['oauth_client'] = $this->oauthClient;
		}

		return $params;
	}

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
			'fields' => 'uid,name,gender,logo120',
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			$this->setError($userInfo);
			return FALSE;
		}

		return $this->returnUserInfo(
			$userInfo['uid'],
			$userInfo['name'],
			$userInfo['gender'] == 0 ? self::MAN : ($userInfo['gender'] == 1 ? self::WOMAN : self::SECRET),
			$userInfo['logo120']
		);

	}

}