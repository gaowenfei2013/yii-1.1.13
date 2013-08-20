<?php
/**
 * CValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CValidator is the base class for all validators.
 *
 * Child classes must implement the {@link validateAttribute} method.
 *
 * The following properties are defined in CValidator:
 * <ul>
 * <li>{@link attributes}: array, list of attributes to be validated;</li>
 * <li>{@link message}: string, the customized error message. The message
 *   may contain placeholders that will be replaced with the actual content.
 *   For example, the "{attribute}" placeholder will be replaced with the label
 *   of the problematic attribute. Different validators may define additional
 *   placeholders.</li>
 * <li>{@link on}: string, in which scenario should the validator be in effect.
 *   This is used to match the 'on' parameter supplied when calling {@link CModel::validate}.</li>
 * </ul>
 *
 * When using {@link createValidator} to create a validator, the following aliases
 * are recognized as the corresponding built-in validator classes:
 * <ul>
 * <li>required: {@link CRequiredValidator}</li>
 * <li>filter: {@link CFilterValidator}</li>
 * <li>match: {@link CRegularExpressionValidator}</li>
 * <li>email: {@link CEmailValidator}</li>
 * <li>url: {@link CUrlValidator}</li>
 * <li>unique: {@link CUniqueValidator}</li>
 * <li>compare: {@link CCompareValidator}</li>
 * <li>length: {@link CStringValidator}</li>
 * <li>in: {@link CRangeValidator}</li>
 * <li>numerical: {@link CNumberValidator}</li>
 * <li>captcha: {@link CCaptchaValidator}</li>
 * <li>type: {@link CTypeValidator}</li>
 * <li>file: {@link CFileValidator}</li>
 * <li>default: {@link CDefaultValueValidator}</li>
 * <li>exist: {@link CExistValidator}</li>
 * <li>boolean: {@link CBooleanValidator}</li>
 * <li>date: {@link CDateValidator}</li>
 * <li>safe: {@link CSafeValidator}</li>
 * <li>unsafe: {@link CUnsafeValidator}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */

// 验证器基类
abstract class CValidator extends CComponent
{
	/**
	 * @var array list of built-in validators (name=>class)
	 */
	// 内置的验证器
	public static $builtInValidators=array(
		'required'=>'CRequiredValidator', // 必须验证
		'filter'=>'CFilterValidator', // 过滤
		'match'=>'CRegularExpressionValidator', // 正则
		'email'=>'CEmailValidator', // 邮箱
		'url'=>'CUrlValidator', // url地址
		'unique'=>'CUniqueValidator', // 唯一性
		'compare'=>'CCompareValidator', // 比较
		'length'=>'CStringValidator', // 字符串
		'in'=>'CRangeValidator', // 范围
		'numerical'=>'CNumberValidator', // 数字
		'captcha'=>'CCaptchaValidator', // 验证码
		'type'=>'CTypeValidator', // 类型
		'file'=>'CFileValidator', // 文件
		'default'=>'CDefaultValueValidator', // 默认值
		'exist'=>'CExistValidator', // 存在，严重数据库
		'boolean'=>'CBooleanValidator', // boolean值
		'safe'=>'CSafeValidator', // 安全
		'unsafe'=>'CUnsafeValidator', // 不安全
		'date'=>'CDateValidator', // 日期
	);

	/**
	 * @var array list of attributes to be validated.
	 */
	// 需要验证的属性
	public $attributes;
	/**
	 * @var string the user-defined error message. Different validators may define various
	 * placeholders in the message that are to be replaced with actual values. All validators
	 * recognize "{attribute}" placeholder, which will be replaced with the label of the attribute.
	 */
	// 错误信息
	public $message;
	/**
	 * @var boolean whether this validation rule should be skipped when there is already a validation
	 * error for the current attribute. Defaults to false.
	 * @since 1.1.1
	 */
	// 当前验证的属性如果已经发生错误，是否终止后面的此属性的验证
	public $skipOnError=false;
	/**
	 * @var array list of scenarios that the validator should be applied.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	// 当前情景 scenario
	public $on;
	/**
	 * @var array list of scenarios that the validator should not be applied to.
	 * Each array value refers to a scenario name with the same name as its array key.
	 * @since 1.1.11
	 */
	// 不需要验证的情景，一个数组，如 search 场景不需要验证 array('search')
	// 空的话，所有场景都要验证
	public $except;
	/**
	 * @var boolean whether attributes listed with this validator should be considered safe for massive assignment.
	 * Defaults to true.
	 * @since 1.1.4
	 */
	// 表示此验证器中的属性在批量分配的时候是是否安全的
	// 如 $user->attributes = $_PSOT['user'];
	// 
	public $safe=true;
	/**
	 * @var boolean whether to perform client-side validation. Defaults to true.
	 * Please refer to {@link CActiveForm::enableClientValidation} for more details about client-side validation.
	 * @since 1.1.7
	 */
	// 是否执行客户端验证
	public $enableClientValidation=true;

