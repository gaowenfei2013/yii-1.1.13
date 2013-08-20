<?php

/**
 * This is the model class for table "{{admin}}".
 *
 * The followings are the available columns in table '{{admin}}':
 * @property integer $id
 * @property integer $admin_group_id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property integer $banned
 * @property string $real_name
 * @property string $salt
 */
class Admin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Admin the static model class
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
		return '{{admin}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username, password, email, salt', 'required'),
			array('admin_group_id, banned', 'numerical', 'integerOnly'=>true),
			array('username, email', 'length', 'max'=>40),
			array('password', 'length', 'max'=>32),
			array('real_name', 'length', 'max'=>20),
			array('salt', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, admin_group_id, username, password, email, banned, real_name, salt', 'safe', 'on'=>'search'),
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
			'admin_group_id' => 'Admin Group',
			'username' => 'Username',
			'password' => 'Password',
			'email' => 'Email',
			'banned' => 'Banned',
			'real_name' => 'Real Name',
			'salt' => 'Salt',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('admin_group_id',$this->admin_group_id);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('banned',$this->banned);
		$criteria->compare('real_name',$this->real_name,true);
		$criteria->compare('salt',$this->salt,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * 验证密码是否正确
	 * @param  string $password 
	 * @return boolean 正确返回TRUE，否则返回FALSE
	 */
	public function validatePassword($password)
	{
		return $this->hashPassword($password, $this->salt) === $this->password;
	}

	/**
	 * 返回加密后的密码
	 * @param  string $password 明文密码
	 * @param  string $salt password salt
	 * @return string 加密后的密码
	 */
	public function hashPassword($password, $salt)
	{
		return md5($salt.$password);
	}

	/**
	 * 产生一个password salt
	 * @return string password salt
	 */
	public function generateSalt()
	{
		return substr(md5(time().uniqid('', TRUE)), 10, 10);
	}

	/**
	 * 保存前给密码加密
	 * @return boolean 成功返回TRUE，否则返回FALSE
	 */
	protected function beforeSave()
	{
		if(parent::beforeSave())
		{
			$salt = $this->generateSalt();
			$this->password = hashPassword($this->password, $salt);
			$this->salt = $salt;
			return true;
		}
		else
		{
			return false;
		}
	}

}