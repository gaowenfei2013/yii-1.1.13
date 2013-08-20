<?php

class UsernameRegisterForm extends RegisterForm
{
	public function mergeRules()
	{
		return array(
			array('username', 'required'),
			array('username', 'UsernameValidator'),
			array('username', 'unique', 'className' => 'User'),
		);
	}

	protected function generateUserModel(&$user)
	{
		$user->username = $this->username;
		$user->password = $this->password;
		$user->register_by = 'username';
	}

}
