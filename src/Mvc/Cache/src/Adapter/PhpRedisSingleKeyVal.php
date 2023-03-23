<?php
namespace Shamrock\Instance\Mvc\Cache\src\Adapter;

class PhpRedisSingleKeyVal{
    private $redis = null;

    /**
     * @param $config
     * @throws \RedisException
     * 初始化
     */
    public function __construct($config){

        $db = 0;
        $port = 6379;
        $host = '127.0.0.1';
        if(isset($config['port'])){
            $port = $config['port'];
        }
        if(isset($config['host'])){
            $host = $config['host'];
        }
        if(isset($config['db'])){
            $db = $config['db'];
        }

        $this->redis = new \Redis();
        $this->redis->connect($host,$port,1);
        if(isset($config['password'])){
            $this->redis->auth($config['password']);
        }
        $this->redis->select($db);
    }

    /**
     * @param $key
     * @param $val
     * @param $expire
     * @return void
     * @throws \RedisException
     * 设置缓存
     */
    public function set($key,$val,$expire=true){
        if(is_array($val)){
            $val = json_encode($val);
        }
        if ($expire!==true){
            $this->redis->set($key,$val,$expire);
        }else{
            $this->redis->set($key,$val);
        }
    }

    /**
     * @param $key
     * @return mixed|\Redis|string|null
     * @throws \RedisException
     * 获取缓存
     */
    public function get($key){
        $val = $this->redis->get($key);
        if($val){
            if($this->isJsonString($val)){
                return json_decode($val,true);
            }else{
                return $val;
            }
        }else{
            return null;
        }
    }

    /**
     * @param $md5
     * @param $key
     * @return void
     * 比较md5和缓存中md5
     */
    public function compareFileMd5($md5,$key){
        $val = $this->get($key);
        if(isset($val['file_md5'])&&$md5==$val['file_md5']){
            return true;
        }
        return false;
    }

    public function compareBathFileMd5($fileName,$md5,$key){
        $batchArr = $this->get($key);
        if (isset($batchArr['file'])){
            foreach ($batchArr['file'] as $md5FileName=>$val){
                if($fileName==$md5FileName && $val==$md5){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $key
     * @return false|int|\Redis
     * @throws \RedisException
     * 删除缓存
     */
    public function remove($key){
        return $this->redis->del($key);
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