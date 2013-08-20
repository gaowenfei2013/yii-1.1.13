<?php

class SOAuth2_163 extends SOAuth2
{

	public $authorizeUrl = 'https://api.t.163.com/oauth2/authorize';

	public $accessTokenUrl = 'https://api.t.163.com/oauth2/access_token';

	public $userInfoUrl = 'https://api.t.163.com/users/show.json';

	public $requestType = self::POST;

	public $display;

	public function userInfo($accessToken)
	{
		$params = array(
			'access_token' => $accessToken,
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
			$userInfo['gender'] == 1 ? self::MAN : ($userInfo['gender'] == 2 ? self::WOMEN : self::SECRET),
			$userInfo['profile_image_url']
		);

	}

}