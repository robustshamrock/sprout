<?php

namespace Shamrock\Instance\Mvc;

use function Shamrock\Instance\HelperLoad;
use const http\Client\Curl\Features\UNIX_SOCKETS;

class RootSegment
{

    // 配置实例
    private $__configInstance;

    // 请求类名称
    private $__currentClassName = '';

    // 请求方法名称
    private $__currentFunctionName = '';

    // 容器
    private $__container;

    // 当前app名称
    protected $currentAppName;

    // app 设置
    protected $appConfig = [];

    // 当前模块别名
    protected $_currentModuleAlias = '';

    // 初始化
    public function __construct($config=''){
        if (is_array($config)){
            $this->appConfig['sys_conf'] = $config;
        }else{
            $this->appConfig['sys_conf'] = null;
        }
    }

    public function germinate(){

        // 载入全局函数
        $this->__loadGlobalFunctions();

        // 获取应用名称
        $this->__getApp();

        // 载入容器
        $this->__setContainer();

        // 载入配置
        $this->__registerConfig();

        // 环境设置
        $this->__prepareEnv();

        // 载入路由
        $this->__route();
    }

    /**
     * @return void
     * 下面是功能区
     */

    /**
     * @return void
     * 准备环境
     */
    protected function __prepareEnv(){

        // app debug
        $envArr = [];

        $redisCacheInstance = $this->__getContainer('redis_cache');

        $envFile = APP_PATH . 'env.txt';
        if(!is_file($envFile)) {
            $fileMd5 = 0;
        }else{
            $fileMd5 = md5_file($envFile);
            if($redisCacheInstance->compareFileMd5($fileMd5,'RootSegment/__prepareEnv')){
                $envArr = $redisCacheInstance->get('RootSegment/__prepareEnv');
            }else{
                $envArr = \Shamrock\Instance\ReadLineByLineToArray($envFile);
                $envArr['file_md5'] = $fileMd5;
                $redisCacheInstance->set('RootSegment/__prepareEnv',$envArr);
            }
        }

        // 未定义设置
        if(!is_array($envArr)){
            $envArr = [];
        }

        if(!isset($envArr['app_debug'])){
            $envArr['app_debug'] = false;
        }

        if(!isset($envArr['time_zone'])){
            $envArr['time_zone'] = 'PRC';
        }

        if(!isset($envArr['version'])){
            $envArr['version'] = 'Unknown Version!';
        }

        // 设置报错级别
        $this->__setErrorRepoting(boolval($envArr['app_debug']));

        // 设置时区
        $this->__setDateDefaultTimezone($envArr['time_zone']);

        // 设置版本号
        $this->__setVersion($envArr['version']);

        // 删除全局变量
        $this->__unregisterGlobals();
    }

    /**
     * @return void
     * 载入公用函数
     */
    private function __loadGlobalFunctions(){
        $filePath = dirname(__DIR__).DIRECTORY_SEPARATOR.'Functions.php';
        if(is_file($filePath)){
            include $filePath;
        }
    }

    /**
     *
     */

