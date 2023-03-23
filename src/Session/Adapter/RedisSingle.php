<?php
namespace Shamrock\Instance\Session\Adapter;

class RedisSingle{

    /**
     * @var
     * redis 实例
     */
    protected $_redisInstance;

    public function __construct($redisInstance){
        $this->_redisInstance = $redisInstance;
    }

    /**
     * @return false|string
     * 获取sessionid
     */
    public function getSessionIdForFileType(){
        return session_id();
    }

    /**
     * @param $name
     * @return void
     * 获取值
     */
    public function get($name){
        return $this->_redisInstance->get($name);
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return void
     * 设置redis值
     */
    public function set($key,$value,$expire){
        $this->_redisInstance->set($key,$value,$expire);
    }

    /**
     * @param $key
     * @return void
     * 删除session
     */
    public function remove($key){
        $this->_redisInstance->remove($key);
    }
}