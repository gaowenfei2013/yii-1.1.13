<?php

class OAuth2_Weibo extends OAuth2_Provider
{

	public $authorize_url = 'https://api.weibo.com/oauth2/authorize';

	public $token_url = 'https://api.weibo.com/oauth2/access_token';

	public $method = self::POST;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
		);

		$uid = $this->sendRequest('https://api.weibo.com/2/account/get_uid.json', $params);

		if ( ! $uid || ! isset($uid['uid']))
		{
			throw new OAuth2_Exception($uid);
		}

		$params = array(
			'access_token' => $token->access_token,
			'uid' => $uid['uid'],
		);

		$userInfo = $this->sendRequest('https://api.weibo.com/2/users/show.json', $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $uid['uid'],
				'screen_name' => $userInfo['screen_name'],
				'location' => $userInfo['location'],
				'gender' => $userInfo['gender'] == 'm' ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == 'f' ? OAuth2_UserInfo::WOMAN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['avatar_large'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}