    /**
     * @return void
     * 获取应用名称
     */
    private function __getApp(){
        $pateUrl = \Shamrock\Instance\GetPageurl();
        $parseUrlArr = parse_url($pateUrl);

        $appName = 'Home';
        if(isset($parseUrlArr['path'])){
            $processUrl = trim($parseUrlArr['path'],'/');
            if(strpos($processUrl,'/')!==false){
                $processUrl = explode('/',$processUrl);
                if(isset($processUrl[0])){
                    $appName = trim($processUrl[0]);
                }
            }else{
                if($processUrl!=''){
                    $appName = trim($processUrl);
                    if (strpos($processUrl,'/')==false&&strpos($processUrl,'.')){
                        $appName = $processUrl;
                    }
                }else{
                    $appName = 'home';
                }
            }
        }
 
        // 赋值
        $this->_currentModuleAlias = $appName;

        // 链接识别
        $targetAppName = 'Unknown App Name';
        // 在app注册表中查找应用
        $appRegisterConfigPath = APP_PATH.DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'App.php';
        $appRegisterConfigArr = \Shamrock\Instance\HelperLoadConfig($appRegisterConfigPath);

        if(is_array($appRegisterConfigArr)){
            foreach ($appRegisterConfigArr['module'] as $keyAppName=>$val){
                if(is_array($val['module_alias'])){
                    if(in_array($appName, $val['module_alias'])){
                        $targetAppName = $keyAppName;
                    }
                }else{
                    if(!empty($val['module_alias'])){
                        if($val['module_alias']==$appName){
                            $targetAppName = $keyAppName;
                        }
                    }else{
                        throw new \Exception("app 注册表缺失,清配置app注册表:/Config/App.php>>>".$keyAppName.'配置');
                    }
                }
            }

            // 未找到应用配置
            if($targetAppName=='Unknown App Name'){
                throw new \Exception("未找到应用配置，清配置app注册表:/Config/App.php>>>配置");
            }else{
                $this->__appRegisterConfigArr = $appRegisterConfigArr;
                $this->currentAppName = $targetAppName;
            }
        }else{
            // 查找所有config
            $path = APP_PATH.'App';
            $configs = \Shamrock\Instance\HelperBathLoadConfigs($path,'Config');
            foreach ($configs as $confKey=>$confVal){
                $meargeConfigs = \Shamrock\Instance\MeargeConfigs($confVal);
                if(is_array($meargeConfigs['module'])){
                    if(in_array($appName, $meargeConfigs['module']['module_alias'])){
                        $targetAppName = strtolower($confKey);
                        $this->currentAppName = $targetAppName;
                        $this->__appRegisterConfigArr = $meargeConfigs;
                        unset($meargeConfigs);

                    }
                }
            }

            unset($configs);
            if($targetAppName=='Unknown App Name'){
                throw new \Exception("app 注册表缺失,清配置app注册表:/Config/App.php");
            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     * 注册配置
     */
    private function __registerConfig(){
        $targetAppName = $this->currentAppName;
        $appRegisterConfigArr = $this->__appRegisterConfigArr;
        if(isset($appRegisterConfigArr['module'][$targetAppName]['route'])&&!empty($appRegisterConfigArr['module'][$targetAppName]['route'])){
            $this->appConfig['route'] = $appRegisterConfigArr['module'][$targetAppName]['route'];
            unset($appRegisterConfigArr['module'][$targetAppName]['route']);
            $this->appConfig['conf'] = $appRegisterConfigArr['module'][$targetAppName];

            $path = APP_PATH.'App'.DIRECTORY_SEPARATOR.$targetAppName.DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Config';

            $redisCacheInstance = $this->__getContainer('redis_cache');
            $batchArr = $batchGetFileMd5Arr = $this->__configInstance->batchGetFileMd5($path);
            foreach ($batchGetFileMd5Arr['file'] as $md5filePath=>$md5Val){
                if($redisCacheInstance->compareBathFileMd5($md5filePath,$md5Val,'RootSegment/__registerConfig')){
                    unset($batchGetFileMd5Arr['file'][$md5filePath]);
                }
            }

            // 触发缓存$batchGetFileMd5Arr
            if (count($batchGetFileMd5Arr['file'])>0){
                $redisCacheInstance->set('RootSegment/__registerConfig',$batchArr);
                $configs = \Shamrock\Instance\HelperLoadConfigs($path);
                $redisCacheInstance->set('RootSegment/__registerConfig/batchGetFileMd5Arr',$configs);
            }else{
                $configs = $redisCacheInstance->get('RootSegment/__registerConfig/batchGetFileMd5Arr');
            }

            $keys = array_keys($configs);
            natcasesort($keys);
            $newArr = [];
            foreach ($keys as $fileName){
                foreach ($configs as $key=>$val){
                    if ($fileName==$key){
                        $newArr[$key] = $val;
                    }
                }
            }

            $combineConfigs = \Shamrock\Instance\MeargeConfigs($newArr);
            if(isset($combineConfigs['route'])&&!empty($combineConfigs['route'])){
                $this->appConfig['route'] = $combineConfigs['route'];
                unset($configs['app']['route']);
                $this->appConfig['conf'] = $configs;

                unset($combineConfigs);
                unset($configs);
            }

            unset($appRegisterConfigArr);
            unset($configs);
            unset($this->__appRegisterConfigArr);
        }else{
            // 查找专用配置
            $path = APP_PATH.'App'.DIRECTORY_SEPARATOR.$targetAppName.DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Config';

            $redisCacheInstance = $this->__getContainer('redis_cache');
            $batchArr = $batchGetFileMd5Arr = $this->__configInstance->batchGetFileMd5($path);
            foreach ($batchGetFileMd5Arr['file'] as $md5filePath=>$md5Val){
                if($redisCacheInstance->compareBathFileMd5($md5filePath,$md5Val,'RootSegment/__registerConfig')){
                    unset($batchGetFileMd5Arr['file'][$md5filePath]);
                }
            }

            // 触发缓存$batchGetFileMd5Arr
            if (count($batchGetFileMd5Arr['file'])>0){
                $redisCacheInstance->set('RootSegment/__registerConfig',$batchArr);
                $configs = \Shamrock\Instance\HelperLoadConfigs($path);
                $redisCacheInstance->set('RootSegment/__registerConfig/batchGetFileMd5Arr',$configs);
            }else{
                $configs = $redisCacheInstance->get('RootSegment/__registerConfig/batchGetFileMd5Arr');
            }

            $keys = array_keys($configs);
            natcasesort($keys);
            $newArr = [];
            foreach ($keys as $fileName){
                foreach ($configs as $key=>$val){
                    if ($fileName==$key){
                        $newArr[$key] = $val;
                    }
                }
            }

            $combineConfigs = \Shamrock\Instance\MeargeConfigs($newArr);
            if(isset($combineConfigs['route'])&&!empty($combineConfigs['route'])){
                $this->appConfig['route'] = $combineConfigs['route'];
                unset($configs['app']['route']);
                $this->appConfig['conf'] = $configs;

                unset($combineConfigs);
                unset($this->__appRegisterConfigArr);
                unset($configs);
            }else{
                throw new \Exception("未找到应用配置，清配置app注册表:/Config/App.php>>>配置");
            }
        }
    }

    /**
     * @param $name
     * @return null
     * 获取某个容器
     */
    protected function __getContainer($name=''){
        if ($name){
            if (!empty($this->__container) && method_exists($this->__container,'get')){
                return $this->__container->get($name);
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    /**
     * @return void
     * 路由组件
     */
    private function __route(){

        if(!isset($this->appConfig['route'])){
            throw new \Exception("app 注册表缺失,清配置App.php->app 路由表:在route字段");
        }

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = unserialize($this->appConfig['route'])->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                header('Location:/404/index.html');
                //include APP_PATH.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."404".DIRECTORY_SEPARATOR."index.html";
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo 'METHOD_NOT_ALLOWED';
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $className = '';
                $functionName = '';
                if(strpos($handler,'@')!==false){
                    $processHandlerArr = explode('@',$handler);
                    if (isset($processHandlerArr[0]) && isset($processHandlerArr[1])){
                        $processHandlerArr[1] = preg_replace('/\s+/', '', $processHandlerArr[1]);
                        if ($processHandlerArr[1]==''){
                            $processHandlerArr[1] = 'home';
                        }
                        $functionName = trim($processHandlerArr[1]);
                        $className = $processHandlerArr[0];
                    }
                }else{
                    $functionName = 'home';
                    $className = $handler;
                }

                // 绝对路径和相对路径处理
                if(strpos($className,'\\')===0){
                    // 绝对路径类
                    $appAbsolutePath = $className;
                }else{
                    $appAbsolutePath = '\\App\\'.ucfirst($this->currentAppName).'\\'.ucfirst($this->_currentModuleAlias).'\\src\\Controller\\'.$className;
                }

                // 类是否存在
                if(!class_exists($appAbsolutePath)){
                    throw new \Exception("$appAbsolutePath 不存在，清创建！");
                }

                $parseUrlArr = parse_url(\Shamrock\Instance\GetPageurl());

                // 空值判断
                if(!isset($parseUrlArr['query'])){
                    $parseUrlArr['query'] = '';
                }

                $theme = 'default';
                if (isset($this->appConfig['conf']['app']['theme_name'])){
                    $theme = $this->appConfig['conf']['app']['theme_name'];
                }

                $appRegisterMap = [
                    'action'=>$functionName,
                    'class'=>$className,
                    'currnt_app_name'=>$this->currentAppName,
                    'container'=>$this->__container,
                    'config'=> $this->appConfig['conf'],
                    'framework_version'=>$this->__getVersion(),
                    'sys_conf'=>$this->appConfig['sys_conf'],

                    'base_domain'=>$parseUrlArr['host'],
                    'base_scheme'=>$parseUrlArr['scheme'],
                    'base_path'=>$parseUrlArr['path'],
                    'base_query'=>$parseUrlArr['query'],
                    'current_module_alias'=>$this->_currentModuleAlias,
                    'current_page_url'=>\Shamrock\Instance\GetPageurl(),
                    'theme'=>$theme,
                    'app_path'=>str_replace('/',DIRECTORY_SEPARATOR,APP_PATH),
                    'args'=>$vars,
                ];

                $this->__currentClassName = $className;
                $this->__currentFunctionName = $functionName;

                $instance = new $appAbsolutePath($appRegisterMap);

                // 清空容器
                $this->__clearContainer();

                // 配置实例清空
                $this->__configInstance = null;

                // 销毁变量
                $this->appConfig['conf'] = null;

                // 方法是否存在
                if(!method_exists($instance,$functionName)){
                    throw new \Exception("$appAbsolutePath 方法：$functionName 不存在，清创建！");
                }

                $this->__routeBefore();

                $instance->$functionName();

                $this->__routeAfter();
                // ... call $handler with $vars
                break;
        }
    }

    /**
     * @return void
     * 路由前置操作
     */
    private function __routeBefore(){
        $file = 'RouteBefore.php';
        if(isset($this->appConfig['conf']['before']) && !empty($this->appConfig['conf']['before'])){
            $file = $this->appConfig['conf']['before'];
        }

        // 绝对路径
        $path = APP_PATH . 'App'.DIRECTORY_SEPARATOR . ucfirst($this->currentAppName) .DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.$file;
        if (is_file($path)){
            \Shamrock\Instance\HelperLoad($path);
        }
    }

    private function __routeAfter(){
        $file = 'RouteAfter.php';
        if(isset($this->appConfig['conf']['after']) && !empty($this->appConfig['conf']['after'])){
            $file = $this->appConfig['conf']['after'];
        }

        // 绝对路径
        $path = APP_PATH . 'App'.DIRECTORY_SEPARATOR . ucfirst($this->currentAppName) .DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.$file;
        if (is_file($path)){
            \Shamrock\Instance\HelperLoad($path);
        }
    }

    /**
     * @param $args
     * @return void
     * 测试方法
     */
    public function see($args){
        echo $args;
    }

    /**
     * @return void
     * 依赖注入组件
     */
    private function __setContainer(){
        $path = APP_PATH.'App'.DIRECTORY_SEPARATOR.$this->currentAppName.DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.'ContainerInstance.php';
        if(is_file($path)){
            $this->__configInstance = $configInstance = new \Shamrock\Instance\Mvc\Cache\src\CaChe('\Shamrock\Instance\Mvc\Cache\src\CacheType\FileSystemV2Config',[
                'cacheDir'=>APP_PATH.'App'.DIRECTORY_SEPARATOR.ucfirst($this->currentAppName).DIRECTORY_SEPARATOR.ucfirst($this->_currentModuleAlias).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Config'
            ]);
            $containerObject = include $path;
            $this->__container = $containerObject;
        }
    }

    /**
     * @return void
     * 清除容器
     */
    private function __clearContainer(){
        $this->__container = null;
    }

    /**
     * -- 设置类 --
     */

    /**
     * @return void
     * 设置错误
     */
    private function __setErrorRepoting($isShowError){
        if($isShowError===true){
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        }else{
            error_reporting(0);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'Off');
        }
    }

    /**
     * @param $timezone
     * @return void
     * 设置时区
     */
    private function __setDateDefaultTimezone($timezone){
        date_default_timezone_set($timezone);
    }

    /**
     * @param $version
     * @return void
     * 设置版本号
     */
    protected function __setVersion($version){
        define('VERSION',$version);
        return $version;
    }

    /**
     * @return mixed
     * 获得版本号
     */
    protected function __getVersion(){
        return VERSION;
    }

    /**
     *   -- 操作类 --
     */

    /**
     * 删除全局变量
     */
    protected function __unregisterGlobals(){
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    public function __destruct(){
        unset($_POST);
    }

}