<?php

class SOAuth2_Taobao extends SOAuth2
{

	public $authorizeUrl = 'https://oauth.taobao.com/authorize';

	public $accessTokenUrl = 'https://oauth.taobao.com/token';

	public $userInfoUrl = 'https://eco.taobao.com/router/rest';

	public $requestType = self::POST;

	public $view;

	protected function authorizeParams()
	{
		$method = __FUNCTION__;
		$params = parent::$method();

		if (isset($this->view))
		{
			$params['view'] = $this->view;
		}

		return $params;
	}

	protected function accessTokenParams()
	{
		$method = __FUNCTION__;
		$params = parent::$method();

		if (isset($this->view))
		{
			$params['view'] = $this->view;
		}

		return $params;
	}

	public function userInfo($accessToken)
	{
		$params = array(
			'method' => 'taobao.user.get',
			'access_token' => $accessToken,
			'format' => 'json',
			'v' => '2.0',
			'fields' => 'uid,nick,sex,avatar',
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || isset($userInfo['error_response']))
		{
			$this->setError($userInfo['error_response']);
			return FALSE;
		}

		$userInfo = $userInfo['user_get_response']['user'];

		return $this->returnUserInfo(
			$userInfo['uid'],
			$userInfo['nick'],
			$userInfo['gender'] == 'm' ? self::MAN : ($userInfo['gender'] == 'f' ? self::WOMAN : self::SECRET),
			$userInfo['avatar']
		);

	}

}