<?php
namespace Shamrock\Instance\Mvc\Controller;
use Shamrock\Instance\Mvc\View\BaseView;

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

        $this->instancePath = APP_PATH .'App'.DIRECTORY_SEPARATOR.ucfirst($args['currnt_app_name']).
            DIRECTORY_SEPARATOR . 'src';

        $this->viewInstance = new BaseView(['class'=>$this->class,
            'instancePath'=>$this->instancePath,
            ]);
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
    public function validate($name,$args){
        if (!strpos($name,DIRECTORY_SEPARATOR)){
            throw new \Exception("清添加路径/");
        }

        $validatePath = $this->instancePath.DIRECTORY_SEPARATOR.'Validate';
        if(!is_dir($validatePath)){
            mkdir($validatePath,0777,true);
        }

        if(!is_file($validatePath.DIRECTORY_SEPARATOR.$name.'.php')){
            throw new \Exception("$validatePath ".DIRECTORY_SEPARATOR." $name.php 不存在");
        }

        if(is_array($args)){
            $args = extract($args);
        }

        include $validatePath.DIRECTORY_SEPARATOR.$name.'.php';
    }

    /**
     * @param $name
     * @return void
     * 显示模板
     */
    public function render($name,$args=[]){
        $this->viewInstance->render($name,$args);
    }

    /**
     * @return void
     * 注入变量
     */
    public function assign($key,$value){
        $this->viewInstance->assign($key,$value);
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
        echo json_encode($arr);
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
