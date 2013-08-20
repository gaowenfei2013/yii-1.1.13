<?php

/**
 * This is the model class for table "{{user_profile}}".
 *
 * The followings are the available columns in table '{{user_profile}}':
 * @property string $user_id
 * @property integer $level_id
 * @property string $real_name
 * @property string $tel
 * @property string $phone
 * @property string $nickname
 * @property integer $gender
 * @property string $avatar
 * @property string $birthday
 * @property string $qq
 * @property string $msn
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $safe_question
 * @property string $safe_answer
 * @property string $money
 * @property string $integral
 */
class UserProfile extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserProfile the static model class
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
		return '{{user_profile}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id', 'required'),
			array('level_id, gender', 'numerical', 'integerOnly'=>true),
			array('user_id, real_name, province, city, area, money, integral', 'length', 'max'=>10),
			array('tel, nickname, qq', 'length', 'max'=>20),
			array('phone', 'length', 'max'=>11),
			array('avatar, safe_question', 'length', 'max'=>50),
			array('msn', 'length', 'max'=>60),
			array('safe_answer', 'length', 'max'=>100),
			array('birthday', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, level_id, real_name, tel, phone, nickname, gender, avatar, birthday, qq, msn, province, city, area, safe_question, safe_answer, money, integral', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'level_id' => 'Level',
			'real_name' => 'Real Name',
			'tel' => 'Tel',
			'phone' => 'Phone',
			'nickname' => 'Nickname',
			'gender' => 'Gender',
			'avatar' => 'Avatar',
			'birthday' => 'Birthday',
			'qq' => 'Qq',
			'msn' => 'Msn',
			'province' => 'Province',
			'city' => 'City',
			'area' => 'Area',
			'safe_question' => 'Safe Question',
			'safe_answer' => 'Safe Answer',
			'money' => 'Money',
			'integral' => 'Integral',
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
		$criteria->compare('level_id',$this->level_id);
		$criteria->compare('real_name',$this->real_name,true);
		$criteria->compare('tel',$this->tel,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('nickname',$this->nickname,true);
		$criteria->compare('gender',$this->gender);
		$criteria->compare('avatar',$this->avatar,true);
		$criteria->compare('birthday',$this->birthday,true);
		$criteria->compare('qq',$this->qq,true);
		$criteria->compare('msn',$this->msn,true);
		$criteria->compare('province',$this->province,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('area',$this->area,true);
		$criteria->compare('safe_question',$this->safe_question,true);
		$criteria->compare('safe_answer',$this->safe_answer,true);
		$criteria->compare('money',$this->money,true);
		$criteria->compare('integral',$this->integral,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}