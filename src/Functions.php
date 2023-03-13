<?php
namespace Shamrock\Instance;
/**
 * @param $path
 * @return array
 * 逐行读取文件保存微数组
 */
function ReadLineByLineToArray($path){
    if (!is_file($path)){
        return [];
    }

    $file = fopen($path, "r");
    $user=array();
    $i=0;
    //输出文本中所有的行，直到文件结束为止。
    while(! feof($file))
    {
        $str = trim(fgets($file));//fgets()函数从文件指针中读取一行
        $str = preg_replace('/\s+/', '', $str);
        if(strpos($str,'')!==false) {
            $processArr = explode('=',$str);
            if (isset($processArr[0]) && isset($processArr[1])){
                $user[$processArr[0]]= trim($processArr[1]);
            }else{
                $user[$i] = trim($str);
            }
        }

        $i++;
    }
    fclose($file);
    $user=array_filter($user);
    return $user;
}

function createGuid($namespace = '') {
    static $guid = '';

    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['PHP_SELF'];
    $data .= $_SERVER['REMOTE_PORT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $data .= generateGuid($namespace);
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid = '{' .
        substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12) .
        '}';
    return $guid;
}

/**
 * @param $prefix
 * @return string
 * 生成唯一id
 */
function generateGuid($prefix=''){
    //假设一个机器id
    $machineId = mt_rand(100000,999999);

    //41bit timestamp(毫秒)
    $time = floor(microtime(true) * 1000);

    //0bit 未使用
    $suffix = 0;

    //datacenterId  添加数据的时间
    $base = decbin(pow(2,40) - 1 + $time);

    //workerId  机器ID
    $machineid = decbin(pow(2,9) - 1 + $machineId);

    //毫秒类的计数
    $random = mt_rand(1, pow(2,11)-1);

    $random = decbin(pow(2,11)-1 + $random);
    //拼装所有数据
    $base64 = $suffix.$base.$machineid.$random;
    //将二进制转换int
    $base64 = bindec($base64);

    $id = sprintf('%.0f', $base64);

    return $prefix.$id;
}

/**
 * @param $path
 * @param $args
 * @return false|mixed
 * 载入文件
 */
function HelperLoad($path,$args=null){
    if(is_array($args)){
        extract($args);
    }
    if(!is_file($path)){
        return false;
    }else{
        return include $path;
    }
}

/**
 * @param $path
 * @return false|mixed
 * 载入配置文件
 */
function HelperLoadConfig($path){
    if(!is_file($path)){
        return false;
    }else{
        return include $path;
    }
}

/**
 * @param $dir
 * @param $dir_array
 * @return void
 * 查找文件
 */
function helperFindFiles($dir, &$dir_array){
    // 读取当前目录下的所有文件和目录（不包含子目录下文件）
    $files = scandir($dir);

    if (is_array($files)) {
        foreach ($files as $val) {
            // 跳过. 和 ..
            if ($val == '.' || $val == '..')
                continue;

            // 判断是否是目录
            if (is_dir($dir . '/' . $val)) {
                // 将当前目录添加进数组
                $dir_array[$dir][] = $val;
                // 递归继续往下寻找
                \helperFindFiles($dir . '/' . $val, $dir_array);
            } else {
                // 不是目录也需要将当前文件添加进数组
                $dir_array[$dir][] = $val;
            }
        }
    }
}

/**
 * @return string
 * 获取页面链接
 */
