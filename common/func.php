<?php
/**
 +-----------------------------------------
 * Common Functions
 +-----------------------------------------
 * @category    Pt
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 +-----------------------------------------
 */


/**
 * autoload class
 +-----------------------------------------
 * @param string $classname
 * @return void
 */
function __autoload($classname)
{
    if (false !== import(CLASS_PATH.'pt/'.$classname))
        return;

    if (false !== import(CLASS_PATH.$classname))
        return;

    // Autoload path of config
    if (config('web.autoload_path'))
    {
        $paths  =   explode(',', config('web.autoload_path'));
        foreach ($paths as $path)
            if (false !== import($path.$classname))
                return ;
    }

    return;
}



/**
 * get / set config
 +-----------------------------------------
 * @param mix $name
 * @param mix $value
 * @return void
 */
function config($name='', $value=null)
{
    static $_config = array();

    // set config
    if (!is_null($value))
    {
        if (strpos($name,'.'))
        {
            $array   =  explode('.', $name);
            $_config[$array[0]][$array[1]] =   $value;
        }
        else
        {
            if (isset($_config[$name]))
                $_config[$name] = array_merge($_config[$name], $value);
            else
                $_config[$name] = $value;
        }
        return;
    }

    // empty name will get all config
    if (empty($name))
        return $_config;

    if (strpos($name,'.'))
    {
        // have "." get sub config value, like db.name
        $array   =  explode('.', $name);

        if(!isset($_config[$array[0]])) config($array[0]);

        if (isset($_config[$array[0]][$array[1]]))
            return $_config[$array[0]][$array[1]];

        return null;
    }
    elseif (isset($_config[$name]))
    {
        // get all package config
        return $_config[$name];
    }
    else
    {
        // config file is exist, require it
        if (defined('CONFIG_PATH') && is_file(CONFIG_PATH.$name.'.php'))
        {
            config($name, array_change_key_case(include CONFIG_PATH.$name.'.php'));
            return $_config[$name];
        }
        else
        {
            return null;
        }
    }
}



/**
 * use trigger_error throw user error
 +-----------------------------------------
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @return void
 */
function error_handler($errno, $errstr, $errfile, $errline)
{
    // use error_handler
    // transform a system error to a ErrorException
    $error = new ErrorException($errstr, 0, $errno, $errfile, $errline);
    switch ($errno)
    {
        case E_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_USER_ERROR:
            throw $error;
        default:
            _exception::append($error);
    }
}



/**
 * get throw exception
 +-----------------------------------------
 * @param  Exception $e
 * @return void
 */
function throw_exception($e)
{
    // Exception:
    //     throw a exception (WARNING / NOTICE) don't die the process
    //     throw_exception use _exception.cls to record more information
    //     return a id of error

    if ( is_a($e, 'ErrorException') )
    {
        $code = $e -> getSeverity();

        switch ($code)
        {
            case E_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
                _exception::halt($e);
        }
    }

    // recode the exception if not a _exception
    if ( !is_a($e, '_exception') )
        _exception::append($e);
}



/**
 * log
 +-----------------------------------------
 * @param  string   $message
 * @param  int      $level
 * @param  enum     $type
 * @return void
 */
function log_message($message, $code=0, $type='Debug')
{
    static $log;
    if (!$log) $log = new log(config('log'));
    $log -> write($message, $code, $type);
}



/**
 * Trace
 * debug record
 +-----------------------------------------
 * @access public
 * @param bool $message
 * @return void
 */
function trace($message=false)
{
    if(!DEBUG) return;

    static $trace = array();

    if ($message===false)
        return $trace;
    else
        $trace[] = is_string($message) ? $message : var_export($message, true);
}


/**
 * URL Redirect
 +-----------------------------------------
 * @param string $url
 * @param integer $time
 * @param string $msg
 */
function redirect($url, $time=0, $msg='')
{
    if (empty($url)) $url = 'http://'.$_SERVER['SERVER_NAME'];
    if (empty($msg)) $msg = str_replace(array('%TIME%', '%URL%'), array($time, $url), _('redirect_msg'));
    if (!headers_sent())
    {
        // redirect
        header("Content-Type:text/html; charset=utf-8");
        if (0 === $time)
        {
            header("Location: ".$url);
        }
        else
        {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }
    else
    {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time!=0)
        {
            $str .= $msg;
        }
        exit($str);
    }
}



/**
 * include / require
 +-----------------------------------------
 * @param string $path
 * @param string $ext
 * @return boolen
 */
