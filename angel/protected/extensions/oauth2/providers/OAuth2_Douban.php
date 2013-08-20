<?php

class OAuth2_Douban extends OAuth2_Provider
{

	public $authorize_url = 'https://www.douban.com/service/auth2/auth';

	public $token_url = 'https://www.douban.com/service/auth2/token';

	public $method = self::POST;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
		);

		$userInfo = $this->sendRequest('https://api.douban.com/v2/user/' . $token->original['douban_user_id'], NULL, self::POST, $params);

		if ( ! $userInfo || isset($userInfo['code']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['id'],
				'screen_name' => $userInfo['name'],
				'gender' => OAuth2_UserInfo::SECRET,
				'avatar' => $userInfo['large_avatar'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}