function GetPageurl() {
    $pageURL = 'http';
    if(isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on"){
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }else{
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}


function MeargeConfigs($config){
    foreach($config as $key=>$val){
        $config[$key]['key_length'] = mb_substr_count($key,'/');
    }

    $targetConfigs = [];
    $cofnigs = sortByKeyDesc($config,'key_length');

    foreach ($cofnigs as $val){
        $targetConfigs =array_merge($targetConfigs,$val);
    }
    return $targetConfigs;
}

function sortByKeyDesc($arr, $key) {
    array_multisort(array_column($arr, $key), SORT_ASC, $arr);
    return $arr;
}

/**
 * @param $path
 * @param $search
 * @return array
 * 载入某些配置
 */
function HelperBathLoadConfigs($path,$search){
    if(!is_dir($path)){
        return [];
    }
    $arr = array();
    $data = scandir($path);
    $configs = [];
    $settingConfigs = [];

    foreach ($data as $value){
        if($value != '.' && $value != '..'){
            $arr[] = $value;
            if(is_dir($path . DIRECTORY_SEPARATOR . $value)){
                $folder_list = [];
                helper_find_files($path . DIRECTORY_SEPARATOR . $value , $folder_list);

                foreach($folder_list as $key=>$val){
                    foreach($val as $vval){
                        $settingConfigs = '';

                        $targetName = str_replace('/',DIRECTORY_SEPARATOR,$key);
                        $processKeyName = explode('App'.DIRECTORY_SEPARATOR,$targetName);

                        $targeetKyeNameArr = [];
                        $targeetKyeNameStr = '';

                        if (isset($processKeyName[1])){
                            if (strpos($processKeyName[1],DIRECTORY_SEPARATOR)!==false){
                                $targeetKyeNameArr = explode(DIRECTORY_SEPARATOR,$processKeyName[1]);
                                $targeetKyeNameStr = $targeetKyeNameArr[0];
                            }else{
                                $targeetKyeNameStr = $processKeyName[1];
                            }
                        }

                        if(strpos($vval,'.php') && strpos($key,$search)){
                            $keyName = substr($vval,0,strpos($vval,'.'));
                            $settingConfigs = include($key . DIRECTORY_SEPARATOR . $vval);
                            $processName = $key . DIRECTORY_SEPARATOR . $vval;
                            $targetNameArr = explode('Config',$processName);
                            if(isset($targetNameArr[1])){
                                $targetNameArr = str_replace(DIRECTORY_SEPARATOR,'/',$targetNameArr[1]);
                                $processTargetNameArr = explode('.php',$targetNameArr);
                                $targetNem = trim($processTargetNameArr[0],'/');
                            }else{
                                $targetNem = $vval;
                            }

                            if(is_array($settingConfigs)){
                                $configs[$targeetKyeNameStr][$targetNem] = $settingConfigs;
                                //$configs[$targetNem]['file_md5'] = md5_file($key . DIRECTORY_SEPARATOR . $vval);
                            }
                        }
                    }
                }
            }else{
                if(is_file($path . DIRECTORY_SEPARATOR . $value)){
                    if(strpos($path . DIRECTORY_SEPARATOR . $value,'.php')&& strpos($path . DIRECTORY_SEPARATOR . $value,$search)){

                        $targetName = str_replace('/',DIRECTORY_SEPARATOR,$path . DIRECTORY_SEPARATOR . $value);
                        $processKeyName = explode('App'.DIRECTORY_SEPARATOR,$targetName);

                        $targeetKyeNameArr = [];
                        $targeetKyeNameStr = '';

                        if (isset($processKeyName[1])){
                            if (strpos($processKeyName[1],DIRECTORY_SEPARATOR)!==false){
                                $targeetKyeNameArr = explode(DIRECTORY_SEPARATOR,$processKeyName[1]);
                                $targeetKyeNameStr = $targeetKyeNameArr[0];
                            }else{
                                $targeetKyeNameStr = $processKeyName[1];
                            }
                        }

                        $processName = $path . DIRECTORY_SEPARATOR . $value;
                        $targetNameArr = explode('Config',$processName);
                        if(isset($targetNameArr[1])){
                            $targetNameArr = str_replace(DIRECTORY_SEPARATOR,'/',$targetNameArr[1]);
                            $processTargetNameArr = explode('.php',$targetNameArr);
                            $targetNem = trim($processTargetNameArr[0],'/');
                        }else{
                            $targetNem = $value;
                        }

                        $settingConfigs = include($path . DIRECTORY_SEPARATOR . $value);
                        $configs[$targeetKyeNameStr][$targetNem] = $settingConfigs;
                        //$configs[$targetNem]['file_md5'] = md5_file($path . DIRECTORY_SEPARATOR . $value);
                    }
                }

            }
        }
    }

    return $configs;
}

/**
 * @param $path
 * @return array
 * 载入某个目录下所有配置文件
 */
function HelperLoadConfigs($path){
    if(!is_dir($path)){
        return [];
    }
    $arr = array();
    $data = scandir($path);
    $configs = [];
    $settingConfigs = [];

    foreach ($data as $value){
        if($value != '.' && $value != '..'){
            $arr[] = $value;
            if(is_dir($path . DIRECTORY_SEPARATOR . $value)){
                $folder_list = [];
                helper_find_files($path . DIRECTORY_SEPARATOR . $value , $folder_list);

                foreach($folder_list as $key=>$val){
                    foreach($val as $vval){
                        $settingConfigs = '';
                        if(strpos($vval,'.php')){
                            $keyName = substr($vval,0,strpos($vval,'.'));
                            $settingConfigs = include($key . DIRECTORY_SEPARATOR . $vval);
                            $processName = $key . DIRECTORY_SEPARATOR . $vval;
                            $targetNameArr = explode('Config',$processName);
                            if(isset($targetNameArr[1])){
                                $targetNameArr = str_replace(DIRECTORY_SEPARATOR,'/',$targetNameArr[1]);
                                $processTargetNameArr = explode('.php',$targetNameArr);
                                $targetNem = trim($processTargetNameArr[0],'/');
                            }else{
                                $targetNem = $vval;
                            }

                            if(is_array($settingConfigs)){
                                $configs[$targetNem] = $settingConfigs;
                                //$configs[$targetNem]['file_md5'] = md5_file($key . DIRECTORY_SEPARATOR . $vval);
                            }
                        }
                    }
                }
            }else{
                if(is_file($path . DIRECTORY_SEPARATOR . $value)){
                    if(strpos($path . DIRECTORY_SEPARATOR . $value,'.php')){
                        $processName = $path . DIRECTORY_SEPARATOR . $value;
                        $targetNameArr = explode('Config',$processName);
                        if(isset($targetNameArr[1])){
                            $targetNameArr = str_replace(DIRECTORY_SEPARATOR,'/',$targetNameArr[1]);
                            $processTargetNameArr = explode('.php',$targetNameArr);
                            $targetNem = trim($processTargetNameArr[0],'/');
                        }else{
                            $targetNem = $value;
                        }

                        $settingConfigs = include($path . DIRECTORY_SEPARATOR . $value);
                        $configs[$targetNem] = $settingConfigs;
                        //$configs[$targetNem]['file_md5'] = md5_file($path . DIRECTORY_SEPARATOR . $value);
                    }
                }

            }
        }
    }

    return $configs;
}

/**
 * @param $dir
 * @param $dir_array
 * 获取子目录下所有文件
 */
function helper_find_files($dir, &$dir_array){
    // 读取当前目录下的所有文件和目录（不包含子目录下文件）
    $files = scandir($dir);

    if (is_array($files)) {
        foreach ($files as $val) {
            // 跳过. 和 ..
            if ($val == '.' || $val == '..')
                continue;

            // 判断是否是目录
            if (is_dir($dir . '/' . $val)) {
                // 将当前目录添加进数组
                $dir_array[$dir][] = $val;
                // 递归继续往下寻找
                helper_find_files($dir . '/' . $val, $dir_array);
            } else {
                // 不是目录也需要将当前文件添加进数组
                $dir_array[$dir][] = $val;
            }
        }
    }
}

/* PHP sha256() */
function sha256($data, $rawOutput = false)
{
    if (!is_scalar($data)) {
        return false;
    }
    $data = (string)$data;
    $rawOutput = !!$rawOutput;
    return hash('sha256', $data, $rawOutput);
}

/* PHP sha256_file() */
function sha256_file($file, $rawOutput = false)
{
    if (!is_scalar($file)) {
        return false;
    }
    $file = (string)$file;
    if (!is_file($file) || !is_readable($file)) {
        return false;
    }
    $rawOutput = !!$rawOutput;
    return hash_file('sha256', $file, $rawOutput);
}

/* PHP sha512() */
function sha512($data, $rawOutput = false)
{
    if (!is_scalar($data)) {
        return false;
    }
    $data = (string)$data;
    $rawOutput = !!$rawOutput;
    return hash('sha512', $data, $rawOutput);
}

/* PHP sha512_file()*/
function sha512_file($file, $rawOutput = false)
{
    if (!is_scalar($file)) {
        return false;
    }
    $file = (string)$file;
    if (!is_file($file) || !is_readable($file)) {
        return false;
    }
    $rawOutput = !!$rawOutput;
    return hash_file('sha512', $file, $rawOutput);
}


/**
 * @param $data
 * @param $key
 * @return string
 * @throws \Exception
 * session加密
 */
function encrypt($data, $key)
{
    $iv = random_bytes(16); // AES block size in CBC mode
    // Encryption
    $ciphertext = openssl_encrypt(
        $data,
        'AES-256-CBC',
        mb_substr($key, 0, 32, '8bit'),
        OPENSSL_RAW_DATA,
        $iv
    );
    // Authentication
    $hmac = hash_hmac(
        'SHA256',
        $iv . $ciphertext,
        mb_substr($key, 32, null, '8bit'),
        true
    );
    return $hmac . $iv . $ciphertext;
}

/**
 * @param $data
 * @param $key
 * @return false|string
 * sesssion解密
 */
function decrypt($data, $key)
{
    $hmac       = mb_substr($data, 0, 32, '8bit');
    $iv         = mb_substr($data, 32, 16, '8bit');
    $ciphertext = mb_substr($data, 48, null, '8bit');
    // Authentication
    $hmacNew = hash_hmac(
        'SHA256',
        $iv . $ciphertext,
        mb_substr($key, 32, null, '8bit'),
        true
    );
    if (! hash_equals($hmac, $hmacNew)) {
        return null;
        //throw new \Exception("Authentication failed");
    }
    // Decrypt
    return openssl_decrypt(
        $ciphertext,
        'AES-256-CBC',
        mb_substr($key, 0, 32, '8bit'),
        OPENSSL_RAW_DATA,
        $iv
    );
}