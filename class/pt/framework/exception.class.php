<?php
/**
 * exception
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class exception extends \Exception
{

    static $_exception = array();


    /**
     * default Exception
     +-----------------------------------------
     * @access public
     * @param string $message
     * @param int    $code
     * @param Exception  $previous
     */
    final public function __construct($message, $code=0, $previous=NULL)
    {
        $this -> message = $message;
        $this -> code = $code;
        self::append($this);
    }



    /**
     * Record Exception
     +-----------------------------------------
     * @access public
     * @param  Exception $e
     * @return void
     */
    static function append($e)
    {
        // log
        $message = $e -> getMessage() . ' File:' . $e -> getFile() . ' Line:' . $e -> getLine();

        if (DEBUG)
        {
            debug::log($message, 'Warning');
            $message .= "\n".$e -> getTraceAsString();
        }

        $code = is_a($e, 'ErrorException') ? $e -> getSeverity() : $e -> getCode();

        log::init() -> write($message, $code, get_class($e));
        self::$_exception[] = $e;
    }



    /**
     * Get last append Exception
     +-----------------------------------------
     * @access public
     * @param  Exception $check
     * @return void
     */
    static function get_last_index($e=null)
    {
        if (!$check)
            return count(self::$_exception);

        for ($i=count(self::$_exception); $i>=1; $i--)
        {
            if (self::$_exception[$i-1] === $e)
                return $i;
        }
    }



    /**
     * halt
     +-----------------------------------------
     * @access public
     * @param ErrorException $e
     * @return void
     */
    static function halt($e)
    {
        self::append($e);

        if(IS_AJAX)
        {
            $code = $e->getCode();
            json_return(null, $code ? $code : 9999, $e->getMessage());
        }

        if (DEBUG)
        {
            $traceInfo='';
            $trace = $e -> getTrace();
            foreach ($trace as $t)
            {
                $traceInfo .= $t['file'].' ('.$t['line'].') ';
                $traceInfo .= $t['class'].$t['type'].$t['function'].'(';
                foreach ($t['args'] as $k => $arg)
                {
                    if ($k != 0) $traceInfo .= ',';
                    switch (gettype($arg))
                    {
                        case 'object':
                        case 'array':
                            $traceInfo .= '<b title="'.addslashes(var_export($arg)).'">'.ucfirst(gettype($arg)).'</b>'; break;
                        default:
                            $traceInfo .= $arg;
                    }
                }
                $traceInfo .= ")\n";
            }

            $message = $e -> getMessage() . ' File:' . $e -> getFile() . ' Line:' . $e -> getLine();
            debug::log($message, 'Error');

            include COMMON_PATH.'500.php'; exit;
        }
        else
        {
            //否则定向到错误页面
            redirect(WEB_PATH.'500.html');
        }
        exit;
    }


    /**
     * set http header
     +-----------------------------------------
     * @access public
     * @param int $code
     * @return void
     */
    static function error_header($code = 500)
    {
        $stati = array(
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );


        if (!isset($stati[$code]))
        {
            trigger_error('invalid code', E_USER_ERROR);
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;

        if (substr(php_sapi_name(), 0, 3) == 'cgi')
        {
            header("Status: {$code} {$text}", true);
        }
        elseif ($server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0')
        {
            header($server_protocol." {$code} {$text}", true, $code);
        }
        else
        {
            header("HTTP/1.1 {$code} {$text}", true, $code);
        }
    }

}
