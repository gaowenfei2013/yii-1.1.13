<?php

class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $autologin;

	private $_identity;

	public function rules()
	{
		return array(
			array('username', 'required', 'message' => '请输入用户名/邮箱/手机号！'),
			array('password', 'required', 'message' => '请输入密码！'),
			array('password', 'authenticate'),
			array('autologin', 'boolean'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'username' => '用户名',
			'password' => '密码',
			'autologin'=>'两周内自动登录',
		);
	}

	public function authenticate($attribute, $params)
	{
		$this->_identity = new UserIdentity($this->username, $this->password);
		if( ! $this->_identity->authenticate())
		{
			$this->addError('password', '用户名或密码错误！');
		}
	}

	/**
	 * 登录
	 * @param  boolean $noNeedPassword 是否不需要密码
	 * @return boolean 登录成功返回TRUE，失败返回FALSE
	 */
	public function login($noNeedPassword = FALSE)
	{

		// 注销已登录账号
		if ( ! Yii::app()->user->getIsGuest())
		{
			Yii::app()->user->logout();
		}

		if ($this->_identity === null)
		{
			$this->_identity = new UserIdentity($this->username, $this->password, $noNeedPassword);
			$this->_identity->authenticate();
		}

		if ($this->_identity->errorCode === UserIdentity::ERROR_NONE)
		{
			$duration = $this->autologin ? 3600*24*14 : 0;
			return Yii::app()->user->login($this->_identity, $duration);
		}
		else
		{
			return FALSE;
		}
	}
}
