<?php

Yii::import('application.extensions.phpmailer.JPhpMailer');

class Email
{

	private static $mail;

	private static $config;

	public static function send($address, $subject, $message)
	{
		if ( ! isset(self::$mail))
		{
			self::$mail = new JPhpMailer;
			self::$config = require Yii::getPathOfAlias('application.config') . '/smtp.php';
		}

		self::$mail->SetLanguage(self::$config['language'], Yii::getPathOfAlias('application.extensions.phpmailer.language') . '/');
		self::$mail->IsSMTP();
		self::$mail->IsHTML(true);
		self::$mail->AddAddress($address);
		self::$mail->Host       = self::$config['host'];
		self::$mail->SMTPAuth   = self::$config['auth'];
		self::$mail->Username   = self::$config['username'];
		self::$mail->Password   = self::$config['password'];
		self::$mail->SMTPSecure = self::$config['secure'];
		self::$mail->CharSet    = self::$config['charset'];
		self::$mail->From       = self::$config['username'];
		self::$mail->FromName   = self::$config['fromname'];
		self::$mail->WordWrap   = self::$config['wordwrap'];
		self::$mail->Subject    = $subject;
		self::$mail->Body       = $message;

		if( ! self::$mail->Send())
		{
			Yii::log(self::$mail->ErrorInfo, CLogger::LEVEL_ERROR);
			return false;
		}

		return true;
	}

}