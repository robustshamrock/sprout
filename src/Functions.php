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

/**
 * @param $string
 * @param $tag
 * @return string|string[]
 * 获取视图模板值
 */
function getViewTagValue($string, $tag){
    $pattern = "/{{$tag}.*}(.*?){\/{$tag}}/s";
    preg_match_all($pattern, $string, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

/**
 * 从HTML代码中取出一个属性值(但不限于HTML)
 * @param string &$html HTML代码
 * @param string $property_name 属性名称
 * @param string $before_exp 前导正则式,不能包含界定符和说明符,默认为空
 * @param string $after_exp 后缀正则式,不能包含界定符和说明符,默认为空
 * @return false|string
 */
function FindHtmlTagProperty(string &$html, string $property_name,string $before_exp='',string $after_exp='')
{
    if (empty($html) || empty($property_name)) return false;
    $pn_len = strlen($property_name);
    $html_len = strlen($html);
    if ($pn_len >= $html_len) return false;
    $strarr = preg_split('//us', $html, -1, PREG_SPLIT_NO_EMPTY);
    $html_len = count($strarr);
    $subchr = '*';//不能设置为空白字符,影响下一步的匹配

    $repls = array();
    $quotchr = 0;
    $backslash = 0;
    for ($i = 0; $i < $html_len; $i++) {
        $chri = ord($strarr[$i]);
        if ($chri == 34 || $chri == 39) {
            if ($quotchr == 0) {
                $quotchr = $chri;
            } elseif ($quotchr == $chri) {
                if ($backslash % 2 == 1) {
                    $repls[] = array($i, $strarr[$i]);
                    $strarr[$i] = $subchr;
                } else
                    $quotchr = 0;
            }
            $backslash = 0;
        } else {
            if ($chri == 92) {
                $backslash++;
            } else {
                $backslash = 0;
            }
        }
    }
    $newStr = null;
    if (count($repls) > 0) $newStr = implode('', $strarr);
    else $newStr = $html;
    $matches = null;
    if (preg_match('/.*?'.$before_exp.'\b' . preg_quote($property_name) . '\s*=\s*((?P<quot>["\'])(?P<prop>.*?)\k<quot>|(?P<prop2>[^\s]+))'.$after_exp.'/ius', $newStr, $matches, PREG_OFFSET_CAPTURE) > 0) {
        $rc = count($repls);
        if ($rc == 0) {
            if ($matches['prop'][1] > -1) {
                return $matches['prop'][0];
            }
            return $matches['prop2'][0];
        } else {
            if ($matches['prop'][1] > -1) {
                return substr($html,$matches['prop'][1],strlen($matches['prop'][0]));
            }
            return substr($html,$matches['prop2'][1],strlen($matches['prop2'][0]));
        }
    }
    return false;
}

/**
 * @param $str
 * @param $key
 * @return array|string|string[]|null
 * 获取html标签
 */
function getHtmlProperty($str,$key){
    $str=preg_replace("/[\s\S]*\s".$key."[=\"\']+([^\"\']*)[\"\'][\s\S]*/","$1",$str);
    return $str;
};

/**
 * AES-256-CBC 加密
 * @param $data
 * @return mixed|string
 */
function encrypt_cbc($data,$key,$iv='')
{
    if(!$iv){
        // 16位
        $iv = '4387438hfdhfdjhg';
    }

    // 是否为空
    if(!$key){
        return null;
    }

    // 是否数组
    if(is_array($data)){
        $data = json_encode($data);
    }

    // 是否对象
    if(is_object($data)){
        $data = serialize($data);
    }

    $text = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($text);
}

/**
 * AES-256-CBC 解密
 * @param $text
 * @return string
 */
function decrypt_cbc($text,$key,$iv='')
{
    if(!$iv){
        $iv = '4387438hfdhfdjhg';
    }
    if(!$key){
        return null;
    }

    $decodeText = base64_decode($text);
    $data = openssl_decrypt($decodeText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    // 是否json数组
    if(myisJsonString($data)){
        return json_decode($data,true);
    }

    // 是否序列化对象
    $obj = unserialize($data);
    if($obj===false){
        return $data;
    }else{
        return $obj;
    }
}

/**
 * 校验json字符串
 * @param string $stringData
 * @return bool
 */
function myisJsonString($stringData)
{
    if (empty($stringData)) return false;

    try
    {
        //校验json格式
        json_decode($stringData, true);
        return JSON_ERROR_NONE === json_last_error();
    }
    catch (\Exception $e)
    {
        return false;
    }
}

// 公钥加密
function opensslPublicEncrypt($text,$public_key){
    $res = openssl_public_encrypt($text,$encrypt,$public_key);
    if($res==false) {
        return false;
    }else{
        return $encrypt;
    }
}

// 私钥解密
function opensslPrivateDecrypt($encrypt,$private_key){
    $res = openssl_private_decrypt($encrypt,$decrypt,$private_key);
    if($res==false){
        return null;
    }else{
        return $decrypt;
    }
}

// 私钥加密
function opensslPrivateEncrypt($text,$private_key){
    $res = openssl_private_encrypt($text,$encrypt,$private_key);
    if($res==false){
        return false;
    }else{
        return $encrypt;
    }
}

// 公钥解密
function opensslPublicDecrypt($encrypt,$public_key){
    $res = openssl_public_decrypt($encrypt,$decrypt,$public_key);
    if($res==false){
        return null;
    }else{
        return $decrypt;
    }
}