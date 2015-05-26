<?php
/**
 * route
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class route extends base
{


    static protected $rules = array();


    /**
     * Construct
     +-----------------------------------------
     * @access public
     * @param array $config
     */
    public function __construct($config=array())
    {
        if (empty($config['routes']))
            $config['routes'] = array();

        $routes = array_merge($config['routes'], self::$rules);

        $path_info = $_SERVER['PATH_INFO'];

        $count = 0;
        $reg = str_replace('/', '(\\/', trim($path_info, '/'), $count);
        $pef = str_repeat('){0,1}', $count+1);

        preg_match_all("/,\/({$reg}{$pef}\//", ','.implode(',', array_keys($routes)), $matchs);

        $matchs = array_filter($matchs[0]);
        rsort($matchs);

        if (!$matchs) $matchs = array('//');

        if (!empty($routes['/']))
            $routes['//'] = &$routes['/'];

        foreach ($matchs as $v)
            if (($r = substr($v, 1)) && isset($routes[$r]) && is_callable($routes[$r]['callback']))
                return call_user_func_array($routes[$r]['callback'], self::params($path_info, $r, $routes[$r]['params']));
    }




    /**
     * Get Params
     +-----------------------------------------
     * @access protected
     * @param string $path_info
     * @param string $route
     * @param string $params
     * @return void
     */
    static protected function params($path_info, $route, $params)
    {
        $_params_val = explode('/', trim(substr($path_info, strlen($route)), '/'));

        if ($last = array_pop($_params_val))
            $_params_val[] = $last;

        if ($params)
        {
            $_params_key = explode('/', trim($params, '/'));
            foreach ($_params_key as $i => $k)
            {
                if ($k[0] == '$' && isset($_params_val[$i]))
                    $_GET[substr($k, 1)] = $_params_val[$i];
            }
        }

        return $_params_val;
    }




    /**
     * Add Route Rule
     +-----------------------------------------
     * @access public
     * @param string $rule
     * @param mixed $callback
     * @param string $params
     * @return void
     */
    static function add($rule, $callback=null, $params=null)
    {
        self::$rules[$rule] = array(
            'params'    => $params,
            'callback'  => $callback,
        );
    }


}