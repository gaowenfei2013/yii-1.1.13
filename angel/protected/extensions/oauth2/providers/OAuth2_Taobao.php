<?php

class OAuth2_Taobao extends OAuth2_Provider
{

	public $authorize_url = 'https://oauth.taobao.com/authorize';

	public $token_url = 'https://oauth.taobao.com/token';

	public $method = self::POST;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'method' => 'taobao.user.get',
			'access_token' => $token->access_token,
			'format' => 'json',
			'v' => '2.0',
			'fields' => 'uid,nick,sex,avatar',
		);

		$userInfo = $this->sendRequest('https://eco.taobao.com/router/rest', $params);

		if ( ! $userInfo || isset($userInfo['error_response']))
		{
			throw new OAuth2_Exception($userInfo['error_response']);
		}

		$userInfo = $userInfo['user_get_response']['user'];

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['uid'],
				'screen_name' => $userInfo['nick'],
				'gender' => $userInfo['gender'] == 'm' ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == 'f' ? OAuth2_UserInfo::WOMAN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['avatar'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}