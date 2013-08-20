<?php

/*Yii::import('ext.soauth2.SOAuth2');
$config = array(
	'qq' => array(
		'clientId' => '217310',
		'clientSecret' => '52bf5a2d17777c4f4d61e648bc29cf7d',
		'redirectUri' => '',
	),
);
$provider = 'qq';
$soauth2 = SOAuth2::create($provider, $config[$provider]);
$accessToken = $soauth2->accessToken();
$userInfo = $soauth2->userInfo($accessToken['access_token']);*/

abstract class SOAuth2
{

	CONST STATEKEY = 'OAUTH2_STATE_KEY';

	CONST GET = 'GET';

	CONST POST = 'POST';

	CONST MAN = 1;

	CONST WOMEN = 2;

	CONST SECRET = 0;

	/**
	 * client_id
	 * @var string
	 */
	public $clientId;

	/**
	 * client_secret
	 * @var string
	 */
	public $clientSecret;

	/**
	 * redirect_uri
	 * @var string
	 */
	public $redirectUri;

	/**
	 * scope
	 * @var string
	 */
	public $scope;

	/**
	 * response_type
	 * @var string
	 */
	public $responseType = 'code';

	/**
	 * grant_type
	 * @var string
	 */
	public $grantType = 'authorization_code';

	/**
	 * url for authorize
	 * @var string
	 */
	public $authorizeUrl;

	/**
	 * url for get access_token
	 * @var string
	 */
	public $accessTokenUrl;

	/**
	 * request method for get access_token
	 * @var string
	 */
	public $requestType = SELF::GET;

	/**
	 * code
	 * @var string
	 */
	private $code;

	/**
	 * error message
	 * @var array
	 */
	private $_error = array();
	
	/**
	 * construct, you should use create method instead it
	 * @param array $config config for provider
	 */
	protected function __construct($config)
	{
		foreach ($config as $k => $v)
		{
			$this->$k = $v;
		}

		if (empty($this->redirectUri))
		{
			$this->redirectUri = $this->autoRedirectUri();
		}
		else
		{
			$this->redirectUri = Yii::app()->createAbsoluteUrl($this->redirectUri);
		}
	}

	/**
	 * create provider instance
	 * @param  string $provider provider name
	 * @param  array $config config for provider
	 * @return object provider instance
	 */
	public static function create($provider, $config)
	{
		$provider = strtolower($provider);
		$dir      = dirname(__FILE__);
		$class    = 'SOAuth2_'.ucfirst($provider);

		$classFile = $dir . DIRECTORY_SEPARATOR . 'providers' . DIRECTORY_SEPARATOR . $class . '.php';

		if ( ! is_file($classFile))
		{
			throw new CException('SOAuth2 provider ' . $provider . ' not found');
		}

		require_once $classFile;

		return new $class($config);
	}

	/**
	 * redirect to authorize_url
	 * @return void
	 */
	public function redirect()
	{
		Yii::app()->request->redirect($this->authorizeUrl());
	}

	/**
	 * get access_token array
	 * @param boolean $original whether return the original access_token array, default false
	 * @return mixed array or false
	 */
	public function accessToken()
	{
		$this->code = $this->getQuery('code');
		$state      = $this->getQuery('state');

		if ( ! $this->code)
		{
			$this->setError('code error');
			return FALSE;
		}
		elseif ( ! $state || $state !== Yii::app()->session->get(self::STATEKEY))
		{
			$this->setError('state error');
			return FALSE;
		}

		$params = $this->accessTokenParams();

		if(strtoupper($this->requestType) == self::POST)
		{
			$accessToken = $this->sendRequest($this->accessTokenUrl, $params, self::POST);
		}
		else
		{
			$accessToken = $this->sendRequest($this->accessTokenUrl, $params, self::GET);
		}

		if ( ! isset($accessToken['access_token']))
		{
			$this->setError($accessToken);
			return FALSE;
		}
		
		return array(
			'access_token' => $accessToken['access_token'],
			'expires_in' => $accessToken['expires_in'],
			'refresh_token' => isset($accessToken['refresh_token']) ? $accessToken['refresh_token'] : '',
			'scope' => isset($accessToken['scope']) ? $accessToken['scope'] : '',
		);

	}

