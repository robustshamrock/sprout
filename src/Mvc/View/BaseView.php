<?php
namespace Shamrock\Instance\Mvc\View;

class BaseView{

    /**
     * @var mixed
     * 实例绝对路径
     */
    protected $instancePath;

    /**
     * @var
     * 模板变量数组
     */
    protected $viewArgs;

    /**
     * @var mixed
     * 当前所在控制器类
     */
    protected $class;

    /**
     * @var mixed
     * 模板名称
     */
    protected $theme;

    /**
     * @var mixed
     * 媒体链接
     */
    protected $_assetsUrl;

    /**
     * @var mixed
     * 当前页链接
     */
    protected $_baes_url;

    /**
     * @param $config
     * 准备工作
     */
    public function __construct($config){
        if (isset($config['instancePath'])){
            $this->instancePath = $config['instancePath'];
        }
        if (isset($config['class'])){
            $this->class = $config['class'];
        }
        if (isset($config['theme'])){
            $this->theme = $config['theme'];
        }
        if(isset($config['theme_url'])){
            $this->_assetsUrl = $config['theme_url'];
        }
        if(isset($config['theme_url'])){
            $this->_baes_url = $config['baes_url'];
        }
    }

    /**
     * @param $key
     * @param $val
     * @return void
     * 注入模板变量
     */
    public function assign($key,$val){
        $this->viewArgs[$key] = $val;
    }

    /**
     * @param $url
     * @return void
     * 媒体
     */
    public function assets($url){
        if (strpos($url,'/')===0){
            echo $this->_assetsUrl.$url;
        }else{
            echo $this->_assetsUrl.'/'.$url;
        }
    }

    public function getCurrentPageUrl(){
        echo $this->_baes_url;
    }

    /**
     * @param $name
     * @param $args
     * @return void
     * @throws \Exception
     * 显示模板
     */
    public function render($name,$args=[]){
        if (is_array($this->viewArgs)){
            extract($this->viewArgs);
        }

        if (is_array($args)){
            extract($args);
        }

        $viewPath = $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->class.DIRECTORY_SEPARATOR.$name;
        if(!is_dir( $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->class)){
            mkdir( $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->class,0777,true);
        }
        if(!is_file($viewPath)){
            throw new \Exception("模板：$viewPath 不存在！");
        }
        include $viewPath;
    }
}