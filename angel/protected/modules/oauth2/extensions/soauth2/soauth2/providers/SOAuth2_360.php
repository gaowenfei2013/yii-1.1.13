<?php

class SOAuth2_360 extends SOAuth2
{

	public $authorizeUrl = 'https://openapi.360.cn/oauth2/authorize';

	public $accessTokenUrl = 'https://openapi.360.cn/oauth2/access_token';

	public $userInfoUrl = 'https://openapi.360.cn/user/me.json';

	public $requestType = self::GET;

	public $oauthVersion;

	public $display;

	public $relogin;

	protected function authorizeParams()
	{
		$method = __FUNCTION__;
		$params = parent::$method();
		
		if (isset($this->oauthVersion))
		{
			$params['oauth_version'] = $this->oauthVersion;
		}

		if (isset($this->display))
		{
			$params['display'] = $this->display;
		}

		if (isset($this->relogin))
		{
			$params['relogin'] = $this->relogin;
		}

		return $params;
	}

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
			'fields' => 'id,name,avatar,sex',
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			$this->setError($userInfo);
			return FALSE;
		}

		return $this->returnUserInfo(
			$userInfo['id'],
			$userInfo['name'],
			$userInfo['sex'] == '男' ? self::MAN : ($userInfo['sex'] == '女' ? self::WOMEN : self::SECRET),
			$userInfo['avatar']
		);

	}

}