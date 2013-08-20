<?php

class SOAuth2_Weibo extends SOAuth2
{

	public $authorizeUrl = 'https://api.weibo.com/oauth2/authorize';

	public $accessTokenUrl = 'https://api.weibo.com/oauth2/access_token';

	public $userInfoUrl = 'https://api.weibo.com/2/users/show.json';

	public $uidUrl = 'https://api.weibo.com/2/account/get_uid.json';

	public $requestType = self::POST;

	public $display;

	public $forcelogin;

	public $language;

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

		if (isset($this->language))
		{
			$params['language'] = $this->language;
		}

		return $params;
	}

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
		);

		$uid = $this->sendRequest($this->uidUrl, $params);

		if ( ! $uid || ! isset($uid['uid']))
		{
			$this->setError($uid);
			return FALSE;
		}

		$params = array(
			'access_token' => $accessToken,
			'uid' => $uid['uid'],
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			$this->setError($uid);
			return FALSE;
		}

		return $this->returnUserInfo(
			$uid['uid'],
			$userInfo['screen_name'],
			$userInfo['gender'] == 'm' ? self::MAN : ($userInfo['gender'] == 'f' ? self::WOMAN : self::SECRET),
			$userInfo['avatar_large']
		);

	}

}