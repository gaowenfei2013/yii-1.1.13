<?php

abstract class RegisterForm extends CFormModel
{
	public $username;
	public $email;
	public $phone;
	public $password;
	public $verifyCode;
	public $smsCode;

	public function rules()
	{
		return array_merge(array(
			array('password', 'required'),
			array('password', 'PasswordValidator'),
			array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements()),
		), $this->mergeRules());
	}

	public function attributeLabels()
	{
		return array(
			'username'   => Yii::t('xiaosu', 'Username'),
			'email'   => Yii::t('xiaosu', 'Email'),
			'phone'   => Yii::t('xiaosu', 'Phone'),
			'password'   => Yii::t('xiaosu', 'Password'),
			'verifyCode' => Yii::t('xiaosu', 'VerifyCode'),
			'smsCode' => Yii::t('xiaosu', 'SmsCode'),
		);
	}

	/**
	 * register
	 * @param  boolean $directLogin whether login after register success
	 * @return mixed success will return user object, otherwise return false
	 */
	public function register($directLogin = FALSE)
	{
		$user = new User;

		$this->generateUserModel($user);

		if ($user->save())
		{
			if ($directLogin)
			{
				$this->onAfterRegister = array($this, 'login');
			}

			if ($this->hasEventHandler('onAfterRegister'))
			{
				$this->onAfterRegister(new CEvent($this));
			}

			return $user;
		}
		else
		{
			$this->addErrors($user->getErrors());
			return FALSE;
		}
	}

	abstract protected function generateUserModel(&$user);

	protected function mergeRules()
	{
		return array();
	}

	/**
	 * raise after register success
	 * @param  CEvent $event the event parameter
	 */
	public function onAfterRegister($event)
	{
		$this->raiseEvent('onAfterRegister', $event);
	}

	/**
	 * login
	 */
	public function login()
	{
		$model           = new LoginForm;
		$model->username = $this->username;
		$model->password = $this->password;
		$model->login();
	}

}
