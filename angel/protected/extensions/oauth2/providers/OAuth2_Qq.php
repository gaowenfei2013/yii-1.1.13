<?php

class OAuth2_Qq extends OAuth2_Provider
{

	public $authorize_url = 'https://graph.qq.com/oauth2.0/authorize';

	public $token_url = 'https://graph.qq.com/oauth2.0/token';

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
		);

		$openId = $this->sendRequest('https://graph.qq.com/oauth2.0/me', $params);
		if ( ! $openId || ! isset($openId['openid']))
		{
			throw new OAuth2_Exception($openId);
		}

		$params = array(
			'access_token' => $token->access_token,
			'openid' => $openId['openid'],
			'oauth_consumer_key' => $this->client_id,
			'format' => 'json',
		);

		$userInfo = $this->sendRequest('https://graph.qq.com/user/get_user_info', $params);

		if ( ! $userInfo || $userInfo['ret'] != 0)
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $openId['openid'],
				'screen_name' => $userInfo['nickname'],
				'gender' => $userInfo['gender'] == '男' ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == '女' ? OAuth2_UserInfo::WOMAN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['figureurl_qq_2'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}