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

        $viewPath = $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->class.DIRECTORY_SEPARATOR.$name;
        if(!is_dir( $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->class)){
            mkdir( $this->instancePath . DIRECTORY_SEPARATOR .'View'.DIRECTORY_SEPARATOR.$this->class,0777,true);
        }
        if(!is_file($viewPath)){
            throw new \Exception("模板：$viewPath 不存在！");
        }
        include $viewPath;
    }
}