<?php
namespace Shamrock\Instance\Session\Type;

class File{

    /**
     * @param $name
     * @return mixed|null
     * 获取session
     */
    public function get($name){
        if (isset($_SESSION[$name])){
            if (isset($_SESSION[$name]['expire']) && ($_SESSION[$name]['expire']===true || $_SESSION[$name]['expire']>=time())){
                return $_SESSION[$name]['value'];
            }
        }
        return null;
    }

    /**
     * @return false|string
     * 获取sessionid
     */
    public function getSessionId(){
        return session_id();
    }

    /**
     * @param $name
     * @param $value
     * @param $expire
     * @return void
     * 设置session
     */
    public function set($name,$value,$expire=9000){
        $_SESSION[$name]['value'] = $value;
        if ($expire===true){
            $_SESSION[$name]['expire'] = true;
        }else{
            $_SESSION[$name]['expire'] = time()+$expire;
        }
    }

    /**
     * @param $key
     * @return void
     * 删除session
     */
    public function remove($key){
        unset($_SESSION[$key]);
    }
}