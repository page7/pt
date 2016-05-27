<?php
/**
 * event
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class event extends base
{

    static $events = array();


    public function __construct($config=array())
    {
        if ($config)
            self::load($config);
    }


    /**
     * Trigger a event (hook)
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function trigger($event, $arg = '')
    {
        $args = array();

        if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
            $args[] = &$arg[0];
        else
            $args[] = $arg;

        for ( $a = 2; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        if ( empty(self::$events[$event]) ) return;

        ksort( self::$events[$event] );
        reset( self::$events[$event] );

        do
        {
            foreach ( (array) current(self::$events[$event]) as $fun )
                if ( !is_null($fun['function']) )
                    call_user_func_array($fun['function'], array_slice($args, 0, (int)$fun['accepted_args']));
        }
        while ( next(self::$events[$event]) !== false );

    }



    /**
     * Batch append listener
     +-----------------------------------------
     * @access public
     * @param  array $listener
     * @return void
     */
    static public function load($listener)
    {
        foreach ($listener as $event => $handler)
        {
            if (isset($handler[0]))
            {
                foreach ($handler as $h)
                    self::bind($event, $h['handler'], $h['priority'], $h['accepted_args']);
            }
            else
            {
                self::bind($event, $handler['handler'], $handler['priority'], $handler['accepted_args']);
            }
        }
    }



    /**
     * Create a listener for the event to execute handler
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function bind($event, $handler, $priority = 10, $accepted_args = 1)
    {
        $idx = getidx($handler);
        self::$events[$event][$priority][$idx] = array('function' => $handler, 'accepted_args' => $accepted_args);
        return true;
    }




    /**
     * Remove (all) listener for the event
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function unbind($event, $handler = null)
    {
        if ( $handler )
        {
            $_idx = getidx($handler);
            foreach ( self::$events[$event] as $priority => $functions )
                foreach ( $functions as $idx => $fun )
                    if ( $idx == $_idx )
                        unset(self::$events[$event][$priority][$idx]);
        }
        else
        {
            unset( self::$event[$event] );
        }
    }

}