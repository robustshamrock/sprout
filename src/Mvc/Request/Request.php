<?php
namespace Shamrock\Instance\Mvc\Request;

class Request{

    /**
     * @param $name
     * @return mixed
     * 获取get 请求参数
     */
    public static function get($name=''){
        return self::getInput($name);
    }

    /**
     * @param $name
     * @return array
     * get 方式提交的数据
     */
    public static function getInput($name=''){
        $currentUrl = \Shamrock\Instance\GetPageurl();
        if (strpos($currentUrl,'?')==false){
            return [];
        }
        $currentUrlStr = mb_substr($currentUrl,stripos($currentUrl,"?")+1);
        parse_str($currentUrlStr, $getParams);
        if (isset($getParams[$name])){
            return $getParams[$name];
        }else{
            return $getParams;
        }
    }

    /**
     * @param $name
     * @return mixed|null
     * post输入
     */
    public static function postInput($name = ''){
        if(isset($_POST[$name])){
            return $_POST[$name];
        }else{
            return $_POST;
        }
    }

    /**
     * @return mixed
     * 是否手机访问
     */
    public static function isMobile(){
        $mobileObject = new \Shamrock\Instance\Mvc\Request\IsMobile();
        return $mobileObject->CheckMobile();
    }

    /**
     * @param $name
     * @return mixed
     * post提交数据
     */
    public static function post($name=''){
        return self::postInput($name);
    }

    /**
     * @return bool
     * 是否https
     */
    public static function isHttps(){
        if(!isset($_SERVER['HTTPS']))
            return FALSE;
        if($_SERVER['HTTPS']===1){  //Apache
            return TRUE;
        }elseif($_SERVER['HTTPS']==='on'){ //IIS
            return TRUE;
        }elseif($_SERVER['SERVER_PORT']==443){ //其他
            return TRUE;
        }
        return FALSE;
    }

    // 获取ip
    public static function getRealIp(){
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                foreach ($arr as $ip) {
                    $ip = trim($ip);

                    if ($ip != 'unknown') {
                        $realip = $ip;
                        break;
                    }
                }
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }

        preg_match('/[\\d\\.]{7,15}/', $realip, $onlineip);
        $realip = (!empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0');
        return $realip;
    }

    // 获取当前完整链接
    public static function getCurrentUrl(){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ?"https://": "http://";
        return self::$currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    // 获取域名
    public static function getDomain(){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ?"https://": "http://";
        return $protocol . $_SERVER['HTTP_HOST'];
    }

    // 获取请求头
    public static function getScheme(){
        self::getCurrentUrl();
        $parseUrlArr = parse_url(self::$currentUrl);
        return isset($parseUrlArr['scheme'])?$parseUrlArr['scheme']:'';
    }

    // 获取host
    public static function getHost(){
        self::getCurrentUrl();
        $parseUrlArr = parse_url(self::$currentUrl);
        return isset($parseUrlArr['host'])?self::getScheme().'://'.$parseUrlArr['host']:'';
    }

    // 获取请求方法
    public static function getMethod(){
        return isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'';
    }

    // 是否命令行模式
    function isCli(){
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }

    // 是否get请求
    public static function isGet(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='GET'){
            return true;
        }
        return false;
    }

    // 是否post请求
    public static function isPost(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='POST'){
            return true;
        }
        return false;
    }

    // 是否put请求
    public static function isPut(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='PUT'){
            return true;
        }
        return false;
    }

    // 是否path请求
    public static function isPatch(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='PATCH'){
            return true;
        }
        return false;
    }

    // 是否delete请求
    public static function isDelete(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='DELETE'){
            return true;
        }
        return false;
    }

    // 是否copy请求
    public static function isCopy(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='COPY'){
            return true;
        }
        return false;
    }

    // 是否options 请求
    public static function isOptions(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='OPTIONS'){
            return true;
        }
        return false;
    }

    // 是否link请求
    public static function isLink(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='LINK'){
            return true;
        }
        return false;
    }

    // 是否unlick请求
    public static function isUnlick(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='UNLINK'){
            return true;
        }
        return false;
    }

    // 是否purge请求
    public static function isPurge(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='PURGE'){
            return true;
        }
        return false;
    }

    // 是否lock请求
    public static function isLock(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='LOCK'){
            return true;
        }
        return false;
    }

    // 是否unlock请求
    public static function isUnlock(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='UNLOCK'){
            return true;
        }
        return false;
    }

    // 是否propfind请求
    public static function isPropfind(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='PROPFIND'){
            return true;
        }
        return false;
    }

    // 是否view请求
    public static function isView(){
        if(isset($_SERVER['REQUEST_METHOD'] )&& $_SERVER['REQUEST_METHOD']=='VIEW'){
            return true;
        }
        return false;
    }

    // 是否ajax请求
    public static function isAjax(){
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            return true;
        }else{
            if (isset($_REQUEST['requestDataType'])&&$_REQUEST['requestDataType']=='ajax'){
                return true;
            }else{
                return false;
            }
        }
    }
}