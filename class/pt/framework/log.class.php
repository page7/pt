<?php
/**
 * log
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class log extends base
{
    // file max size
    protected $file_max_size = 1048576;

    // log path
    protected $path = '';

    // log send type
    protected $type = 3;    //error_log() log types


    // config
    public function __construct($config=array())
    {
        $this -> __config($config);
    }


    /**
     * write log file
     +-----------------------------------------
     * @access public
     * @param string $message
     * @param int    $code
     * @param string $type
     * @return void
     */
    public function write($message, $code=0, $type='Debug')
    {
        $time = date('m-d H:i:s');

        // System / User error handle
        if ($type == 'ErrorException')
        {
            switch ($code)
            {
                case E_ERROR:
                    $filename = 'error';
                    $type = 'Error';
                    break;
                case E_USER_ERROR:
                    $filename = 'error';
                    $type = 'Debug Error';
                    break;
                case E_RECOVERABLE_ERROR:
                    $filename = 'error';
                    $type = 'Recoverable Error';
                    break;
                case E_WARNING:
                    $filename = 'warning';
                    $type = 'Warning';
                    break;
                case E_NOTICE:
                    $filename = 'notice';
                    $type = 'Notice';
                    break;
                case E_STRICT:
                    $filename = 'strict';
                    $type = 'Strict';
                    break;
                case E_USER_WARNING:
                    $filename = 'warning';
                    $type = 'Debug Warning';
                    break;
                case E_USER_NOTICE:
                    $filename = 'notice';
                    $type = 'Debug Notice';
                    break;
                default:
                    $filename = 'undefined';
                    $type = 'Undefined';
            }
        }
        else
        {
            if ($type == 'Debug')
            {
                $filename = 'debug';
                if(!DEBUG) return;  // debug not record in undebug mode
            }
            else
            {
                $filename = 'exception';
                $type = str_replace('Exception', '', $type);
            }
        }

        if (empty($_SERVER['REQUEST_METHOD']))
        {
            $method = 'PHP_CGI';
            $url = $_SERVER['SCRIPT_FILENAME'];
        }
        else
        {
            $method = $_SERVER['REQUEST_METHOD'];
            $url = $_SERVER['REQUEST_URI'];
        }

        $post = '';
        if ($method == 'POST')
        {
            $post = empty($GLOBALS["HTTP_RAW_POST_DATA"]) ? http_build_query($_POST) : $GLOBALS["HTTP_RAW_POST_DATA"];
        }
        $log = "[{$time}] {$method} {$url} {$post}\n{$type} : #{$code} $message\n\n";

        $logfile = $this -> path . date('ymd') . '_' . $filename . '.log';

        // check log file size
        // if size biger than config size, rename it, and create a new log.
        if ( file_exists($logfile) && floor($this -> file_max_size) <= filesize($logfile) )
        {
            // any php file function is clear catch, don't need clearstatcache again
            @rename($logfile, str_replace('.log', '_'.(NOW - strtotime('today')).'.log', $logfile));
        }

        // error_log don't need to consider file lock, for php bug##40897
        error_log($log, $this -> type, $logfile);
    }


}
