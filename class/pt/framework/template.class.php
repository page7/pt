<?php
/**
 * template
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class template extends base
{
    // template dir root path
    static $path = PT_PATH;

    // template dir name
    static $dir = 'template';

    // default variables
    static $vars = array();

    // callback
    static $callback = array();


    // config
    public function __construct($config=array())
    {
        $this -> __config($config);
    }


    /**
     * assign
     +-----------------------------------------
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    static function assign($key, $value=null)
    {
        if (is_array($key) && is_null($value))
        {
            self::$vars = array_merge(self::$vars, $key);
        }
        else
        {
            self::$vars[$key] = $value;
        }
    }



    /**
     * get
     +-----------------------------------------
     * @access public
     * @param mixed $key
     * @return void
     */
    static function get($key)
    {
        return isset(self::$vars[$key]) ? self::$vars[$key] : null;
    }



    /**
     * fetch
     +-----------------------------------------
     * @access public
     * @param string $file
     * @param string $output
     * @param string $suffix
     * @return void
     */
    static function fetch($file, $output=null, $suffix='.tpl.php')
    {
        extract(self::$vars, EXTR_OVERWRITE);

        ob_start();
        ob_implicit_flush(0);

        include(self::$path.self::$dir.'/'.$file.$suffix);

        $content = ob_get_clean();
        if ($output)
        {
            file_put_contents($content, $output);
        }
        else
        {
            return $content;
        }
    }



    /**
     * display
     +-----------------------------------------
     * @access public
     * @param string $file
     * @return void
     */
    static function display($file, $debug=false, $suffix='.tpl.php')
    {
        $debug = DEBUG || $debug;

        extract(self::$vars, EXTR_OVERWRITE);
        include(self::$path.self::$dir.'/'.$file.$suffix);

        if (!empty(self::$callback['display']))
        {
            call_user_func(self::$callback['display'], $debug);
        }
    }


}