	/**
	 * Validates a single attribute.
	 * This method should be overridden by child classes.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
	// 验证属性
	// $object CModel ar模型对象
	// $attribute 属性名
	abstract protected function validateAttribute($object,$attribute);


	/**
	 * Creates a validator object.
	 * @param string $name the name or class of the validator
	 * @param CModel $object the data object being validated that may contain the inline validation method
	 * @param mixed $attributes list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the validator properties
	 * @return CValidator the validator
	 */
	// 创建验证器对象
	// $name 验证器的类名
	// $object ar模型类
	// $attributes 这个验证器验证的属性
	// $params 其他参数，例如：场景 "on" => "search"
	public static function createValidator($name,$object,$attributes,$params=array())
	{
		// 属性
		if(is_string($attributes))
			$attributes=preg_split('/[\s,]+/',$attributes,-1,PREG_SPLIT_NO_EMPTY);

		// 适用的情景
		if(isset($params['on']))
		{
			if(is_array($params['on']))
				$on=$params['on'];
			else
				$on=preg_split('/[\s,]+/',$params['on'],-1,PREG_SPLIT_NO_EMPTY);
		}
		else
			$on=array();

		// 这个验证器不适用的场景
		if(isset($params['except']))
		{
			if(is_array($params['except']))
				$except=$params['except'];
			else
				$except=preg_split('/[\s,]+/',$params['except'],-1,PREG_SPLIT_NO_EMPTY);
		}
		else
			$except=array();

		// 模型类自己方法的验证，也即是内嵌验证器
		if(method_exists($object,$name))
		{
			$validator=new CInlineValidator;
			$validator->attributes=$attributes;
			$validator->method=$name;
			if(isset($params['clientValidate']))
			{
				$validator->clientValidate=$params['clientValidate'];
				unset($params['clientValidate']);
			}
			$validator->params=$params;
			if(isset($params['skipOnError']))
				$validator->skipOnError=$params['skipOnError'];
		}
		else
		{
			$params['attributes']=$attributes;
			if(isset(self::$builtInValidators[$name]))
				$className=Yii::import(self::$builtInValidators[$name],true);
			else
				$className=Yii::import($name,true);
			$validator=new $className;
			foreach($params as $name=>$value)
				$validator->$name=$value;
		}

		$validator->on=empty($on) ? array() : array_combine($on,$on); // 保存验证器适用的场景
		$validator->except=empty($except) ? array() : array_combine($except,$except); // 保存不需要验证的场景

		return $validator;
	}

	/**
	 * Validates the specified object.
	 * @param CModel $object the data object being validated
	 * @param array $attributes the list of attributes to be validated. Defaults to null,
	 * meaning every attribute listed in {@link attributes} will be validated.
	 */
	// 验证指定的模型对象
	public function validate($object,$attributes=null)
	{
		if(is_array($attributes)) // 求交集，去除不存在的属性
			$attributes=array_intersect($this->attributes,$attributes);
		else
			$attributes=$this->attributes; // 赋值模型所有属性
		foreach($attributes as $attribute)
		{
			// 如果发生错误时不跳出，或者这个属性还没有错误，就继续执行验证
			if(!$this->skipOnError || !$object->hasErrors($attribute))
				$this->validateAttribute($object,$attribute);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * Do not override this method if the validator does not support client-side validation.
	 * Two predefined JavaScript variables can be used:
	 * <ul>
	 * <li>value: the value to be validated</li>
	 * <li>messages: an array used to hold the validation error messages for the value</li>
	 * </ul>
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script. Null if the validator does not support client-side validation.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object,$attribute)
	{
	}

	/**
	 * Returns a value indicating whether the validator applies to the specified scenario.
	 * A validator applies to a scenario as long as any of the following conditions is met:
	 * <ul>
	 * <li>the validator's "on" property is empty</li>
	 * <li>the validator's "on" property contains the specified scenario</li>
	 * </ul>
	 * @param string $scenario scenario name
	 * @return boolean whether the validator applies to the specified scenario.
	 */
	// 返回验证器是否适用于当前情景
	public function applyTo($scenario)
	{
		// 不需要验证的场景，直接返回false
		if(isset($this->except[$scenario]))
			return false;

		// 验证器没有场景或场景匹配返回true，否则返回false
		return empty($this->on) || isset($this->on[$scenario]);
	}

	/**
	 * Adds an error about the specified attribute to the active record.
	 * This is a helper method that performs message selection and internationalization.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the attribute being validated
	 * @param string $message the error message
	 * @param array $params values for the placeholders in the error message
	 */
	// 给模型属性添加错误
	// 这是一个辅助方法，执行消息的选择和国际化
	protected function addError($object,$attribute,$message,$params=array())
	{
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		$object->addError($attribute,strtr($message,$params));
	}

	/**
	 * Checks if the given value is empty.
	 * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
	 * Note that this method is different from PHP empty(). It will return false when the value is 0.
	 * @param mixed $value the value to be checked
	 * @param boolean $trim whether to perform trimming before checking if the string is empty. Defaults to false.
	 * @return boolean whether the value is empty
	 */
	// 检查给定的值是否为空
	protected function isEmpty($value,$trim=false)
	{
		return $value===null || $value===array() || $value==='' || $trim && is_scalar($value) && trim($value)==='';
	}
}

