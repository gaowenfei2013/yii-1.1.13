<?php

/**
 * This is the model class for table "{{user_oauth}}".
 *
 * The followings are the available columns in table '{{user_oauth}}':
 * @property string $id
 * @property string $uid
 * @property string $provider
 * @property string $access_token
 * @property integer $expires_in
 * @property string $refresh_token
 * @property string $user_id
 */
class UserOauth extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserOauth the static model class
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
		return '{{user_oauth}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('provider, access_token, expires_in', 'required'),
			array('expires_in', 'numerical', 'integerOnly'=>true),
			array('uid', 'length', 'max'=>50),
			array('provider, user_id', 'length', 'max'=>10),
			array('access_token, refresh_token', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uid, provider, access_token, expires_in, refresh_token, user_id', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'uid' => 'Uid',
			'provider' => 'Provider',
			'access_token' => 'Access Token',
			'expires_in' => 'Expires In',
			'refresh_token' => 'Refresh Token',
			'user_id' => 'User',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('uid',$this->uid,true);
		$criteria->compare('provider',$this->provider,true);
		$criteria->compare('access_token',$this->access_token,true);
		$criteria->compare('expires_in',$this->expires_in);
		$criteria->compare('refresh_token',$this->refresh_token,true);
		$criteria->compare('user_id',$this->user_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}