function import($path, $ext='.class.php')
{
    static $_file = array();

    if (isset($_file[$path]))
        return $_file[$path];

    $file = $path . $ext;
    if (file_exists($file))
    {
        if($ext == '.class.php')
            require_once($file);
        else
            include_once($file);
        $_file[$path] = true;
    }
    else
    {
        $_file[$path] = false;
        return false;
    }
}



/**
 * addslashes
 +-----------------------------------------
 * @param mixed $value
 * @return mixed
 */
function addslashes_deep($value)
{
    $value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    return $value;
}



/**
 * check a path / file is writable
 +-----------------------------------------
 * @access public
 * @param  string $path
 * @return void
 */
function _is_writable($path)
{
    // is not Windows, and not safe mode, use is_writable
    if(!strstr(PHP_OS, 'WIN') && @ini_get("safe_mode") == false)
        return is_writable($path);

    // is a dir, create a file
    if ($path{strlen($path)-1} == '/')
        return _is_writable($path.uniqid(mt_rand()).'.tmp');

    if (file_exists($path))
    {
        if (!($f = @fopen($path, 'r+')))
            return false;
        fclose($f);
        return true;
    }

    if (!($f = @fopen($path, 'w')))
        return false;
    fclose($f);
    unlink($path);
    return true;
}


/**
 * a better mkdir
 +-----------------------------------------
 * @param string $dir
 * @param int $mode
 * @return void
 */
function _mkdir($dir, $mode = 0755)
{
    if (is_dir($dir) || @mkdir($dir, $mode))
    {
        if (config("wp.build_dir_secure"))
            file_put_contents("{$dir}/index.html", "");

        return true;
    }
    if (!_mkdir(dirname($dir), $mode)) return false;
    return @mkdir($dir,$mode);
}



/**
 * mkdir for a deep path
 +-----------------------------------------
 * @param string $dir
 * @return void
 */
function mkdirs($dir)
{
    if (is_dir($dir)) return true;
    $dir = explode('/', $dir);
    $temp = '';
    foreach ($dir as $value)
    {
        $temp .= $value.'/';
        if ($value=='.' || $value == '..' || !$value) continue;
        $return = _mkdir($temp);
    }
    return $return;
}




/**
 * short code for translate
 +-----------------------------------------
 * @param string $key
 * @return string
 */
function __($key)
{
    return translate($key);
}



/**
 * translate
 +-----------------------------------------
 * @param string $key
 * @return string
 */
function translate($key)
{
    return language::translate($key);
}



/**
 * get a object or function id
 +-----------------------------------------
 * @access public
 * @param mixed $function
 * @return void
 */
function getidx($function)
{
    if ( is_string($function) )
        return $function;

    if ( is_object($function) )
        return spl_object_hash($function);

    $function = (array)$function;

    if ( is_object($function[0]) )
    {
        return spl_object_hash($function[0]).$function[1];
    }
    else
    {
        // Static Calling
        return $function[0] . '::' . $function[1];
    }
}



/**
 * get a deep path of id or hash
 +-----------------------------------------
 * @param string $str
 * @param int $split_length
 * @param int $length
 * @param string $pad_string
 * @param int $pad_type
 * @return string
 */
function path_by_str($str, $split_length = 3, $length = 9, $pad_string = '0', $pad_type = STR_PAD_LEFT)
{
    $string = str_pad($str, $length, $pad_string, $pad_type);
    $dirs = str_split($string, $split_length);
    return implode('/', $dirs);
}


/**
 * get a db obj
 +-----------------------------------------
 * @access public
 * @param array $config
 * @return void
 */
function db($config=array())
{
    static $db = array();

    array_multisort($config, SORT_DESC, SORT_STRING);
    $ids = md5(serialize($config));

    if(isset($db[$ids]))
        return $db[$ids];

    $db[$ids] = new db($config);
    return $db[$ids];
}

/**
 * print json result
 +-----------------------------------------
 * @param mixed $data
 * @param int $errcode
 * @param string $err
 * @return void
 */
function json_return($data, $errcode=0, $err='')
{
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(array('s'=>(int)$errcode, 'rs'=>$data, 'err'=>$err)));
}


/**
 * charset covert
 +-----------------------------------------
 * @param string $content
 * @param string $from
 * @param string $to
 * @return void
 */
function charset_convert($content, $from='gbk', $to='utf-8')
{
    if(function_exists('mb_convert_encoding'))
    {
        return mb_convert_encoding ($content, $to, $from);
    }
    elseif (function_exists('iconv'))
    {
        return iconv($from, $to, $content);
    }
    else
    {
        return $content;
    }
}

?>