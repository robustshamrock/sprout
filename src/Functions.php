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