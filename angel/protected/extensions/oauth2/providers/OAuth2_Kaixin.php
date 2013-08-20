<?php

class OAuth2_Kaixin extends OAuth2_Provider
{

	public $authorize_url = 'http://api.kaixin001.com/oauth2/authorize';

	public $token_url = 'https://api.kaixin001.com/oauth2/access_token';

	public $method = self::POST;

	public $scope = 'basic user_birthday';

	public function getUserInfo(OAuth2_Token $token)
	{
		$params = array(
			'access_token' => $token->access_token,
			'fields' => 'uid,name,gender,logo120,city,birthday',
		);

		$userInfo = $this->sendRequest('https://api.kaixin001.com/users/me.json', $params);

		if ( ! $userInfo || isset($userInfo['error']))
		{
			throw new OAuth2_Exception($userInfo);
		}

		preg_match('/(?P<year>\d+).+(?P<month>\d+).+(?P<day>\d+)/', $userInfo['birthday'], $match);

		$birthday = str_pad($match['year'], 4, '19', STR_PAD_LEFT) . '-' . sprintf('%02d-%02d', $match['month'], $match['day']);

		return new OAuth2_UserInfo(
			array(
				'via' => $this->providerName(),
				'uid' => $userInfo['uid'],
				'screen_name' => $userInfo['name'],
				'gender' => $userInfo['gender'] == 0 ? OAuth2_UserInfo::MAN : ($userInfo['gender'] == 1 ? OAuth2_UserInfo::WOMAN : OAuth2_UserInfo::SECRET),
				'avatar' => $userInfo['logo120'],
				'location' => $userInfo['city'],
				'birthday' => $birthday,
				'access_token' => $token->access_token,
				'expires_in' => $token->expires_in,
				'refresh_token' => $token->refresh_token
			)
		);

	}

}