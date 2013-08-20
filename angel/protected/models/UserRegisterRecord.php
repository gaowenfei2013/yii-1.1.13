<?php

/**
 * This is the model class for table "{{user_register_record}}".
 *
 * The followings are the available columns in table '{{user_register_record}}':
 * @property string $user_id
 * @property string $time
 * @property string $ip
 * @property string $user_agent
 * @property integer $source_id
 */
class UserRegisterRecord extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserRegisterRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{user_register_record}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, time, ip, user_agent', 'required'),
			array('source_id', 'numerical', 'integerOnly'=>true),
			array('user_id, time', 'length', 'max'=>10),
			array('ip', 'length', 'max'=>15),
			array('user_agent', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, time, ip, user_agent, source_id', 'safe', 'on'=>'search'),
		);
	}
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => '用户ID',
			'time' => '注册时间',
			'ip' => '注册IP',
			'user_agent' => '浏览器信息',
			'source_id' => '注册来源',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('time',$this->time,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('user_agent',$this->user_agent,true);
		$criteria->compare('source_id',$this->source_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}