<?php

class OAuth2_Baidu extends OAuth2_Provider
{

	public $authorize_url = 'https://openapi.baidu.com/oauth/2.0/authorize';

	public $token_url = 'https://openapi.baidu.com/oauth/2.0/token';

	public $method = self::GET;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token
		);

		$userInfo = $this->sendRequest('https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser', $params);

		if ( ! $userInfo || isset($userInfo['error_code']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['uid'],
				'screen_name' => $userInfo['uname'],
				'gender' => OAuth2_UserInfo::SECRET,
				'avatar' => 'http://tb.himg.baidu.com/sys/portrait/item/' . $userInfo['portrait'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}