<?php
/**
 +-----------------------------------------
 * Common Functions
 +-----------------------------------------
 * @category    pt
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
    if (false !== import(CLASS_PATH.str_replace('\\', '/', $classname)))
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
        // have "." get config value, like db.name
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
            pt\framework\exception::append($error);
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
                pt\framework\exception::halt($e);
        }
    }

    // recode the exception if not a _exception
    if ( !is_a($e, '_exception') )
        pt\framework\exception::append($e);
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
    if (!$log) $log = new pt\framework\log(config('log'));

    $log -> write($message, $code, $type);
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
    if (empty($msg)) $msg = str_replace(array('%TIME%', '%URL%'), array($time, $url), __('redirect_msg'));
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
    return pt\framework\language::translate($key);
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

