<?php
namespace Shamrock\Instance\Mvc\Cache\src\CacheType;

class FileSystemV2Config{

    private $ext = '.php';
    private $cacheDir = '';
    public $cofnig = [];
    public function __construct($config=[]){
           $this->cofnig = $config;
           $this->cacheDir = $config['cacheDir'];
           if(!is_dir($this->cacheDir)){
               mkdir($this->cacheDir,0777,true);
           }
           if(isset($config['ext'])){
               $this->ext = $config['ext'];
           }
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return false|int
     * 设置文件缓存
     */
    public function set($key,$value,$expire){
        if(!is_array($value)){
            $putValue = ['value'=>$value,'expire'=>$expire+time()];
        }else{
            $putValue['value'] = $value;
            if(!isset($value['expire'])){
                $putValue['expire'] = time()+$expire;
            }
        }
        if ($expire===true){
            $putValue['expire'] = true;
        }

        $cacheName = $this->getName($key,true);

        return file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.$cacheName.$this->ext,"<?php  \n return ".'$returnValue='.var_export($putValue,true).';');
    }

    /**
     * @param $key
     * @return void
     * 获取文件缓存
     */
    public function get($key){
        //文件是否存在
        $cacheName = $this->getName($key);

        $targetFile = $this->cacheDir.DIRECTORY_SEPARATOR.$cacheName.$this->ext;

        if(is_file($targetFile)){
            $values = include ($targetFile);
        }else{
            return null;
        }

        if(isset($values['expire'])){
            // 判断是否过期
            if($values['expire']===true){
                return $values['value'];
            }elseif($values['expire']>=time()){
                return $values['value'];
            }else{
                return null;
            }
        }
    }

    /**
     * @param $key
     * @return void
     * 获取名称
     */
    private function getName($key,$isAutoGeFolder=false){
        $fileName = $key;
        $processName = '';
        if(strpos($key,'/')) {
            $targetName = explode('/', $key);
            if (is_array($targetName)) {
                if (isset($targetName[count($targetName) - 1])) {
                    $processName = $targetName[count($targetName) - 1];
                    unset($targetName[count($targetName) - 1]);
                }
                if($isAutoGeFolder===true&&!is_dir($this->cacheDir.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $targetName))){
                    mkdir($this->cacheDir.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $targetName),0777,true);
                }
                $fileName = implode(DIRECTORY_SEPARATOR, $targetName) . DIRECTORY_SEPARATOR.$processName;
            }
        }
        return $fileName;
    }

    /**
     * @return true
     * 清除所有缓存
     */
    public function clear(){
        $files = $this->scanFile($this->cacheDir.DIRECTORY_SEPARATOR);

        if(is_array($files)){
            if(isset($files['file'])){
                $this->delFile($files['file']);
            }
            if(isset($files['path'])){
                $files['path'] = array_reverse($files['path']);
                $this->delFile($files['path']);
            }
            rmdir($this->cacheDir);
        }
        return true;
    }

    /**
     * @param $path
     * @return mixed
     * 搜索目录下所有文件
     */
    protected function scanFile($path) {
        global $result;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $result['path'][] =$path . '/' . $file;
                        $this->scanFile($path . '/' . $file);
                } else {
                    $result['file'][] = $path.'/'.basename($file);
                }
            }
        }
        return $result;
    }

    /**
     * @param $key
     * @return true
     * 删除缓存
     */
    public function remove($key){
        $cacheName = $this->getName($key);
        $targetFile = $this->cacheDir.DIRECTORY_SEPARATOR.$cacheName.$this->ext;
        if($targetFile){
            $this->delFile($targetFile);
        }
        return true;
    }

    /**
     * @param $file
     * @return void
     * 删除文件
     */
    public function delFile($file){
        if(is_array($file)){
            foreach ($file as $fileVal){
                if(is_dir($fileVal)){
                    rmdir($fileVal);
                }else{
                    unlink($fileVal);
                }
            }
        }else{
            if(is_dir($file)){
                rmdir($file);
            }else{
                unlink($file);
            }
        }
    }

    /**
     * @param $path
     * @return mixed
     * 搜索目录下所有文件
     */
    public function batchGetFileMd5($path) {
        global $result;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $result['path'][] =$path . '/' . $file;
                    $this->batchGetFileMd5($path . '/' . $file);
                } else {
                    $result['file'][$path.'/'.basename($file)] = md5_file($path.'/'.basename($file));
                }
            }
        }
        return $result;
    }

}