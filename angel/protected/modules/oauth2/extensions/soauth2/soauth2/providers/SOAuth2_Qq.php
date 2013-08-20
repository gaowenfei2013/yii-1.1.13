<?php

class SOAuth2_Qq extends SOAuth2
{

	public $authorizeUrl = 'https://graph.qq.com/oauth2.0/authorize';

	public $accessTokenUrl = 'https://graph.qq.com/oauth2.0/token';

	public $openIdUrl = 'https://graph.qq.com/oauth2.0/me';

	public $userInfoUrl = 'https://graph.qq.com/user/get_user_info';

	public $requestType = self::GET;

	public $display;

	public $gUt;

	protected function authorizeParams()
	{
		$method = __FUNCTION__;
		$params = parent::$method();
		
		if (isset($this->display))
		{
			$params['display'] = $this->display;
		}

		if (isset($this->gUt))
		{
			$params['g_t'] = $this->gUt;
		}

		return $params;
	}

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
		);

		$openId = $this->sendRequest($this->openIdUrl, $params);
		if ( ! $openId || ! isset($openId['openid']))
		{
			$this->setError($openId);
			return FALSE;
		}

		$params = array(
			'access_token' => $accessToken,
			'openid' => $openId['openid'].'22',
			'oauth_consumer_key' => $this->clientId,
			'format' => 'json',
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || $userInfo['ret'] != 0)
		{
			$this->setError($userInfo);
			return FALSE;
		}

		return $this->returnUserInfo(
			$openId['openid'],
			$userInfo['nickname'],
			$userInfo['gender'] == '男' ? self::MAN : ($userInfo['gender'] == '女' ? self::WOMAN : self::SECRET),
			$userInfo['figureurl_qq_2']
		);

	}

}