<?php

/**
 * CCaptchaAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptchaAction renders a CAPTCHA image.
 *
 * CCaptchaAction is used together with {@link CCaptcha} and {@link CCaptchaValidator}
 * to provide the {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA} feature.
 *
 * You must configure properties of CCaptchaAction to customize the appearance of
 * the generated image.
 *
 * Note, CCaptchaAction requires PHP GD2 extension.
 *
 * Using CAPTCHA involves the following steps:
 * <ol>
 * <li>Override {@link CController::actions()} and register an action of class CCaptchaAction with ID 'captcha'.</li>
 * <li>In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 * to be validated by the 'captcha' validator.</li>
 * <li>In the controller view, insert a {@link CCaptcha} widget in the form.</li>
 * </ol>
 *
 * @property string $verifyCode The verification code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets.captcha
 * @since 1.0
 */

// 验证码动作
// 作用生成验证码图片、验证器调用这个类的validate方法验证验证码
class CCaptchaAction extends CAction
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	// 刷新验证码图片的参数
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	// 验证码session名称的前缀
	const SESSION_VAR_PREFIX='Yii.CCaptchaAction.';
	/**
	 * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
	 * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
	 */
	// 同一个验证码显示的次数
	// 也就是验证同一个验证码次数操作这个数值，将重新分配验证码值
	public $testLimit = 3;
	/**
	 * @var integer the width of the generated CAPTCHA image. Defaults to 120.
	 */
	// 验证码图片的宽度
	public $width = 120;
	/**
	 * @var integer the height of the generated CAPTCHA image. Defaults to 50.
	 */
	// 验证码图片的高度
	public $height = 50;
	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	// 验证码文字的padding
	public $padding = 2;
	/**
	 * @var integer the background color. For example, 0x55FF00.
	 * Defaults to 0xFFFFFF, meaning white color.
	 */
	// 验证码图片的背景色，十六进制表示
	public $backColor = 0xFFFFFF;
	/**
	 * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
	 */
	// 验证码文字的颜色
	public $foreColor = 0x2040A0;
	/**
	 * @var boolean whether to use transparent background. Defaults to false.
	 */
	// 是否使用透明背景
	public $transparent = false;
	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 6.
	 */
	// 验证码文字的最小长度
	public $minLength = 6;
	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 7.
	 */
	// 验证码文字的最大长度
	public $maxLength = 7;
	/**
	 * @var integer the offset between characters. Defaults to -2. You can adjust this property
	 * in order to decrease or increase the readability of the captcha.
	 * @since 1.1.7
	 **/
	// 字符间的偏移
	public $offset = -2;
	/**
	 * @var string the TrueType font file. Defaults to Duality.ttf which is provided
	 * with the Yii release.
	 */
	// 字体文件
	public $fontFile;
	/**
	 * @var string the fixed verification code. When this is property is set,
	 * {@link getVerifyCode} will always return this value.
	 * This is mainly used in automated tests where we want to be able to reproduce
	 * the same verification code each time we run the tests.
	 * Defaults to null, meaning the verification code will be randomly generated.
	 * @since 1.1.4
	 */
	// 固定的验证码字符串
	public $fixedVerifyCode;
	/**
	 * @var string the graphic extension that will be used to draw CAPTCHA image. Possible values
	 * are 'gd', 'imagick' and null. Null value means that fallback mode will be used: ImageMagick
	 * is preferred over GD. Default value is null.
	 * @since 1.1.13
	 */
	// 图片处理扩展，可以是 imagick 或 gd
	// 默认null，会依次判断 imagick ，然后 gd ，先到先得哦
	public $backend;

	/**
	 * Runs the action.
	 */
	public function run()
	{
		// 刷新验证码
		if(isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request for regenerating code
		{
			$code=$this->getVerifyCode(true);
			echo CJSON::encode(array(
				'hash1'=>$this->generateValidationHash($code),
				'hash2'=>$this->generateValidationHash(strtolower($code)),
				// we add a random 'v' parameter so that FireFox can refresh the image
				// when src attribute of image tag is changed
				'url'=>$this->getController()->createUrl($this->getId(),array('v' => uniqid())),
			));
		}
		else
			$this->renderImage($this->getVerifyCode());
		Yii::app()->end();
	}

	/**
	 * Generates a hash code that can be used for client side validation.
	 * @param string $code the CAPTCHA code
	 * @return string a hash code generated from the CAPTCHA code
	 * @since 1.1.7
	 */
	// 产生 hash code 用于客户端验证
	public function generateValidationHash($code)
	{
		for($h=0,$i=strlen($code)-1;$i>=0;--$i)
			$h+=ord($code[$i]);
		return $h;
	}

	/**
	 * Gets the verification code.
	 * @param boolean $regenerate whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	// 返回验证码字符
	// $regenerate 是否新建
	public function getVerifyCode($regenerate=false)
	{
		// 如果设置了固定值
		if($this->fixedVerifyCode !== null)
			return $this->fixedVerifyCode;

		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey();
		if($session[$name] === null || $regenerate)
		{
			$session[$name] = $this->generateVerifyCode(); // 保存的验证码值
			$session[$name . 'count'] = 1; // 验证同一个验证码的次数
		}
		return $session[$name];
	}

	/**
	 * Validates the input to see if it matches the generated code.
	 * @param string $input user input
	 * @param boolean $caseSensitive whether the comparison should be case-sensitive
	 * @return boolean whether the input is valid
	 */
	// 验证输入的验证码是否正确
	// $input 输入的验证码
	// $caseSensitive 是否区分大小写
	public function validate($input,$caseSensitive)
	{
		// 保存在session中的验证码
		$code = $this->getVerifyCode();
		// 验证码是否匹配
		$valid = $caseSensitive ? ($input === $code) : strcasecmp($input,$code)===0;

		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey() . 'count';
		$session[$name] = $session[$name] + 1;
		// 同一个验证码验证次数操作限制，重写分配验证码
		if($session[$name] > $this->testLimit && $this->testLimit > 0)
			$this->getVerifyCode(true);
		return $valid;
	}

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	// 产生一个验证码字符串
	protected function generateVerifyCode()
	{
		if($this->minLength < 3)
			$this->minLength = 3;
		if($this->maxLength > 20)
			$this->maxLength = 20;
		if($this->minLength > $this->maxLength)
			$this->maxLength = $this->minLength;
		$length = mt_rand($this->minLength,$this->maxLength);

		$letters = 'bcdfghjklmnpqrstvwxyz';
		$vowels = 'aeiou';
		$code = '';
		for($i = 0; $i < $length; ++$i)
		{
			if($i % 2 && mt_rand(0,10) > 2 || !($i % 2) && mt_rand(0,10) > 9)
				$code.=$vowels[mt_rand(0,4)];
			else
				$code.=$letters[mt_rand(0,20)];
		}

		return $code;
	}

	/**
	 * Returns the session variable name used to store verification code.
	 * @return string the session variable name
	 */
	// 返回用于保存验证码的 session 名称
	protected function getSessionKey()
	{
		return self::SESSION_VAR_PREFIX . Yii::app()->getId() . '.' . $this->getController()->getUniqueId() . '.' . $this->getId();
	}

	/**
	 * Renders the CAPTCHA image based on the code using library specified in the {@link $backend} property.
	 * @param string $code the verification code
	 */
	// 输出验证码图片
	protected function renderImage($code)
	{
		if($this->backend===null && CCaptcha::checkRequirements('imagick') || $this->backend==='imagick')
			$this->renderImageImagick($code);
		else if($this->backend===null && CCaptcha::checkRequirements('gd') || $this->backend==='gd')
			$this->renderImageGD($code);
	}

	/**
	 * Renders the CAPTCHA image based on the code using GD library.
	 * @param string $code the verification code
	 * @since 1.1.13
	 */
	// gd渲染
	protected function renderImageGD($code)
	{
		$image = imagecreatetruecolor($this->width,$this->height);

		$backColor = imagecolorallocate($image,
				(int)($this->backColor % 0x1000000 / 0x10000),
				(int)($this->backColor % 0x10000 / 0x100),
				$this->backColor % 0x100);
		imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
		imagecolordeallocate($image,$backColor);

		if($this->transparent)
			imagecolortransparent($image,$backColor);

		$foreColor = imagecolorallocate($image,
				(int)($this->foreColor % 0x1000000 / 0x10000),
				(int)($this->foreColor % 0x10000 / 0x100),
				$this->foreColor % 0x100);

		if($this->fontFile === null)
			$this->fontFile = dirname(__FILE__) . '/Duality.ttf';

		$length = strlen($code);
		$box = imagettfbbox(30,0,$this->fontFile,$code);
		$w = $box[4] - $box[0] + $this->offset * ($length - 1);
		$h = $box[1] - $box[5];
		$scale = min(($this->width - $this->padding * 2) / $w,($this->height - $this->padding * 2) / $h);
		$x = 10;
		$y = round($this->height * 27 / 40);
		for($i = 0; $i < $length; ++$i)
		{
			$fontSize = (int)(rand(26,32) * $scale * 0.8);
			$angle = rand(-10,10);
			$letter = $code[$i];
			$box = imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFile,$letter);
			$x = $box[2] + $this->offset;
		}

		imagecolordeallocate($image,$foreColor);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
	}

	/**
	 * Renders the CAPTCHA image based on the code using ImageMagick library.
	 * @param string $code the verification code
	 * @since 1.1.13
	 */
	// imagick渲染
	protected function renderImageImagick($code)
	{
		$backColor=new ImagickPixel('#'.dechex($this->backColor));
		$foreColor=new ImagickPixel('#'.dechex($this->foreColor));

		$image=new Imagick();
		$image->newImage($this->width,$this->height,$backColor);

		if($this->fontFile===null)
			$this->fontFile=dirname(__FILE__).'/Duality.ttf';

		$draw=new ImagickDraw();
		$draw->setFont($this->fontFile);
		$draw->setFontSize(30);
		$fontMetrics=$image->queryFontMetrics($draw,$code);

		$length=strlen($code);
		$w=(int)($fontMetrics['textWidth'])-8+$this->offset*($length-1);
		$h=(int)($fontMetrics['textHeight'])-8;
		$scale=min(($this->width-$this->padding*2)/$w,($this->height-$this->padding*2)/$h);
		$x=10;
		$y=round($this->height*27/40);
		for($i=0; $i<$length; ++$i)
		{
			$draw=new ImagickDraw();
			$draw->setFont($this->fontFile);
			$draw->setFontSize((int)(rand(26,32)*$scale*0.8));
			$draw->setFillColor($foreColor);
			$image->annotateImage($draw,$x,$y,rand(-10,10),$code[$i]);
			$fontMetrics=$image->queryFontMetrics($draw,$code[$i]);
			$x+=(int)($fontMetrics['textWidth'])+$this->offset;
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		$image->setImageFormat('png');
		echo $image;
	}
}