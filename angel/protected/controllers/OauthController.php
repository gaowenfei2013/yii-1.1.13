<?php

class OauthController extends CController
{
	public function actionIndex($provider)
	{
		Yii::import('ext.soauth2.SOAuth2');

		$config = require Yii::getPathOfAlias('application.config') . '/oauth2.php';

		if ( ! isset($config[$provider]))
		{
			throw new CHttpException('404');
		}

		if (isset($config[$provider]['enabled']) && ! $config[$provider]['enabled'])
		{
			throw new CHttpException('400', 'Authorization has been banned.');
		}

		$soauth2 = SOAuth2::create($provider, $config[$provider]);

		if( ! isset($_GET['code']) || ! is_string($_GET['code']) || ! isset($_GET['state']) || ! is_string($_GET['state']))
		{
			$soauth2->redirect();
		}

		$accessToken = $soauth2->accessToken();

		if ( ! $accessToken)
		{
			throw new CHttpException('500', $provider . ' - 认证失败！' . '<br />' . CJSON::encode($soauth2->getError()));
		}

		$userInfo = $soauth2->userInfo($accessToken['access_token']);

		if ( ! $userInfo)
		{
			throw new CHttpException('500', $provider . ' - 认证失败！' . '<br />' . CJSON::encode($soauth2->getError()));
		}

		$userInfo['provider'] = $provider;

		$userOauth = userOauth::model()->findByAttributes(array('provider' => $provider, 'uid' => $userInfo['id']));

		if ($userOauth && $userOauth->user_id)
		{

			$user = User::model()->findByPk($userOauth['user_id']);

			if ( ! $user)
			{
				throw new CException(Yii::t('xiaosu', 'User id {id} not found.', array('{id}' => $userOauth['user_id'])));
			}

			$loginForm           = new LoginForm;
			$loginForm->username = $user['username'];
			$loginForm->login(TRUE);

			$this->redirect(Yii::app()->user->returnUrl);
		}

		$code = md5(rand() . rand() . rand() .uniqid());

		Yii::app()->session->add($this->getUserInfoKey($code), $userInfo);

		$this->redirect( array('bind', 'code' => $code));

	}

	public function actionBind()
	{
		$code = Yii::app()->request->getQuery('code');

		if ( ! $this->checkCode($code))
		{
			throw new CHttpException('400');
		}

		$data = array(
			'back' => urlencode($this->createAbsoluteUrl('success', array('code' => $code))),
		);

		$this->render('bind', $data);
	}

	public function actionSuccess()
	{
		$code = Yii::app()->request->getQuery('code');

		if ( ! $this->checkCode($code))
		{
			throw new CHttpException('400');
		}

		$userInfo = Yii::app()->session->get($this->getUserInfoKey($code));

		if ( ! $userInfo)
		{
			throw new CHttpException('bind error.');
		}

	}

	protected function getUserInfoKey($code)
	{
		return 'OAUTH_'.$code;
	}

	protected function checkCode($code)
	{
		if ( ! $code || ! is_string($code) || ! preg_match('/[a-z0-9]{32}/', $code))
		{
			return FALSE;
		}
		return TRUE;
	}
}