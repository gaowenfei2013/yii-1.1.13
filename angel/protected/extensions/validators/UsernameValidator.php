<?php
/**
 * username validator
 */
class UsernameValidator extends CValidator
{

	public $min = 4;

	public $max = 18;

	protected function _message()
	{
		return $this->message !== NULL ? $this->message : Yii::t('xiaosu', 'The username should be within {min}-{max} characters. Consist of letters, numbers, underscores.', array('{min}' => $this->min, '{max}' => $this->max));
	}

	protected function validateAttribute($object,$attribute)
	{
		$value = $object->$attribute;
		if (is_string($value) && preg_match($this->getPattern(), $value))
		{
			return TRUE;
		}
		else
		{
			$this->addError($object, $attribute, $this->_message());
		}
	}

	public function clientValidateAttribute($object,$attribute)
	{
		return "
if(!value.match(".$this->getPattern().")) {
	messages.push(".CJSON::encode($this->_message()).");
}
";
	}

	protected function getPattern()
	{
		static $pattern;
		if (isset($pattern))
		{
			return $pattern;
		}

		$pattern = strtr('/^[^\s]{_min_,_max_}$/', array('_min_' => $this->min, '_max_' => $this->max));
		return $pattern;
	}
}