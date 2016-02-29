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
    static public $path = 'template/';

    // template package name(dir)
    static protected $package = '';

    // default variables
    static public $vars = array();


    public function __construct($config=array())
    {
        $this -> __config($config);
    }

    /**
     * assign
     +-----------------------------------------
     * @access public
     * @param  string $key
     * @param  mixed $value
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
     * @param  string $key
     * @return void
     */
    static function get($key)
    {
        return isset(self::$vars[$key]) ? self::$vars[$key] : null;
    }



    /**
     * change or load package
     +-----------------------------------------
     * @access public
     * @param  string $name
     * @return void
     */
    static function package($name=null)
    {
        if (is_null($name))
            return self::$package;
        else
            return self::$package = $name;
    }



    /**
     * fetch
     +-----------------------------------------
     * @access public
     * @param  string $file
     * @param  string $output
     * @param  string $suffix
     * @return void
     */
    static function fetch($file, $output=null, $suffix='.tpl.php')
    {
        extract(self::$vars, EXTR_OVERWRITE);

        ob_start();
        ob_implicit_flush(0);

        $path = self::$path.self::$package.'/'.$file;
        include($path.$suffix);

        $content = ob_get_clean();
        event::trigger('pt\framework\template:fetch', $path, $content, $output);

        if ($output)
        {
            file_put_contents($output, $content);
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
     * @param  string  $file
     * @param  boolean $debug
     * @param  string  $suffix
     * @return void
     */
    static function display($file, $debug=false, $suffix='.tpl.php')
    {
        $debug = DEBUG || $debug;

        extract(self::$vars, EXTR_OVERWRITE | EXTR_REFS );

        $path = self::$path.self::$package.'/'.$file;
        include($path.$suffix);

        event::trigger('pt\framework\template:display', $path, $debug);
    }




    /**
     * include
     +-----------------------------------------
     * @access public
     * @param  string $path
     * @param  array  $vars
     * @param  string $suffix
     * @return void
     */
    static function append($path, $vars=array(), $suffix='.tpl.php')
    {
        extract(self::$vars, EXTR_OVERWRITE | EXTR_REFS );

        if ($vars)
            extract($vars, EXTR_OVERWRITE | EXTR_REFS );

        include(self::$path.self::$package.'/'.$path.$suffix);
    }

}
