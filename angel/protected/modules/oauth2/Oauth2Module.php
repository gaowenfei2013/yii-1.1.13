<?php

class Oauth2Module extends CWebModule
{

	public $sessionID = 'session';

	public $providerName = 'provider';

	public $providers = array();

	public $userTable = '{{user}}';

	public $oauth2Table = '{{oauth2}}';

	public $userId = 'id';
	
	public $userUsername = 'username';

	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'oauth2.models.*',
			'oauth2.components.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}

	public static function t($message, $params=array())
	{
		return Yii::t('Oauth2Module.oauth2', $message, $params);
	}

	public static function g($name)
	{
		return Yii::app()->controller->module->$name;
	}

}
