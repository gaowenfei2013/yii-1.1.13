<?php

class OAuth2_Renren extends OAuth2_Provider
{

	public $authorize_url = 'https://graph.renren.com/oauth/authorize';

	public $token_url = 'https://graph.renren.com/oauth/token';

	public $method = self::GET;

	public function getUserInfo(OAuth2_Token $token)
	{
		if (isset($token->original['user']))
		{
			$userInfo = $token->original['user'];
		}
		else
		{
			$params = array(
				'access_token' => $token->access_token,
			);

			$userInfo = $this->sendRequest('https://api.renren.com/v2/user/login/get', $params);

			if ( ! $userInfo || ! isset($userInfo['response']))
			{
				throw new OAuth2_Exception($userInfo['error']);
			}

			$userInfo = $userInfo['response'];
		}

		$gender = OAuth2_UserInfo::SECRET;

		if (isset($userInfo['basicInformation']['sex']))
		{
			$gender = $userInfo['basicInformation']['sex'] == 'MALE' ? OAuth2_UserInfo::MAN : OAuth2_UserInfo::WOMAN;
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['id'],
				'screen_name' => $userInfo['name'],
				'gender' => $gender,
				'avatar' => end($userInfo['avatar'])['url'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}