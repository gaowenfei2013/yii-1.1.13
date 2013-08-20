<?php

/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $phone
 * @property integer $is_banned
 * @property integer $need_reset_password
 * @property string $login_time
 * @property string $login_ip
 * @property integer $is_email_validated
 * @property integer $is_phone_validated
 * @property string $register_by
 */
class User extends CActiveRecord
{

	private $_oldPassword = NULL;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
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
		return '{{user}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username, password', 'required'),
			array('is_banned, need_reset_password, is_email_validated, is_phone_validated', 'numerical', 'integerOnly'=>true),
			array('username, email', 'length', 'max'=>40),
			array('email', 'email'),
			array('password', 'length', 'max'=>60),
			array('phone', 'length', 'max'=>11),
			array('login_time, register_by', 'length', 'max'=>10),
			array('login_time', 'length', 'max'=>10),
			array('login_ip', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, username, password, email, phone, is_banned, need_reset_password, login_time, login_ip, is_email_validated, is_phone_validated', 'safe', 'on'=>'search'),
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
			'id'                  => '用户ID',
			'username'            => '用户名',
			'password'            => '密码',
			'email'               => '邮箱',
			'phone'               => '手机',
			'is_banned'              => '是否禁用',
			'need_reset_password' => '是否需要重设密码',
			'login_time'          => '最近登录时间',
			'login_ip'            => '最近登录IP',
			'is_email_validated'  => '邮箱是否验证通过',
			'is_phone_validated'  => '手机是否验证通过',
			'register_by' => '注册方式',
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
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('is_banned',$this->is_banned);
		$criteria->compare('need_reset_password',$this->need_reset_password);
		$criteria->compare('login_time',$this->login_time,true);
		$criteria->compare('login_ip',$this->login_ip,true);
		$criteria->compare('is_email_validated',$this->is_email_validated);
		$criteria->compare('is_phone_validated',$this->is_phone_validated);
		$criteria->compare('register_by',$this->register_by,true);
		
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * 验证密码
	 * @param string $password 明文密码
	 * @return boolean 正确返回TRUE，否则返回FALSE
	 */
	public function validatePassword($password)
	{
		return crypt($password, $this->password) === $this->password;
	}

	/**
	 * 加密密码
	 * @param string $password 明文密码
	 * @return string 返回加密后的密码
	 */
	public function hashPassword($password)
	{
		if ( ! CRYPT_BLOWFISH)
		{
			throw new CException('crypt does not support Blowfish.');
		}

		return crypt($password, $this->generateSalt());
	}

	/**
	 * 产生一个 salt
	 * @param  integer $cost 用于产生Blowfish哈希算法salt的参数
	 * @return string salt
	 */
	protected function generateSalt($cost = 10)
	{
		if( ! is_numeric($cost) || $cost < 4 || $cost > 31)
		{
			throw new CException(Yii::t('Cost parameter must be between 4 and 31.'));
		}

		// Get some pseudo-random data from mt_rand().
		$rand = '';
		for($i = 0; $i < 8; ++$i)
		{
			$rand.=pack('S',mt_rand(0,0xffff));
		}

		// Add the microtime for a little more entropy.
		$rand .= microtime();
		// Mix the bits cryptographically.
		$rand = sha1($rand,true);
		// Form the prefix that specifies hash algorithm type and cost parameter.
		$salt = '$2a$'.str_pad((int)$cost, 2, '0', STR_PAD_RIGHT).'$';
		// Append the random salt string in the required base64 format.
		$salt .= strtr(substr(base64_encode($rand), 0, 22), array('+'=>'.'));
		return $salt;
	}

	protected function beforeSave()
	{
		// 保存时，如果是修改密码，那么加密密码，再更新数据库
		if (parent::beforeSave())
		{
			if ($this->_oldPassword !== $this->password)
			{
				$this->password = $this->hashPassword($this->password);
			}
			return TRUE;
		}
		return FALSE;
	}

	protected function afterSave()
	{
		if ($this->getIsNewRecord())
		{
			// 保存注册信息
			$userRegisterRecord             = new UserRegisterRecord;
			$userRegisterRecord->user_id    = $this->id;
			$userRegisterRecord->time       = time();
			$userRegisterRecord->ip         = Yii::app()->request->getUserHostAddress();
			$userRegisterRecord->user_agent = Yii::app()->request->getUserAgent();
			$userRegisterRecord->source_id  = 0;
			$userRegisterRecord->save();

			// 建立资料信息
			$userProfile          = new UserProfile;
			$userProfile->user_id = $this->id;
			$userProfile->save();
		}
	}

	public function setOldPassword($oldPassword)
	{
		$this->_oldPassword = $oldPassword;
	}

}