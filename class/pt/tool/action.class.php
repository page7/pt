<?php
/**
 * action
 * like wordpress action
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


class action
{

    static $actions = array();


    /**
     * do action
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function exec($tag, $arg = '')
    {
        $args = array();

        if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
            $args[] = &$arg[0];
        else
            $args[] = $arg;

        for ( $a = 2; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        if ( empty(self::$actions[$tag]) ) return;

        ksort( self::$actions[$tag] );
        reset( self::$actions[$tag] );

        do
        {
            foreach ( (array) current(self::$actions[$tag]) as $fun )
                if ( !is_null($fun['function']) )
                    call_user_func_array($fun['function'], array_slice($args, 0, (int)$fun['accepted_args']));
        }
        while ( next(self::$actions[$tag]) !== false );

    }



    /**
     * add action
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function add($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        $idx = getidx($function_to_add);
        self::$actions[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
        return true;
    }



    /**
     * isset
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function is_set()
    {

    }



    /**
     * remove
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function remove()
    {

    }

}