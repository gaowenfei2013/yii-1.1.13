<?php

class EmailRegisterForm extends RegisterForm
{
	public function mergeRules()
	{
		return array(
			array('email', 'required'),
			array('email', 'unique', 'className' => 'User', 'username'),
		);
	}

	protected function generateUserModel(&$user)
	{
		$user->username = $this->email;
		$user->password = $this->password;
		$user->email = $this->email;
		$user->register_by = 'email';
	}

}