	abstract public function userInfo($accessToken);

	/**
	 * return normalization user info, used in method normalizationUserInfo
	 * @param  string $id id
	 * @param  string $nickname nickname
	 * @param  integer $gender gender
	 * @param  string $avatar avatar
	 * @return array user info
	 */
	protected function returnUserInfo($id, $nickname, $gender, $avatar)
	{
		return array(
			'id' => $id,
			'nickname' => $nickname,
			'gender' => $gender,
			'avatar' => $avatar,
		);
	}

	/**
	 * return params for bulid authorize_url
	 * @return array params
	 */
	protected function authorizeParams()
	{
		$params = array(
			'client_id'     => $this->clientId,
			'redirect_uri'  => $this->redirectUri,
			'response_type' => $this->responseType,
		);

		if (isset($this->scope))
		{
			$params['scope'] = $this->scope;
		}

		return $params;
	}

	/**
	 * return params for get access_token
	 * @return array params
	 */
	protected function accessTokenParams()
	{
		return array(
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
			'redirect_uri'  => $this->redirectUri,
			'code'          => $this->code,
			'grant_type'    => $this->grantType,
		);
	}

	/**
	 * generate a state code
	 * @return string state code
	 */
	protected function generatState()
	{
		$state = strtolower(md5(rand() . rand() . rand() . uniqid()));
		Yii::app()->session->add(self::STATEKEY, $state);
		return $state;
	}

	/**
	 * return authorize_url
	 * @return string authorize_url
	 */
	protected function authorizeUrl()
	{
		$params = array_merge($this->authorizeParams(), array('state' => $this->generatState()));

		return $this->bulidUrl($this->authorizeUrl, $params);
	}

	/**
	 * return a default autoRedirect_uri
	 * @return string autoRedirect_uri
	 */
	protected function autoRedirectUri()  
	{
		return Yii::app()->createAbsoluteUrl(
			Yii::app()->controller->getUniqueId() . '/' . Yii::app()->controller->action->getId(),
			array('provider' => $this->providerName())
		);
	}

	/**
	 * bulid url
	 * @param  string $url url
	 * @param  array $params query params
	 * @return string url
	 */
	protected function bulidUrl($url, $params = array())
	{
		if ($params)
		{
			$url .= (strpos($this->authorizeUrl, '?') !== FALSE ? '&' : '?' ) . http_build_query($params);
		}

		return $url;
	}

	/**
	 * send http request
	 * @param  string $url url
	 * @param  array $data request data
	 * @param  string $method request method, self::GET or self::POST
	 * @param  array $headers http headers
	 * @return array response array
	 */
	protected function sendRequest($url, $data = array(), $method = self::GET, $headers = array())
	{
		$ch = curl_init();

		if($method == self::POST)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		$url = $this->bulidUrl($url, $data);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

		if ($headers)
		{
			$_headers = array();
			foreach ($headers as $k => $v)
			{
				$_headers[] = $k . ': ' . $v;
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
		}

		$response = curl_exec($ch);

		if(curl_errno($ch) > 0)
		{
			throw new CException(curl_error($ch) . "\n" . $url);
		}
		
		curl_close($ch);
		
		return $this->resolveResponse($response);
	}

	/**
	 * return response array
	 * @param  string $response response string
	 * @return array response array
	 */
	protected function resolveResponse($response)
	{
		if(strpos($response, '{') !== false)
		{
			preg_match('/{.*}/s', $response, $matches);
			return CJSON::decode($matches[0]);
		}

		$data = array();
		parse_str($response, $data);
		return $data;
	}

	/**
	 * set a error
	 * @param string $error error message
	 */
	protected function setError($error)
	{
		$this->_error = $error;
	}

	/**
	 * get error
	 * @return mixed error message string or null
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * return provider name for this class
	 * @return string provider name
	 */
	protected function providerName()
	{
		return strtolower(str_replace('SOAuth2_', '', get_class($this)));
	}

	/**
	 * returns the named GET parameter value
	 * @param  string $name parameter name
	 * @return string parameter value
	 */
    protected function getQuery($name)
    {
        return isset($_GET[$name]) && is_string($_GET[$name]) ? $_GET[$name] : NULL;
    }

}