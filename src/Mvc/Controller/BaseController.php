<?php
namespace Shamrock\Instance\Mvc\Controller;
use Shamrock\Instance\Mvc\View\BaseView;
use Shamrock\Instance\Session\Session;
use Shamrock\Instance\Mvc\Request\Request;

class BaseController{
    /**
     * @var mixed
     * 控制器动作
     */
    protected $action;

    /**
     * @var mixed
     * 当前实例类
     */
    protected $class;

    /**
     * @var mixed
     * 容器变量
     */
    protected $container;

    /**
     * @var mixed
     * 配置
     */
    protected $config;

    /**
     * @var mixed
     * 框架版本号
     */
    protected $framework_version;

    /**
     * @var mixed
     * 系统配置
     */
    protected $sys_conf;

    /**
     * @var mixed
     * 域名
     */
    protected $base_domain;

    /**
     * @var mixed
     * 请求头
     */
    protected $base_scheme;

    /**
     * @var mixed
     * 请求路径
     */
    protected $base_path;

    /**
     * @var mixed
     * 请求参数
     */
    protected $base_query;

    /**
     * @var mixed
     * 网站根目录
     */
    protected $app_path;

    /**
     * @var mixed
     * 请求参数
     */
    protected $args;

    /**
     * @var string
     * 实例路径
     */
    protected $instancePath = '';

    /**
     * @var BaseView
     * 模板实例
     */
    protected $viewInstance;

    /**
     * 当前模块别名
     */
    protected $_current_module_alias;

    // 主题
    protected $_theme;

    /**
     * @var
     * 表单令牌
     */
    protected $_token;

    /**
     * @var
     * session实例
     */
    protected $_sessionInstance;

    /**
     * @var mixed
     * 当前链接
     */
    protected $_current_page_url;

    /**
     * @var
     * 缓存实例
     */
    protected $_cacheInstance;

    /**
     * @param $args
     * 准备工作
     */
    public function __construct($args){
        $this->action = $args['action'];
        $this->class = $args['class'];
        $this->container = $args['container'];
        $this->config = $args['config'];
        $this->framework_version = $args['framework_version'];
        $this->sys_conf = $args['sys_conf'];
        $this->base_domain = $args['base_domain'];
        $this->base_scheme = $args['base_scheme'];
        $this->base_path = $args['base_path'];
        $this->base_query = $args['base_query'];
        $this->app_path = $args['app_path'];
        $this->args = $args['args'];
        $this->_current_module_alias = $args['current_module_alias'];

        $this->_current_page_url = $args['current_page_url'];

        $this->instancePath = APP_PATH .'App'.DIRECTORY_SEPARATOR.ucfirst($args['currnt_app_name']).DIRECTORY_SEPARATOR.ucfirst($this->_current_module_alias)
            .DIRECTORY_SEPARATOR . 'src';

        $this->_theme = $args['theme'];
        $this->viewInstance = new BaseView(['class'=>$this->class,
            'instancePath'=>$this->instancePath,
            'theme'=>$args['theme'],
            'baes_url'=>$this->_current_page_url,
            'theme_url'=> $this->base_scheme . '://'.$this->base_domain . '/assets/' .$this->_current_module_alias.'/'.$this->_theme,
            ]);

        $this->loadSession();
        $this->loadCache();
        $this->assign('framework_versition',$this->framework_version);
    }

    /**
     * @return void
     * 载入session实例
     */
    public function loadSession(){
        session_start();
        $redisInstance = $this->getContainer('redis_cache');
        $session = new Session(['key'=>'123123fasdfsdsdf123123sdfasfasdfsdfasd',
            'instance'=> new \Shamrock\Instance\Session\Adapter\RedisSingle($redisInstance),
        ]);
        $this->_sessionInstance = $session;
    }

    /**
     * @return void
     * 加载缓存
     */
    public function loadCache(){
        $redisInstance = $this->getContainer('redis_cache');
        $this->_cacheInstance = $redisInstance;
    }

    /**
     * @param $url
     * @return string
     * 链接
     */
    public function Url($url){
        $url = trim($url,'/');
        return $this->base_scheme . '://'.$this->base_domain . '/' .$this->_current_module_alias.'/'.$url;
    }

    /**
     * @param $url
     * @return void
     * 跳转
     */
    public function redirect($url){
        $url = $this->Url($url);
        header('Location: '.$url);
    }

