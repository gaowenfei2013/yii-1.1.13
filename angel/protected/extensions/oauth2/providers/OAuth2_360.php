<?php

class OAuth2_360 extends OAuth2_Provider
{

	public $authorize_url = 'https://openapi.360.cn/oauth2/authorize';

	public $token_url = 'https://openapi.360.cn/oauth2/access_token';

	public $method = self::GET;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
			'fields' => 'id,name,avatar,sex,area',
		);

		$userInfo = $this->sendRequest('https://openapi.360.cn/user/me.json', $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['id'],
				'screen_name' => $userInfo['name'],
				'gender' => $userInfo['sex'] == '男' ? OAuth2_UserInfo::MAN : ($userInfo['sex'] == '女' ? OAuth2_UserInfo::WOMEN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['avatar'],
				'location' => $userInfo['area'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}