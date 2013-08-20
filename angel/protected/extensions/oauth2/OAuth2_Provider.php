<?php

/**
 * oauth2 provider
 */
abstract class OAuth2_Provider
{

	CONST GET = 'GET';

	CONST POST = 'POST';

	public $state_key = 'OAUTH2_STATE_KEY';

	/**
	 * client_id
	 * @var string
	 */
	public $client_id;

	/**
	 * client_secret
	 * @var string
	 */
	public $client_secret;

	/**
	 * redirect_uri
	 * @var string
	 */
	public $redirect_uri;

	/**
	 * scope
	 * @var string
	 */
	public $scope;

	/**
	 * response_type
	 * @var string
	 */
	public $response_type = 'code';

	/**
	 * grant_type
	 * @var string
	 */
	public $grant_type = 'authorization_code';

	/**
	 * authorize_url
	 * @var string
	 */
	public $authorize_url;

	/**
	 * token_url
	 * @var string
	 */
	public $token_url;

	/**
	 * request method for get token
	 * @var string
	 */
	public $method = SELF::GET;

	/**
	 * authorize params for create authorize_url
	 * @var array
	 */
	public $authorize_params = array('client_id', 'redirect_uri', 'response_type', 'scope');

	/**
	 * token params for create access_tokenUrl, except param code
	 * @var array
	 */
	public $token_params = array('client_id', 'client_secret', 'redirect_uri', 'grant_type');

	/**
	 * other config
	 * @var array
	 */
	public $others = array();
	
	/**
	 * construct, you should use create method instead it
	 * @param array $config config for provider
	 */
	public function __construct($config)
	{
		foreach ($config as $k => $v)
		{
			$this->$k = $v;
		}

		if (empty($this->redirect_uri))
		{
			$this->redirect_uri = $this->autoRedirectUri();
		}
		else
		{
			$this->redirect_uri = Yii::app()->createAbsoluteUrl($this->redirect_uri);
		}

		$this->init();
	}

	/**
	 * init
	 */
	protected function init()
	{
		if (is_string($this->token_params))
		{
			$this->token_params = preg_split('//', $this->token_params, -1, PERG_SPLIT_NO_EMPTY);
		}

		if (is_string($this->token_params))
		{
			$this->token_params = preg_split('//', $this->token_params, -1, PERG_SPLIT_NO_EMPTY);
		}
	}

	/**
	 * redirect to authorize
	 */
	public function redirect()
	{
		Yii::app()->request->redirect($this->getAuthorizeUrl());
	}

	/**
	 * redirect to authorize
	 */
	public function authorize()
	{
		$this->redirect();
	}

	/**
	 * validate state
	 * @return boolean success will return true
	 */
	public function validateState($state)
	{
		return $state && $state === $this->sessionGet($this->state_key);
	}

	/**
	 * get token
	 * @param string $code code
	 * @return OAuth2_Token, if has error will throw exception
	 */
	public function getToken($code)
	{
		$params = $this->tokenParams($code);

		if(strtoupper($this->method) == self::POST)
		{
			$result = $this->sendRequest($this->token_url, $params, self::POST);
		}
		else
		{
			$result = $this->sendRequest($this->token_url, $params, self::GET);
		}

		if ( ! isset($result['access_token']))
		{
			throw new OAuth2_Exception($result);
		}

		$token                = new OAuth2_Token;
		$token->access_token  = $result['access_token'];
		$token->expires_in    = $result['expires_in'];
		$token->refresh_token = isset($result['refresh_token']) ? $result['refresh_token'] : NULL;
		$token->original      = $result;

		return $token;
	}

	abstract public function getUserInfo(OAuth2_Token $token);

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
	 * add session
	 * @param  string $key session key
	 * @param  mixed $value session value
	*/
	public function sessionAdd($key, $value)
	{
		Yii::app()->session->add($key, $value);
	}

	/**
	 * get session
	 * @param  string $key session key
	 * @return boolean mixed
	*/
	public function sessionGet($key)
	{
		return Yii::app()->session->get($key);
	}

	/**
	 * remove session
	 * @param  string $key session key
	 * @return boolean removed value, null if no such session variable
	*/
	public function sessionRemove($key)
	{
		return Yii::app()->session->remove($key);
	}

	/**
	 * return params for create authorize_url
	 * @return array params
	 */
	protected function authorizeParams()
	{
		$params = array();

		foreach ($this->authorize_params as $key)
		{
			if (isset($this->$key) && $this->$key)
			{
				$params[$key] = $this->$key;
			}
			elseif (isset($this->others[$key]))
			{
				$params[$key] = $this->others[$key];
			}
		}

		return $params;
	}

	/**
	 * return params for get access_token
	 * @return array params
	 */
	protected function tokenParams($code)
	{
		$params = array();

		foreach ($this->token_params as $key)
		{
			if (isset($this->$key) && $this->$key)
			{
				$params[$key] = $this->$key;
			}
			elseif (isset($this->others[$key]))
			{
				$params[$key] = $this->others[$key];
			}
		}

		if ($this->grant_type == 'refresh_token')
		{
			$params['refresh_token'] = $code;
		}
		else
		{
			$params['code'] = $code;
		}

		return $params;
	}

	/**
	 * generate a state code
	 * @return string state code
	 */
	protected function generatState()
	{
		$state = strtolower(md5(rand() . rand() . rand() . uniqid()));
		$this->sessionAdd($this->state_key, $state);
		return $state;
	}

	/**
	 * return authorize_url
	 * @return string authorize_url
	 */
	protected function getAuthorizeUrl()
	{
		$params = array_merge($this->authorizeParams(), array('state' => $this->generatState()));

		return $this->createUrl($this->authorize_url, $params);
	}

	/**
	 * create url
	 * @param  string $url url
	 * @param  array $params query params
	 * @return string url
	 */
	protected function createUrl($url, $params = array())
	{
		if ($params)
		{
			$url .= (strpos($url, '?') !== FALSE ? '&' : '?' ) . http_build_query($params);
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

		$url = $this->createUrl($url, $data);

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
			throw new OAuth2_Exception(array('code' => curl_errno($ch), 'message' => curl_error($ch)));
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
			return json_decode($matches[0], TRUE);
		}

		$data = array();
		parse_str($response, $data);
		return $data;
	}

	/**
	 * return provider name for this class
	 * @return string provider name
	 */
	protected function providerName()
	{
		return strtolower(str_replace('OAuth2_', '', get_class($this)));
	}

}