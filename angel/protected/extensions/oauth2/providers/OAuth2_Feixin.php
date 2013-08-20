<?php

class OAuth2_Feixin extends OAuth2_Provider
{

	public $authorize_url = 'https://i.feixin.10086.cn/oauth2/authorize';

	public $token_url = 'https://i.feixin.10086.cn/oauth2/access_token';

	public $method = self::GET;

	public $scope = 'basic email';

	public function tokenParams($code)
	{
		$params = parent::tokenParams($code);
		$params['scope'] = $this->scope;
		return $params;
	}

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token
		);

		$userInfo = $this->sendRequest('https://i.feixin.10086.cn/api/user.json', $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['userId'],
				'screen_name' => $userInfo['nickname'],
				'gender' => $userInfo['gender'] == 1 ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == 2 ? OAuth2_UserInfo::WOMAN : OAuth2_UserInfo::SECRET),
				'email' => $userInfo['email'],
				'birthday' => $userInfo['birthday'],
				'avatar' => $userInfo['portraitLarge'],
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}