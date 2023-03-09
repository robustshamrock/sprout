<?php
namespace Shamrock\Instance\Mvc\Cache\src;

class Cache{
    public $cacheType = '';

    /**
     * @param $cacheType
     * @param $config
     * 初始化cache工作
     */
	public function __construct($cacheType,$config= ['']){
		$this->cacheType = new $cacheType($config);
	}

    public function compareFileMd5($file_md5,$key){
        return $this->cacheType->compareFileMd5($file_md5,$key);
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return mixed
     * 设置cache值
     */
	public function set($key,$value,$expire=300){
        return $this->cacheType->set($key,$value,$expire);
	}

    /**
     * @param $key
     * @return void
     * 获取cache值
     */
    public function get($key){
        $cacheValue = $this->cacheType->get($key);
        return !empty($cacheValue)?$cacheValue:null;
    }

    /**
     * @param $key
     * @return mixed
     * 移除cache
     */
	public function remove($key){
        return $this->cacheType->remove($key);
    }

    /**
     * @param $key
     * @return void
     * 销毁cache
     */
    public function destory($key){
        return $this->cacheType->destory();
    }

    /**
     * @return void
     * 清除所有缓存
     */
    public function clear(){
        return $this->cacheType->clear();
    }

    public function batchGetFileMd5($path){
        return $this->cacheType->batchGetFileMd5($path);
    }

    public function compareBathFileMd5($fileName,$md5,$key){
        return $this->cacheType->compareBathFileMd5($fileName,$md5,$key);
    }
}