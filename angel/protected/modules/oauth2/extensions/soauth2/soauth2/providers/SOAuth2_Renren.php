<?php

class SOAuth2_Renren extends SOAuth2
{

	public $authorizeUrl = 'https://graph.renren.com/oauth/authorize';

	public $accessTokenUrl = 'https://graph.renren.com/oauth/token';

	public $userInfoUrl = 'https://api.renren.com/v2/user/login/get';

	public $requestType = self::GET;

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, $params);

		if ( ! $userInfo || ! isset($userInfo['response']))
		{
			$this->setError($userInfo['error']);
			return FALSE;
		}

		$userInfo = $userInfo['response'];

		$gender = self::SECRET;

		if (isset($userInfo['basicInformation']['sex']))
		{
			$gender = $userInfo['basicInformation']['sex'] == 'MALE' ? self::MAN : self::WOMAN;
		}

		return $this->returnUserInfo(
			$userInfo['id'],
			$userInfo['name'],
			$gender,
			end($userInfo['avatar'])['url']
		);

	}

}