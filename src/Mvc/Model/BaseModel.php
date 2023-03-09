<?php
namespace Shamrock\Instance\Mvc\Model;

class BaseModel{

    /**
     * @var \MysqliDb
     * db实例
     */
    protected $_dbInstance;

    /**
     * @param $config
     * 准备工作
     */
    public function __construct($config){
        if (isset($config['mysql_single'])){
            if(!isset($config['mysql_single']['host'])){
                $config['mysql_single']['host'] = '127.0.0.1';
            }
            if(!isset($config['mysql_single']['user'])){
                $config['mysql_single']['user'] = 'root';
            }
            if(!isset($config['mysql_single']['port'])){
                $config['mysql_single']['port'] = '3306';
            }
            if(!isset($config['mysql_single']['password'])){
                $config['mysql_single']['password'] = '123456';
            }
            if(!isset($config['mysql_single']['dbname'])){
                $config['mysql_single']['dbname'] = 'shamrock.com';
            }
            if(!isset($config['mysql_single']['table_prefix'])){
                $config['mysql_single']['table_prefix'] = 'shamrock';
            }

            require_once (APP_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'thingengineer'.DIRECTORY_SEPARATOR.'mysqli-database-class'. DIRECTORY_SEPARATOR .'MysqliDb.php');
            $this->_dbInstance = new \MysqliDb ($config['mysql_single']['host'], $config['mysql_single']['user'], $config['mysql_single']['password'], $config['mysql_single']['dbname'], $config['mysql_single']['port']);
            $this->_dbInstance->setPrefix ($config['mysql_single']['table_prefix']);
        }
    }

    /**
     * @return \MysqliDb
     * db方法
     */
    public function db(){
        return $this->_dbInstance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return null
     * 不存在的方法
     */
    public function __call($name, $arguments)
    {
        if (function_exists($name)){
            return $this->_dbInstance->$name($arguments);
        }else{
            return null;
        }
    }
}