<?php
namespace Shamrock\Instance\Session;

class Session{

    /**
     * @var
     * session类型实例
     */
	protected $_sessionInstance;

    /**
     * @var
     * key加密解密需要
     */
    protected $_key;

    /**
     * @param $typeObject
     * 准备工作
     */
	public function __construct($config){
		if(isset($config['key'])){
            $this->_key = $config['key'];
		}
        if (isset($config['instance'])){
            $this->_sessionInstance = $config['instance'];
        }
        unset($config);
	}

    /**
     * @param $key
     * @return mixed
     * 获取sesion
     */
	public function get($key){
        $val = $this->_sessionInstance->get($key);
        $resultStr = $this->decrypt($val,$this->_key);
        if ($this->isJsonString($resultStr)){
            return json_decode($resultStr,true);
        }else{
			$data = unserialize($resultStr);
			if($data===false){
				return $resultStr;
			}else{
				return $data;
			}
        }
	}

    /**
     * @param $key
     * @param $val
     * @param $expire
     * @return mixed
     * 设置session
     */
	public function set($key,$val,$expire=true){
        if (is_array($val)){
            $val = json_encode($val);
        }
		if (is_object($val)){
            $val = serialize($val);
        }
        $val = $this->encrypt($val,$this->_key);
		return $this->_sessionInstance->set($key,$val,$expire);
	}

    /**
     * @return false|string
     * 获取sessionid
     */
    public function getSessionId(){
        return session_id();
    }

    /**
     * @return false|string
     * 获取sessionid
     */
    public function getSessionIdForFileType(){
        return session_id();
    }

    /**
     * @param $key
     * @return void
     * 移除session
     */
    public function remove($key){
        $this->_sessionInstance->remove($key);
    }

    /**
     * @param $dreverObject
     * @return void
     * 设置session类型
     */
	public function set_deiver($dreverObject){
		$this->_sessionInstance = $dreverObject;
	}

    /**
     * @param $data
     * @param $key
     * @return string
     * @throws \Exception
     * session加密
     */
	protected function encrypt($data, $key)
    {
        $iv = random_bytes(16); // AES block size in CBC mode
        // Encryption
        $ciphertext = openssl_encrypt(
            $data,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
        // Authentication
        $hmac = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        return $hmac . $iv . $ciphertext;
    }

    /**
     * @param $data
     * @param $key
     * @return false|string
     * sesssion解密
     */
	protected function decrypt($data, $key)
    {
        $hmac       = mb_substr($data, 0, 32, '8bit');
        $iv         = mb_substr($data, 32, 16, '8bit');
        $ciphertext = mb_substr($data, 48, null, '8bit');
        // Authentication
        $hmacNew = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        if (! hash_equals($hmac, $hmacNew)) {
            return null;
            //throw new \Exception("Authentication failed");
        }
        // Decrypt
        return openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * @param $stringData
     * @return bool
     * 是否json数组
     */
    private function isJsonString($stringData)
    {
        if (empty($stringData)) return false;
        try
        {
            //校验json格式
            json_decode($stringData, true);
            return JSON_ERROR_NONE === json_last_error();
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

}