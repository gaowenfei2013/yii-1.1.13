<?php
/**
 * CModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModel is the base class providing the common features needed by data model objects.
 *
 * CModel defines the basic framework for data models that need to be validated.
 *
 * @property CList $validatorList All the validators declared in the model.
 * @property array $validators The validators applicable to the current {@link scenario}.
 * @property array $errors Errors for all attributes or the specified attribute. Empty array is returned if no error.
 * @property array $attributes Attribute values (name=>value).
 * @property string $scenario The scenario that this model is in.
 * @property array $safeAttributeNames Safe attribute names.
 * @property CMapIterator $iterator An iterator for traversing the items in the list.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
// 模型类基类
abstract class CModel extends CComponent implements IteratorAggregate, ArrayAccess
{
	// 保存错误信息
	// 属性名对应一个数组（错误信息）
	private $_errors=array();	// attribute name => array of errors

	// 验证器
	private $_validators;  		// validators

	// 场景
	private $_scenario='';  	// scenario

	/**
	 * Returns the list of attribute names of the model.
	 * @return array list of attribute names.
	 */
	// 返回模型的所有属性名称
	abstract public function attributeNames();

	/**
	 * Returns the validation rules for attributes.
	 *
	 * This method should be overridden to declare validation rules.
	 * Each rule is an array with the following structure:
	 * <pre>
	 * array('attribute list', 'validator name', 'on'=>'scenario name', ...validation parameters...)
	 * </pre>
	 * where
	 * <ul>
	 * <li>attribute list: specifies the attributes (separated by commas) to be validated;</li>
	 * <li>validator name: specifies the validator to be used. It can be the name of a model class
	 *   method, the name of a built-in validator, or a validator class (or its path alias).
	 *   A validation method must have the following signature:
	 * <pre>
	 * // $params refers to validation parameters given in the rule
	 * function validatorName($attribute,$params)
	 * </pre>
	 *   A built-in validator refers to one of the validators declared in {@link CValidator::builtInValidators}.
	 *   And a validator class is a class extending {@link CValidator}.</li>
	 * <li>on: this specifies the scenarios when the validation rule should be performed.
	 *   Separate different scenarios with commas. If this option is not set, the rule
	 *   will be applied in any scenario that is not listed in "except". Please see {@link scenario} for more details about this option.</li>
	 * <li>except: this specifies the scenarios when the validation rule should not be performed.
	 *   Separate different scenarios with commas. Please see {@link scenario} for more details about this option.</li>
	 * <li>additional parameters are used to initialize the corresponding validator properties.
	 *   Please refer to individal validator class API for possible properties.</li>
	 * </ul>
	 *
	 * The following are some examples:
	 * <pre>
	 * array(
	 *     array('username', 'required'),
	 *     array('username', 'length', 'min'=>3, 'max'=>12),
	 *     array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
	 *     array('password', 'authenticate', 'on'=>'login'),
	 * );
	 * </pre>
	 *
	 * Note, in order to inherit rules defined in the parent class, a child class needs to
	 * merge the parent rules with child rules using functions like array_merge().
	 *
	 * @return array validation rules to be applied when {@link validate()} is called.
	 * @see scenario
	 */
	// 返回属性的验证规则
	public function rules()
	{
		return array();
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 * The return value should be an array of behavior configurations indexed by
	 * behavior names. Each behavior configuration can be either a string specifying
	 * the behavior class or an array of the following structure:
	 * <pre>
	 * 'behaviorName'=>array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </pre>
	 *
	 * Note, the behavior classes must implement {@link IBehavior} or extend from
	 * {@link CBehavior}. Behaviors declared in this method will be attached
	 * to the model when it is instantiated.
	 *
	 * For more details about behaviors, see {@link CComponent}.
	 * @return array the behavior configurations (behavior name=>behavior configuration)
	 */
	// 返回模型需要添加的行为
	public function behaviors()
	{
		return array();
	}

	/**
	 * Returns the attribute labels.
	 * Attribute labels are mainly used in error messages of validation.
	 * By default an attribute label is generated using {@link generateAttributeLabel}.
	 * This method allows you to explicitly specify attribute labels.
	 *
	 * Note, in order to inherit labels defined in the parent class, a child class needs to
	 * merge the parent labels with child labels using functions like array_merge().
	 *
	 * @return array attribute labels (name=>label)
	 * @see generateAttributeLabel
	 */
	// 返回属性的标签
	// 属性名对应标签名，如 array('username' => '用户名', 'password' => '密码');
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Performs the validation.
	 *
	 * This method executes the validation rules as declared in {@link rules}.
	 * Only the rules applicable to the current {@link scenario} will be executed.
	 * A rule is considered applicable to a scenario if its 'on' option is not set
	 * or contains the scenario.
	 *
	 * Errors found during the validation can be retrieved via {@link getErrors}.
	 *
	 * @param array $attributes list of attributes that should be validated. Defaults to null,
	 * meaning any attribute listed in the applicable validation rules should be
	 * validated. If this parameter is given as a list of attributes, only
	 * the listed attributes will be validated.
	 * @param boolean $clearErrors whether to call {@link clearErrors} before performing validation
	 * @return boolean whether the validation is successful without any error.
	 * @see beforeValidate
	 * @see afterValidate
	 */
	// 执行验证，返回是否验证成功
	// 这个方法执行验证规则中提供的规则。
	// 只有适用与当前场景的规则才会被执行
	// 
	// 如果验证失败，我们可以调用 getErrors 返回错误信息
	// 
	// $attributes 表示哪些属性需要验证，如果设置为null，表示所有 rules 提到的属性都要验证
	// $clearErrors 在验证时是否清空之前的错误信息，默认true
	public function validate($attributes=null, $clearErrors=true)
	{
		// 是否需要清除所有错误信息
		if($clearErrors)
			$this->clearErrors();

		// 调用beforeValidate，如果返回true，才会执行验证，否则直接验证不成功
		if($this->beforeValidate())
		{
			// 遍历适用的验证器，执行验证
			foreach($this->getValidators() as $validator)
				$validator->validate($this,$attributes);

			// 调用验证后执行 afterValidate
			$this->afterValidate();

			// 如果没有错误，那么验证成功
			return !$this->hasErrors();
		}
		else
			return false;
	}

	/**
	 * This method is invoked after a model instance is created by new operator.
	 * The default implementation raises the {@link onAfterConstruct} event.
	 * You may override this method to do postprocessing after model creation.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	// 这个方法在模型初始化后执行
	// 默认将促发onAfterConstruct事件
	// 你可以重写此方法，在模型初始化后处理一些事情
	// 确保调用parent::afterConstruct() 以便发起onAfterConstruct事件
	protected function afterConstruct()
	{
		if($this->hasEventHandler('onAfterConstruct'))
			$this->onAfterConstruct(new CEvent($this));
	}

	/**
	 * This method is invoked before validation starts.
	 * The default implementation calls {@link onBeforeValidate} to raise an event.
	 * You may override this method to do preliminary checks before validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 * @return boolean whether validation should be executed. Defaults to true.
	 * If false is returned, the validation will stop and the model is considered invalid.
	 */
	// 这个方法将在执行验证前执行
	protected function beforeValidate()
	{
		$event=new CModelEvent($this);
		$this->onBeforeValidate($event);
		return $event->isValid; // 如果这里返回false，验证将退出，就是验证不成功咯
	}

	/**
	 * This method is invoked after validation ends.
	 * The default implementation calls {@link onAfterValidate} to raise an event.
	 * You may override this method to do postprocessing after validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 */
	// 这个方法在验证后执行
	protected function afterValidate()
	{
		$this->onAfterValidate(new CEvent($this));
	}

	/**
	 * This event is raised after the model instance is created by new operator.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterConstruct($event)
	{
		$this->raiseEvent('onAfterConstruct',$event);
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CModelEvent $event the event parameter
	 */
	public function onBeforeValidate($event)
	{
		$this->raiseEvent('onBeforeValidate',$event);
	}

	/**
	 * This event is raised after the validation is performed.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterValidate($event)
	{
		$this->raiseEvent('onAfterValidate',$event);
	}

	/**
	 * Returns all the validators declared in the model.
	 * This method differs from {@link getValidators} in that the latter
	 * would only return the validators applicable to the current {@link scenario}.
	 * Also, since this method return a {@link CList} object, you may
	 * manipulate it by inserting or removing validators (useful in behaviors).
	 * For example, <code>$model->validatorList->add($newValidator)</code>.
	 * The change made to the {@link CList} object will persist and reflect
	 * in the result of the next call of {@link getValidators}.
	 * @return CList all the validators declared in the model.
	 * @since 1.1.2
	 */
	// 返回模型中所有的验证
	public function getValidatorList()
	{
		if($this->_validators===null)
			$this->_validators=$this->createValidators();
		return $this->_validators;
	}

	/**
	 * Returns the validators applicable to the current {@link scenario}.
	 * @param string $attribute the name of the attribute whose validators should be returned.
	 * If this is null, the validators for ALL attributes in the model will be returned.
	 * @return array the validators applicable to the current {@link scenario}.
	 */
	// 返回适用当前情景的验证器
	// $attribute 指定的属性名，默认null，表示所有属性
	public function getValidators($attribute=null)
	{
		// 创建验证器
		if($this->_validators===null)
			$this->_validators=$this->createValidators();

		// 待返回的验证器
		$validators=array();
		// 当前场景
		$scenario=$this->getScenario();

		// 遍历，找出适用的验证器
		foreach($this->_validators as $validator)
		{
			// 如果适用于当前情景
			if($validator->applyTo($scenario))
			{
				if($attribute===null || in_array($attribute,$validator->attributes,true))
					$validators[]=$validator;
			}
		}

		return $validators;
	}

	/**
	 * Creates validator objects based on the specification in {@link rules}.
	 * This method is mainly used internally.
	 * @return CList validators built based on {@link rules()}.
	 */
	// 根据rules中的规则创建验证器对象
	public function createValidators()
	{
		$validators=new CList;

		foreach($this->rules() as $rule)
		{
			if(isset($rule[0],$rule[1]))  // attributes, validator name
				$validators->add(CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2)));
			else
				throw new CException(Yii::t('yii','{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.',
					array('{class}'=>get_class($this))));
		}
		return $validators;
	}

	/**
	 * Returns a value indicating whether the attribute is required.
	 * This is determined by checking if the attribute is associated with a
	 * {@link CRequiredValidator} validation rule in the current {@link scenario}.
	 * @param string $attribute attribute name
	 * @return boolean whether the attribute is required
	 */
	// 返回指定属性是否必须的
	// 就是检查这个属性有没有关联CRequireValidator
	public function isAttributeRequired($attribute)
	{
		foreach($this->getValidators($attribute) as $validator)
		{
			if($validator instanceof CRequiredValidator)
				return true;
		}
		return false;
	}

	/**
	 * Returns a value indicating whether the attribute is safe for massive assignments.
	 * @param string $attribute attribute name
	 * @return boolean whether the attribute is safe for massive assignments
	 * @since 1.1
	 */
	// 返回这个属性是否是安全的大规模任务
	public function isAttributeSafe($attribute)
	{
		$attributes=$this->getSafeAttributeNames();
		return in_array($attribute,$attributes);
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param string $attribute the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 * @see attributeLabels
	 */
	// 返回指定属性的文本标签
	public function getAttributeLabel($attribute)
	{
		// 从定义的标签中查找
		$labels=$this->attributeLabels();
		if(isset($labels[$attribute]))
			return $labels[$attribute];
		else
			// 自动生成一个
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Returns a value indicating whether there is any validation error.
	 * @param string $attribute attribute name. Use null to check all attributes.
	 * @return boolean whether there is any error.
	 */
	// 返回是否有验证错误
	// 可以详细的指定判断哪个属性的
	public function hasErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors!==array();
		else
			return isset($this->_errors[$attribute]);
	}

	/**
	 * Returns the errors for all attribute or a single attribute.
	 * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
	 * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
	 */
	// 返回单个属性或者全部属性的验证错误信息
	// 没有错误返回一个空array()
	public function getErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors;
		else
			return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
	}

	/**
	 * Returns the first error of the specified attribute.
	 * @param string $attribute attribute name.
	 * @return string the error message. Null is returned if no error.
	 */
	// 返回指定属性的第一个错误信息
	// 没有错误返回null
	public function getError($attribute)
	{
		return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
	}

	/**
	 * Adds a new error to the specified attribute.
	 * @param string $attribute attribute name
	 * @param string $error new error message
	 */
	// 添加一个错误信息到指定的属性
	public function addError($attribute,$error)
	{
		$this->_errors[$attribute][]=$error;
	}

	/**
	 * Adds a list of errors.
	 * @param array $errors a list of errors. The array keys must be attribute names.
	 * The array values should be error messages. If an attribute has multiple errors,
	 * these errors must be given in terms of an array.
	 * You may use the result of {@link getErrors} as the value for this parameter.
	 */
	// 添加多个错误信息
	// $errors 是 属性=>错误信息 组成的数组
	public function addErrors($errors)
	{
		foreach($errors as $attribute=>$error)
		{
			if(is_array($error))
			{
				foreach($error as $e)
					$this->addError($attribute, $e);
			}
			else
				$this->addError($attribute, $error);
		}
	}

	/**
	 * Removes errors for all attributes or a single attribute.
	 * @param string $attribute attribute name. Use null to remove errors for all attribute.
	 */
	// 清空指定属性或者全部属性的错误信息
	public function clearErrors($attribute=null)
	{
		if($attribute===null)
			$this->_errors=array();
		else
			unset($this->_errors[$attribute]);
	}

	/**
	 * Generates a user friendly attribute label.
	 * This is done by replacing underscores or dashes with blanks and
	 * changing the first letter of each word to upper case.
	 * For example, 'department_name' or 'DepartmentName' becomes 'Department Name'.
	 * @param string $name the column name
	 * @return string the attribute label
	 */
	// 创建一个友好的属性标签
	public function generateAttributeLabel($name)
	{
		return ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}

	/**
	 * Returns all attribute values.
	 * @param array $names list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes as listed in {@link attributeNames} will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @return array attribute values (name=>value).
	 */
	// 返回属性值
	public function getAttributes($names=null)
	{
		$values=array();
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;

		if(is_array($names))
		{
			$values2=array();
			foreach($names as $name)
				$values2[$name]=isset($values[$name]) ? $values[$name] : null;
			return $values2;
		}
		else
			return $values;
	}

	/**
	 * Sets the attribute values in a massive way.
	 * @param array $values attribute values (name=>value) to be set.
	 * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
	 * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
	 * @see getSafeAttributeNames
	 * @see attributeNames
	 */
	// 设置属性值
	// $safeOnly 表示是否只分配安全属性
	public function setAttributes($values,$safeOnly=true)
	{
		if(!is_array($values))
			return;
		// 根据是否安全，返回属性
		$attributes=array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());
		foreach($values as $name=>$value)
		{
			if(isset($attributes[$name]))
				$this->$name=$value;
			elseif($safeOnly)
				$this->onUnsafeAttribute($name,$value);
		}
	}

	/**
	 * Sets the attributes to be null.
	 * @param array $names list of attributes to be set null. If this parameter is not given,
	 * all attributes as specified by {@link attributeNames} will have their values unset.
	 * @since 1.1.3
	 */
	// 将全部属性的值设为null
	public function unsetAttributes($names=null)
	{
		if($names===null)
			$names=$this->attributeNames();
		foreach($names as $name)
			$this->$name=null;
	}

	/**
	 * This method is invoked when an unsafe attribute is being massively assigned.
	 * The default implementation will log a warning message if YII_DEBUG is on.
	 * It does nothing otherwise.
	 * @param string $name the unsafe attribute name
	 * @param mixed $value the attribute value
	 * @since 1.1.1
	 */
	public function onUnsafeAttribute($name,$value)
	{
		if(YII_DEBUG)
			Yii::log(Yii::t('yii','Failed to set unsafe attribute "{attribute}" of "{class}".',array('{attribute}'=>$name, '{class}'=>get_class($this))),CLogger::LEVEL_WARNING);
	}

	/**
	 * Returns the scenario that this model is used in.
	 *
	 * Scenario affects how validation is performed and which attributes can
	 * be massively assigned.
	 *
	 * A validation rule will be performed when calling {@link validate()}
	 * if its 'except' value does not contain current scenario value while
	 * 'on' option is not set or contains the current scenario value.
	 *
	 * And an attribute can be massively assigned if it is associated with
	 * a validation rule for the current scenario. Note that an exception is
	 * the {@link CUnsafeValidator unsafe} validator which marks the associated
	 * attributes as unsafe and not allowed to be massively assigned.
	 *
	 * @return string the scenario that this model is in.
	 */
	// 返回这个模型当前的情景
	// 情景表示当前情况下是否需要验证这个属性
	// 
	public function getScenario()
	{
		return $this->_scenario;
	}

	/**
	 * Sets the scenario for the model.
	 * @param string $value the scenario that this model is in.
	 * @see getScenario
	 */
	// 设置模型的情景
	public function setScenario($value)
	{
		$this->_scenario=$value;
	}

	/**
	 * Returns the attribute names that are safe to be massively assigned.
	 * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
	 * @return array safe attribute names
	 */
	// 返回所有安全属性的名称
	// 所谓安全属性，就是配备了安全验证器的属性，验证器类有个属性 safe 默认true，使用这类验证器的属性都是安全的，若设为false，就是不安全的
	public function getSafeAttributeNames()
	{
		$attributes=array(); // 安全的属性
		$unsafe=array(); // 不安全属性

		foreach($this->getValidators() as $validator) // 遍历所有的验证器
		{
			if(!$validator->safe) // 如果验证器sale为false
			{
				foreach($validator->attributes as $name) // 那么绑定这个验证器的属性就是非安全的
					$unsafe[]=$name;
			}
			else
			{
				foreach($validator->attributes as $name) // 安全的
					$attributes[$name]=true;
			}
		}

		// 再筛检
		foreach($unsafe as $name)
			unset($attributes[$name]);
		return array_keys($attributes);
	}

	/**
	 * Returns an iterator for traversing the attributes in the model.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	// 返回一个用于遍历属性的迭代器
	public function getIterator()
	{
		$attributes=$this->getAttributes();
		return new CMapIterator($attributes);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return property_exists($this,$offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset,$item)
	{
		$this->$offset=$item;
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}
}
