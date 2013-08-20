<?php

class OAuth2_163 extends OAuth2_Provider
{

	public $authorize_url = 'https://api.t.163.com/oauth2/authorize';

	public $token_url = 'https://api.t.163.com/oauth2/access_token';

	public $method = self::POST;

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
		);

		$userInfo = $this->sendRequest('https://api.t.163.com/users/show.json', $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['id'],
				'screen_name' => $userInfo['name'],
				'gender' => $userInfo['gender'] == 1 ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == 2 ? OAuth2_UserInfo::WOMEN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['profile_image_url'],
				'email' => $userInfo['email'],
				'location' => str_replace(',', ' ', $userInfo['location']),
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}