    /**
     * @param $url
     * @param $data
     * @return void
     * 跳转中转页
     */
    public function redirectPage($url,$data){
        
    }

    /**
     * @return mixed|null
     * 获取用户id
     */
    public function getUID(){
        $userArr = $this->getSession('user');
        if ($userArr['UID']){
            return $userArr['UID'];
        }else{
            return null;
        }
    }

    /**
     * @return void
     * 生成表单token
     */
    public function generateFormToken(){
        if (Request::isGet()){
            $token = \Shamrock\Instance\generateGuid('M'.date('Ymd'));
            $this->_token = $token;
            $this->assign('token',$token);
            $this->setSession('token',$token,1800);
        }
    }

    /**
     * @param $key
     * @param $val
     * @return void
     * 设置session
     */
    public function setSession($key,$val,$expire=14000){
        $sessionID = $this->_sessionInstance->getSessionIdForFileType();
        $this->_sessionInstance->set('session_'.$sessionID.$key,$val,$expire);
    }

    /**
     * @param $key
     * @return mixed
     * 获取session
     */
    public function getSession($key){
        $sessionID = $this->_sessionInstance->getSessionIdForFileType();
        return $this->_sessionInstance->get('session_'.$sessionID.$key);
    }

    /**
     * @param $type
     * @return void
     * 获取sessionid
     */
    public function getSessionId($type='file'){
        if($type='file') {
            return $this->_sessionInstance->getSessionIdForFileType();
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return void
     * 缓存方法
     */
    public function cache($key,$value='',$expire=true){
        if ($value){
            if($expire===true){
                $this->_cacheInstance->set($key,$value);
            }else{
                $this->_cacheInstance->set($key,$value,$expire);
            }
        }else{
            return $this->_cacheInstance->get($key);
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return void
     * 设置缓存
     */
    public function setCache($key,$value='',$expire=true)
    {
        if ($value) {
            if ($expire === true) {
                $this->_cacheInstance->set($key, $value);
            } else {
                $this->_cacheInstance->set($key, $value, $expire);
            }
        }
    }

    /**
     * @param $key
     * @return mixed
     * 获取缓存
     */
    public function getCache($key){
        return $this->_cacheInstance->get($key);
    }

    /**
     * @param $name
     * @return void
     * 获取某个容器
     */
    public function getContainer($name){
        return $this->container->get($name);
    }

    /**
     * @param $name
     * @param $args
     * @return void
     * 验证
     */
    public function validate($name,$args=[]){
        $name = trim($name,DIRECTORY_SEPARATOR);
        $validatePath = $this->instancePath.DIRECTORY_SEPARATOR.'Validate';
        if(!is_dir($validatePath)){
            mkdir($validatePath,0777,true);
        }

        if(strpos($name,'.php')){
            $path = $validatePath.DIRECTORY_SEPARATOR.$name;
        }else{
            $path = $validatePath.DIRECTORY_SEPARATOR.$name.'.php';
        }

        if(!is_file($path)){
            throw new \Exception("$path 不存在");
        }

        $post['post'] = Request::post();
        if(is_array($post)){
            extract($post);
        }

        $args['token'] = $this->_token;
        if(is_array($args)){
            extract($args);
        }

        //unset($post);
        //unset($args);
        return include $path;
    }

    /**
     * @param $name
     * @return void
     * 显示模板
     */
    public function render($name,$args=[]){
        $this->generateFormToken();
        $this->viewInstance->render($name,$args);
    }

    /**
     * @return void
     * 注入变量
     */
    public function assign($key,$value){
        $this->viewInstance->assign($key,$value);
    }

    /*
     * 获取媒体资源
     */
    public function assets($assets){
        $assets = ltrim('/',$assets);
        echo $this->base_scheme . '://'.$this->base_domain . '/assets/' .$this->_current_module_alias.'/'.$this->_theme.''.$assets;
    }

    /**
     * @return void
     * 返回jison数据
     */
    public function json($arr){
        if(!is_array($arr)){
            $rArr = [];
            $rArr['msg'] = $arr;
            $rArr['code'] = 1;
            $arr = $rArr;
            unset($rArr);
        }
        echo json_encode($arr);exit;
    }

    /**
     * @param $name
     * @param $args
     * @return null
     * 不存在的方法默认调用容器内的方法
     */
    public function __call($name,$args){
        return $this->getContainer($name);
    }

    /**
     * @return void
     * 收尾工作
     */
    public function __descruct(){
        unset($_POST);
    }
}
