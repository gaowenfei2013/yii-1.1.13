<?php
/**
 * This file contains classes implementing security manager feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSecurityManager provides private keys, hashing and encryption functions.
 *
 * CSecurityManager is used by Yii components and applications for security-related purpose.
 * For example, it is used in cookie validation feature to prevent cookie data
 * from being tampered.
 *
 * CSecurityManager is mainly used to protect data from being tampered and viewed.
 * It can generate HMAC and encrypt the data. The private key used to generate HMAC
 * is set by {@link setValidationKey ValidationKey}. The key used to encrypt data is
 * specified by {@link setEncryptionKey EncryptionKey}. If the above keys are not
 * explicitly set, random keys will be generated and used.
 *
 * To protected data with HMAC, call {@link hashData()}; and to check if the data
 * is tampered, call {@link validateData()}, which will return the real data if
 * it is not tampered. The algorithm used to generated HMAC is specified by
 * {@link validation}.
 *
 * To encrypt and decrypt data, call {@link encrypt()} and {@link decrypt()}
 * respectively, which uses 3DES encryption algorithm.  Note, the PHP Mcrypt
 * extension must be installed and loaded.
 *
 * CSecurityManager is a core application component that can be accessed via
 * {@link CApplication::getSecurityManager()}.
 *
 * @property string $validationKey The private key used to generate HMAC.
 * If the key is not explicitly set, a random one is generated and returned.
 * @property string $encryptionKey The private key used to encrypt/decrypt data.
 * If the key is not explicitly set, a random one is generated and returned.
 * @property string $validation
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */

// 安全组件
class CSecurityManager extends CApplicationComponent
{
	// 验证数据是否被篡改使用的key键值，保存在globalstate中的
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	// 加密解密使用的key
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	/**
	 * @var string the name of the hashing algorithm to be used by {@link computeHMAC}.
	 * See {@link http://php.net/manual/en/function.hash-algos.php hash-algos} for the list of possible
	 * hash algorithms. Note that if you are using PHP 5.1.1 or below, you can only use 'sha1' or 'md5'.
	 *
	 * Defaults to 'sha1', meaning using SHA1 hash algorithm.
	 * @since 1.1.3
	 */
	// 指定使用哪个hash算法
	public $hashAlgorithm='sha1';
	/**
	 * @var mixed the name of the crypt algorithm to be used by {@link encrypt} and {@link decrypt}.
	 * This will be passed as the first parameter to {@link http://php.net/manual/en/function.mcrypt-module-open.php mcrypt_module_open}.
	 *
	 * This property can also be configured as an array. In this case, the array elements will be passed in order
	 * as parameters to mcrypt_module_open. For example, <code>array('rijndael-256', '', 'ofb', '')</code>.
	 *
	 * Defaults to 'des', meaning using DES crypt algorithm.
	 * @since 1.1.3
	 */
	// 指定使用哪个加密算法
	public $cryptAlgorithm='des';

	private $_validationKey; // 验证用的key
	private $_encryptionKey; // 加密用的key
	private $_mbstring; // 标识是否加载了mbstring扩展

	// 初始化
	public function init()
	{
		parent::init();
		$this->_mbstring=extension_loaded('mbstring'); // 是否加载了mbstring扩展
	}

	/**
	 * @return string a randomly generated private key
	 */
	// 产生一个随机key
	protected function generateRandomKey()
	{
		return sprintf('%08x%08x%08x%08x',mt_rand(),mt_rand(),mt_rand(),mt_rand());
	}

	/**
	 * @return string the private key used to generate HMAC.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	// 获取验证用的key
	public function getValidationKey()
	{
		// 自己定义了，返回自己的
		if($this->_validationKey!==null)
			return $this->_validationKey;
		else
		{
			// 看看是否保存在了globalstate 中
			if(($key=Yii::app()->getGlobalState(self::STATE_VALIDATION_KEY))!==null)
				$this->setValidationKey($key); // 设置
			else
			{
				$key=$this->generateRandomKey(); // 产生一个随机key
				$this->setValidationKey($key); // 设置
				Yii::app()->setGlobalState(self::STATE_VALIDATION_KEY,$key); // 写入globalstate
			}
			return $this->_validationKey; // 返回
		}
	}

	/**
	 * @param string $value the key used to generate HMAC
	 * @throws CException if the key is empty
	 */
	// 设置验证用的key
	public function setValidationKey($value)
	{
		if(!empty($value))
			$this->_validationKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validationKey cannot be empty.'));
	}

