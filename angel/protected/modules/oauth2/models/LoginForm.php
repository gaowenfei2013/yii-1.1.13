<?php

class LoginForm extends CFormModel
{
	public $username;
	
	public $password;

	private $_identity;

	public function rules()
	{
		return array(
			array('username', 'required'),
			array('password', 'required'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'username' => Oauth2Module::t('Username'),
			'password' => Oauth2Module::t('Password'),
		);
	}

	public function login()
	{
		if ( ! Yii::app()->user->getIsGuest())
		{
			Yii::app()->user->logout();
		}

		if ($this->_identity === null)
		{
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
		}

		if ($this->_identity->errorCode === UserIdentity::ERROR_NONE)
		{
			$result = Yii::app()->user->login($this->_identity);
		}
		else
		{
			$result =  FALSE;
		}

		if ( ! $result)
		{
			$this->addError('username', Oauth2Module::t('Username or Password error.'));
		}

		return $result;
	}

}
