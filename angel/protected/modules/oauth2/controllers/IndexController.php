<?php

class IndexController extends Controller
{

	const KEYPREFIX = 'OAUTH2_';

	public function actions()
	{
		return array(
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
		);
	}

	public function actionIndex()
	{
		$provider = $this->getQuery(Oauth2Module::g('providerName'));
		$providers = Oauth2Module::g('providers');

		if ( ! $provider || ! $providers || ! isset($providers[$provider]))
		{
			throw new CHttpException('400', 'invalid request.');
		}

		Yii::import('oauth2.extensions.soauth2.SOAuth2');
		
		$soauth2 = SOAuth2::create($provider, $providers[$provider]);

		if( ! $this->getQuery('code') || ! $this->getQuery('state'))
		{
			$soauth2->redirect();
		}

		$accessToken = $soauth2->accessToken();

		if ( ! $accessToken)
		{
			throw new CHttpException('400', Oauth2Module::t('provider {provider} authentication failed.', array('{provider}' => $provider)));
		}

		$userInfo = $soauth2->userInfo($accessToken['access_token']);

		if ( ! $userInfo)
		{
			throw new CHttpException('400', Oauth2Module::t('provider {provider} get user info failed.', array('{provider}' => $provider)));
		}

		$oauth2 = Oauth2::model()->findByAttributes(array('provider' => $provider, 'uid' => $userInfo['id']));

		if ($oauth2 && $oauth2->user_id)
		{
			$username = Yii::app()->db->createCommand()
									->select(Oauth2Module::g('userUsername'))
									->from(Oauth2Module::g('userTable'))
									->where(Oauth2Module::g('userId') . '=:id', array(':id'=>$oauth2->user_id))
									->queryScalar();

			if ($username)
			{
				$model = new LoginForm;
				$model->username = $username;
				if ($model->login())
				{
					$this->redirect(Yii::app()->user->returnUrl);
				}
				else
				{
					throw CHttpException('500', Oauth2Module::t('login field.'));
				}
			}
			else
			{
				throw CHttpException('500', Oauth2Module::t('user {username} not found.', array('{username}' => $username)));
			}
		}

		$data = array_merge($userInfo, $accessToken, array('provider' => $provider));

		$code = $this->getCode();

		Yii::app()->{Oauth2Module::g('sessionID')}->add(self::KEYPREFIX . $code, $data);

		$this->redirect(array('callback', 'code' => $code));

	}

	public function actionCallback()
	{
		$model = new LoginForm;

		if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['LoginForm']))
		{
			$model->attributes = $_POST['LoginForm'];
			
			if ($model->validate() && $model->login())
			{

				$this->onLoginSuccess = array($this, 'bind');

				if ($this->hasEventHandler('onLoginSuccess'))
				{
					$this->onLoginSuccess(new CEvent($this));
				}

				$this->redirect(Yii::app()->user->returnUrl);
			}
		}

		$data = array(
			'model' => $model,
		);

		$this->render('callback', $data);
	}

	protected function getQuery($name)
	{
		return isset($_GET[$name]) && is_string($_GET[$name]) ? $_GET[$name] : NULL;
	}

	protected function getCode()
	{
		return md5(rand() . rand() . rand());
	}

	public function onLoginSuccess($event)
	{
		$this->raiseEvent('onLoginSuccess', $event);
	}

	public function bind()
	{
		$code = $this->getQuery('code');
		if ( ! $code || isset($code[32]) || Yii::app()->user->isGuest)
		{
			return;
		}

		$data = Yii::app()->{Oauth2Module::g('sessionID')}->get(self::KEYPREFIX . $code);
		if ( ! $data)
		{
			return;
		}

		$oauth2 = new Oauth2;

		$oauth2->attributes = array(
			'uid' => $data['id'],
			'provider' => $data['provider'],
			'access_token' => $data['access_token'],
			'expires_in' => $data['expires_in'],
			'refresh_token' => $data['refresh_token'],
			'user_id' => Yii::app()->user->getId(),
		);

		$oauth2->save();

		Yii::app()->{Oauth2Module::g('sessionID')}->remove(self::KEYPREFIX . $code);

	}

}