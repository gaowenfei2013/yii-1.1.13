<?php

class PassController extends Controller
{

	public function actions()
	{
		return array(
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
		);
	}

	/**
	 * login
	 */
	public function actionLogin()
	{
		$model = new LoginForm;

		// ajax validate
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
				$this->onLoginSuccess = array($this, 'logLogin');

				if ($this->hasEventHandler('onLoginSuccess'))
				{
					$this->onLoginSuccess(new CEvent($this));
				}

				$this->loginBack();
			}
		}

		$data = array(
			'model' => $model,
		);

		$this->render('login', $data);
	}

	/**
	 * register
	 */
	public function actionRegister($by = 'username')
	{

		if ( ! in_array($by, array('username', 'email', 'phone')))
		{
			$by = 'username';
		}

		$registerFormName = ucfirst($by).'RegisterForm';

		$model = new $registerFormName;

		// ajax validate
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST[$registerFormName]))
		{
			$model->attributes = $_POST[$registerFormName];

			$directLogin = $by != 'email' ? TRUE : FALSE;

			if ($model->validate() && ($user = $model->register($directLogin)))
			{
				if ($this->hasEventHandler('onLoginSuccess'))
				{
					$this->onLoginSuccess(new CEvent($this));
				}

				$route = array(
					'success',
					'id' => $user->id,
				);

				$this->redirect($route);
			}
		}

		$data = array(
			'model' => $model,
			'by' => $by,
		);

		$this->render('register', $data);
	}

	/**
	 * logout
	 */
	public function actionLogout()
	{
		if ( ! Yii::app()->user->getIsGuest())
		{
			Yii::app()->user->logout();
		}

		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * register success
	 */
	public function actionSuccess($id)
	{
		if ( ! is_int($id) || ! ($user = User::model()->findByPk($id)))
		{
			throw new CHttpException(404);
		}

		$data = array(
			'user' => $user,
		);

		$this->render('success', $data);
	}

	/**
	 * oauth
	 * @param  string $provider 提供者，第三方类型，如qq、weibo
	 */
	public function actionOauth($provider)
	{
		Yii::import('ext.oauth2.OAuth2');

		$config = require Yii::getPathOfAlias('application.config') . '/oauth2.php';

		if ( ! isset($config[$provider]))
		{
			throw new CHttpException('invalid request.');
		}

		try
		{
			$oauth2 = OAuth2::create($provider, $config[$provider]);
		}
		catch (OAuth2_Exception $e)
		{
			throw new CHttpException($e->getMessage());
		}

		if( ! isset($_GET['code']))
		{
			$oauth2->redirect();
		}

		if ( ! $oauth2->validateState(Yii::app()->request->getQuery('state')))
		{
			throw new CHttpException('invalid request.');
		}

		try
		{
			$token = $oauth2->getToken($_GET['code']);

			if ( ! $token)
			{
				throw new CHttpException('500', $provider . ' - get token error.');
			}

			$userInfo = $oauth2->getUserInfo($token);

			if ( ! $userInfo)
			{
				throw new CHttpException('500', $provider . ' - get user info error.');
			}
		}
		catch (OAuth2_Exception $e)
		{
			throw new CHttpException($e->getMessage());
		}

		echo '<pre>';
		print_r($userInfo);
		exit();

		// 已经绑定，直接登录 start
		$userOauth = userOauth::model()->findByAttributes(array('provider' => $provider, 'uid' => $userInfo['uid']));

		if ($userOauth && $userOauth->user_id)
		{

			$user = User::model()->findByPk($userOauth['user_id']);

			if ( ! $user)
			{
				throw new CException('ID为 ' . $userOauth['user_id'] . ' 的用户找不到');
			}

			$loginForm           = new LoginForm;
			$loginForm->username = $user['username'];
			$loginForm->login(TRUE); // 不需要密码直接登录

			$this->redirect(Yii::app()->user->getReturnUrl());
		}
		// 已经绑定，直接登录 end

		$username = $provider . '#' . md5($userInfo['id']);
		$user     = User::model()->find('username = :username', array('username' => $username));

		// 如果这个第三方id第一次登录
		// 为其建立一个账号
		if ( ! $user)
		{
			$registerForm           = new RegisterForm;
			$registerForm->username = $username;
			$registerForm->password = substr(md5(rand() . rand() . rand()), 0, 16);

			$user = $registerForm->register(FALSE);

			if ($user)
			{
				// 更新资料表
				$userProfile            = UserProfile::model()->findByPk($user['id']);
				$userProfile->nickname = $userInfo['nickname'];
				$userProfile->gender    = $userInfo['gender'];
				$userProfile->avatar    = $userInfo['avatar'];
				if ( ! $userProfile->save())
				{
					throw new CException('注册失败！');
				}
			}
			else
			{
				throw new CException('注册失败！');
			}
		}

		if ( ! $userConnect)
		{
			$userConnect = new UserConnect;
		}

		$userConnect->user_id       = $user->id;
		$userConnect->platform_name = $provider;
		$userConnect->platform_id   = $userInfo['id'];
		$userConnect->access_token  = $accessToken['access_token'];
		$userConnect->expires_in    = $accessToken['expires_in'];

		if (isset($accessToken['refresh_token']))
		{
			$userConnect->refresh_token = $accessToken['refresh_token'];
		}

		$userConnect->save();

		Yii::app()->session->add('oauth', array(
			'platform_name' => $provider,
			'platform_id'   => $userInfo['id'],
			'access_token'  => $accessToken['access_token'],
			'expires_in'    => $accessToken['expires_in'],
			'refresh_token' => isset($accessToken['refresh_token']) ? $accessToken['refresh_token'] : '',
		));

		$this->redirect(array('bind'));

	}

	/**
	 * 绑定账号
	 */
	public function actionBind()
	{
		$data['status'] = isset($_GET['status']) ? $_GET['status'] : ''; // 状态 1开始绑定，2绑定成功，3绑定失败
		$this->render('bind', $data);
	}

	/**
	 * onLoginSuccess事件
	 * @param CEvent 事件
	 */
	public function onLoginSuccess($event)
	{
		$this->raiseEvent('onLoginSuccess', $event);
	}

	/**
	 * 记录登录
	 */
	public function logLogin()
	{
		// 登录后记录登录信息
		$userLoginRecord             = new UserLoginRecord;
		$userLoginRecord->user_id    = Yii::app()->user->id;
		$userLoginRecord->time       = time();
		$userLoginRecord->ip         = Yii::app()->request->getUserHostAddress();
		$userLoginRecord->user_agent = Yii::app()->request->getUserAgent();
		$userLoginRecord->status     = 1;
		$userLoginRecord->save();
	}

	/**
	 * 绑定账号事件句柄
	 */
	public function bindUser()
	{
		if ( ! Yii::app()->user->getIsGuest() && ($oauth = Yii::app()->session->get('oauth')))
		{
			Yii::app()->session->remove('oauth');

			$userConnect = UserConnect::model()->findByAttributes(array('platform_name' => $oauth['platform_name'], 'platform_id' => $oauth['platform_id']));
			$userConnect->user_id = Yii::app()->user->id;

			$result = $userConnect->save();

			$this->redirect($this->createUrl('pass/bind', array('status' => ($result ? 2 : 3))));
		}
	}

	protected function loginBack()
	{
		$back = Yii::app()->request->getQuery('back');
		
		if ($back)
		{
			$_back = urldecode($back);
			$__back = urlencode($_back);
			if ($back === $__back)
			{
				$back = $_back;
			}
		}
		else
		{
			$back = Yii::app()->user->getReturnUrl();
		}

		$this->redirect($back);
	}

}