	/**
	 * @return string the private key used to encrypt/decrypt data.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	// 返回加密用的key
	public function getEncryptionKey()
	{
		// 自己定义了
		if($this->_encryptionKey!==null)
			return $this->_encryptionKey;
		else
		{
			// 是否保存在了globalstate中
			if(($key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY))!==null)
				$this->setEncryptionKey($key);
			else
			{
				$key=$this->generateRandomKey(); // 随机产生
				$this->setEncryptionKey($key);
				Yii::app()->setGlobalState(self::STATE_ENCRYPTION_KEY,$key);
			}
			return $this->_encryptionKey;
		}
	}

	/**
	 * @param string $value the key used to encrypt/decrypt data.
	 * @throws CException if the key is empty
	 */
	// 设置加密key
	public function setEncryptionKey($value)
	{
		if(!empty($value))
			$this->_encryptionKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.encryptionKey cannot be empty.'));
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @return string
	 */
	// 返回验证用的hash算法
	public function getValidation()
	{
		return $this->hashAlgorithm;
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @param string $value -
	 */
	// 设置验证用的hash算法
	public function setValidation($value)
	{
		$this->hashAlgorithm=$value;
	}

	/**
	 * Encrypts data.
	 * @param string $data data to be encrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the encrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded
	 */
	// 加密
	public function encrypt($data,$key=null)
	{
		$module=$this->openCryptModule();
		$key=$this->substr($key===null ? md5($this->getEncryptionKey()) : $key,0,mcrypt_enc_get_key_size($module));
		srand();
		$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		mcrypt_generic_init($module,$key,$iv);
		$encrypted=$iv.mcrypt_generic($module,$data);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Decrypts data
	 * @param string $data data to be decrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the decrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded
	 */
	// 解密
	public function decrypt($data,$key=null)
	{
		$module=$this->openCryptModule();
		$key=$this->substr($key===null ? md5($this->getEncryptionKey()) : $key,0,mcrypt_enc_get_key_size($module));
		$ivSize=mcrypt_enc_get_iv_size($module);
		$iv=$this->substr($data,0,$ivSize);
		mcrypt_generic_init($module,$key,$iv);
		$decrypted=mdecrypt_generic($module,$this->substr($data,$ivSize,$this->strlen($data)));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Opens the mcrypt module with the configuration specified in {@link cryptAlgorithm}.
	 * @return resource the mycrypt module handle.
	 * @since 1.1.3
	 */
	// 返回加密模块
	protected function openCryptModule()
	{
		if(extension_loaded('mcrypt'))
		{
			if(is_array($this->cryptAlgorithm))
				$module=@call_user_func_array('mcrypt_module_open',$this->cryptAlgorithm);
			else
				$module=@mcrypt_module_open($this->cryptAlgorithm,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));

			return $module;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Prefixes data with an HMAC.
	 * @param string $data data to be hashed.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string data prefixed with HMAC
	 */
	// 将hash摘要信息添加至数据的前面
	public function hashData($data,$key=null)
	{
		return $this->computeHMAC($data,$key).$data;
	}

	/**
	 * Validates if data is tampered.
	 * @param string $data data to be validated. The data must be previously
	 * generated using {@link hashData()}.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string the real data with HMAC stripped off. False if the data
	 * is tampered.
	 */
	// 验证数据有没有被改变，如果没有变返回真实数据，否则返回false
	public function validateData($data,$key=null)
	{
		$len=$this->strlen($this->computeHMAC('test'));
		if($this->strlen($data)>=$len)
		{
			$hmac=$this->substr($data,0,$len);
			$data2=$this->substr($data,$len,$this->strlen($data));
			return $hmac===$this->computeHMAC($data2,$key)?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Computes the HMAC for the data with {@link getValidationKey ValidationKey}.
	 * @param string $data data to be generated HMAC
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string the HMAC for the data
	 */
	// 计算hash摘要
	protected function computeHMAC($data,$key=null)
	{
		// 指定key
		if($key===null)
			$key=$this->getValidationKey();

		if(function_exists('hash_hmac'))
			return hash_hmac($this->hashAlgorithm, $data, $key);

		if(!strcasecmp($this->hashAlgorithm,'sha1'))
		{
			$pack='H40';
			$func='sha1';
		}
		else
		{
			$pack='H32';
			$func='md5';
		}
		if($this->strlen($key) > 64)
			$key=pack($pack, $func($key));
		if($this->strlen($key) < 64)
			$key=str_pad($key, 64, chr(0));
		$key=$this->substr($key,0,64);
		return $func((str_repeat(chr(0x5C), 64) ^ $key) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ $key) . $data)));
	}

	/**
	 * Returns the length of the given string.
	 * If available uses the multibyte string function mb_strlen.
	 * @param string $string the string being measured for length
	 * @return integer the length of the string
	 */
	// 返回字符长度
	private function strlen($string)
	{
		// 8bit编码就是utf-8
		return $this->_mbstring ? mb_strlen($string,'8bit') : strlen($string);
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * If available uses the multibyte string function mb_substr
	 * @param string $string the input string. Must be one character or longer.
	 * @param integer $start the starting position
	 * @param integer $length the desired portion length
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 */
	// 裁剪字符
	private function substr($string,$start,$length)
	{
		return $this->_mbstring ? mb_substr($string,$start,$length,'8bit') : substr($string,$start,$length);
	}
}
