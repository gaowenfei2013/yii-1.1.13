<?php
/**
 * CEmailValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CEmailValidator validates that the attribute value is a valid email address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
// 
class CEmailValidator extends CValidator
{
	/**
	 * @var string the regular expression used to validate the attribute value.
	 * @see http://www.regular-expressions.info/email.html
	 */
	// 验证整个邮箱的正则
	public $pattern='/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
	/**
	 * @var string the regular expression used to validate email addresses with the name part.
	 * This property is used only when {@link allowName} is true.
	 * @see allowName
	 */
	// 完整的验证邮箱的正则表达式，包含名称，例如 Qiang Xue <qiang.xue@gmail.com> 这种形式的
	public $fullPattern='/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
	/**
	 * @var boolean whether to allow name in the email address (e.g. "Qiang Xue <qiang.xue@gmail.com>"). Defaults to false.
	 * @see fullPattern
	 */
	public $allowName=false;
	/**
	 * @var boolean whether to check the MX record for the email address.
	 * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
	 * exists in your PHP installation.
	 */
	// 是否需要检查邮箱的mx（邮件交互记录）记录
	// 默认为fasle，如果开启，请确保函数checkdnsrr可用
	public $checkMX=false;
	/**
	 * @var boolean whether to check port 25 for the email address.
	 * Defaults to false. To enable it, ensure that the PHP functions 'dns_get_record' and
	 * 'fsockopen' are available in your PHP installation.
	 */
	// 是否需要检查端口是否为25
	// 默认false，如果需要开启，请确保dns_get_record和fsockopen函数可用
	public $checkPort=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	// 是否允许空
	public $allowEmpty=true;
	/**
	 * @var boolean whether validation process should care about IDN (internationalized domain names). Default
	 * value is false which means that validation of emails containing IDN will always fail.
	 * @since 1.1.13
	 */
	// 是否需要关心国际化域名
	// 默认为false，也就是如果是国际化域名（多语种域名），那肯定验证不通过
	public $validateIDN=false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	// 验证模型的属性
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value)) // 为空检查
			return;
		if(!$this->validateValue($value)) // 验证不通过
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid email address.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Validates a static value to see if it is a valid email.
	 * Note that this method does not respect {@link allowEmpty} property.
	 * This method is provided so that you can call it directly without going through the model validation rule mechanism.
	 * @param mixed $value the value to be validated
	 * @return boolean whether the value is a valid email
	 * @since 1.1.1
	 */
	// 验证给定的值是否是正确的邮箱
	public function validateValue($value)
	{
		if($this->validateIDN)
			$value=$this->encodeIDN($value);
		// make sure string length is limited to avoid DOS attacks
		$valid=is_string($value) && strlen($value)<=254 && (preg_match($this->pattern,$value) || $this->allowName && preg_match($this->fullPattern,$value));
		if($valid)
			$domain=rtrim(substr($value,strpos($value,'@')+1),'>');
		if($valid && $this->checkMX && function_exists('checkdnsrr'))
			$valid=checkdnsrr($domain,'MX');
		if($valid && $this->checkPort && function_exists('fsockopen') && function_exists('dns_get_record'))
			$valid=$this->checkMxPorts($domain);
		return $valid;
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	// 返回前端验证邮箱的js代码
	public function clientValidateAttribute($object,$attribute)
	{
		if($this->validateIDN)
		{
			Yii::app()->getClientScript()->registerCoreScript('punycode');
			// punycode.js works only with the domains - so we have to extract it before punycoding
			$validateIDN='
var info = value.match(/^(.[^@]+)@(.+)$/);
if (info)
	value = info[1] + "@" + punycode.toASCII(info[2]);
';
		}
		else
			$validateIDN='';

		$message=$this->message!==null ? $this->message : Yii::t('yii','{attribute} is not a valid email address.');
		$message=strtr($message, array(
			'{attribute}'=>$object->getAttributeLabel($attribute),
		));

		$condition="!value.match({$this->pattern})";
		if($this->allowName)
			$condition.=" && !value.match({$this->fullPattern})";

		return "
$validateIDN
if(".($this->allowEmpty ? "jQuery.trim(value)!='' && " : '').$condition.") {
	messages.push(".CJSON::encode($message).");
}
";
	}

	/**
	 * Retrieves the list of MX records for $domain and checks if port 25
	 * is opened on any of these.
	 * @since 1.1.11
	 * @param string $domain domain to be checked
	 * @return boolean true if a reachable MX server has been found
	 */
	// 检查域和端口
	protected function checkMxPorts($domain)
	{
		$records=dns_get_record($domain, DNS_MX);
		if($records===false || empty($records))
			return false;
		usort($records,array($this,'mxSort'));
		foreach($records as $record)
		{
			$handle=fsockopen($record['target'],25);
			if($handle!==false)
			{
				fclose($handle);
				return true;
			}
		}
		return false;
	}

	/**
	 * Determines if one MX record has higher priority as another
	 * (i.e. 'pri' is lower). Used by {@link checkMxPorts}.
	 * @since 1.1.11
	 * @param mixed $a first item for comparison
	 * @param mixed $b second item for comparison
	 * @return boolean
	 */
	// 确定某个mx记录具有更高的优先级
	protected function mxSort($a, $b)
	{
		if($a['pri']==$b['pri'])
			return 0;
		return ($a['pri']<$b['pri'])?-1:1;
	}

	/**
	 * Converts given IDN to the punycode.
	 * @param $value IDN to be converted.
	 * @return string resulting punycode.
	 * @since 1.1.13
	 */
	// 将非英文的地址转为英文的地址
	private function encodeIDN($value)
	{
		require_once(Yii::getPathOfAlias('system.vendors.idna_convert').DIRECTORY_SEPARATOR.'idna_convert.class.php');
		$idnaConvert=new idna_convert();
		return $idnaConvert->encode($value);
	}
}
