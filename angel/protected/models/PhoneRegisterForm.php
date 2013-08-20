<?php

class PhoneRegisterForm extends RegisterForm
{

	CONST SMSCODE_KEY_PREFIX = 'SMSCODE_KEY_PREFIX';

	public $smsCode;

	public function mergeRules()
	{
		return array(
			array('phone', 'required'),
			array('phone', 'unique', 'className' => 'User', 'username'),
			array('smsCode', 'required'),
			array('smsCode', 'validatrSmsCode'),
		);
	}

	protected function generateUserModel(&$user)
	{
		$user->username = $this->phone;
		$user->password = $this->password;
		$user->phone = $this->phone;
		$user->is_phone_validated = 1;
		$user->register_by = 'phone';
	}

	public function validatrSmsCode()
	{

	}

	public function sendSmsCode()
	{
		$smsCode = $this->generateSmsCode();
		if ( ! SmsHelper::send($smsCode))
		{
			return FALSE;
		}
		Yii::app()->session->add(SMSCODEKEY, $smsCode);
	}

	protected function generateSmsCode()
	{
		return rand(100000, 999999);
	}

	protected function getSmsCodeKey()
	{
		
	}

}
