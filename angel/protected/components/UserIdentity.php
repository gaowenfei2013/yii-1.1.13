<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{

	/**
	 * user id
	 * @var integer
	 */
	private $_id;

	public function __construct($username, $password = NULL)
	{
		$this->username        = $username;
		$this->password        = $password;
	}

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		if (IsHelper::email($this->username))
		{
			$user = User::model()->find('email = :email', array(':email'=>$this->username));
		}
		elseif (IsHelper::phone($this->username))
		{
			$user = User::model()->find('phone = :phone', array(':phone'=>$this->username));
		}
		else
		{
			$user = User::model()->find('username = :username', array(':username'=>$this->username));
		}

		// user not found
		if ( ! $user)
		{
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		}
		// password incorrect
		else if ($this->password !== NULL && ! $user->validatePassword($this->password))
		{
			$this->errorCode = self::ERROR_PASSWORD_INVALID;

			$this->_id = $user['id'];

			$this->onPasswordError = array($this, 'recordPasswordError');

			if ($this->hasEventHandler('onPasswordError'))
			{
				$this->onPasswordError();
			}
		}
		else
		{
			$this->errorCode = self::ERROR_NONE;
			$this->_id        = $user->id;
			$this->setPersistentStates($user->attributes);
		}

		return !$this->errorCode;
	}

	/**
	 * return user id
	 * @return ingeter user id
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * record login error info
	 */
	public function recordPasswordError($event)
	{
		$userLoginRecord             = new UserLoginRecord;
		$userLoginRecord->user_id    = $event->sender->getId();
		$userLoginRecord->time       = time();
		$userLoginRecord->ip         = Yii::app()->request->getUserHostAddress();
		$userLoginRecord->user_agent = Yii::app()->request->getUserAgent();
		$userLoginRecord->status     = 0;
		$userLoginRecord->save();
	}

	/**
	 * raise when password error
	 */
	public function onPasswordError()
	{
		$this->raiseEvent('onPasswordError', new CEvent($this));
	}

}