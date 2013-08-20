<?php

class SOAuth2_Douban extends SOAuth2
{

	public $authorizeUrl = 'https://www.douban.com/service/auth2/auth';

	public $accessTokenUrl = 'https://www.douban.com/service/auth2/token';

	public $userInfoUrl = 'https://api.douban.com/v2/user/~me';

	public $requestType = self::POST;

	public function userInfo($accessToken)
	{
		$headers = array(
			'access_token' => $accessToken,
		);

		$userInfo = $this->sendRequest($this->userInfoUrl, NULL, self::POST, $headers);

		if ( ! $userInfo || isset($userInfo['code']))
		{
			$this->setError($userInfo);
			return FALSE;
		}

		return $this->returnUserInfo(
			$userInfo['id'],
			$userInfo['name'],
			self::SECRET,
			$userInfo['large_avatar']
		);

	}

}