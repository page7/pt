<?php
/**
 * filter of
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class filter extends base
{

    static $filters = array();


    public function __construct($config=array())
    {
        if ($config)
            self::load($config);
    }


    /**
     * Apply filter for a variable
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function apply($tag, $value)
    {
        $args = array();

        if (empty(self::$filters[$tag])) return $value;

        ksort( self::$filters[$tag] );
        reset( self::$filters[$tag] );

        if ( empty($args) )
            $args = func_get_args();

        do
        {
            foreach ( (array) current(self::$filters[$tag]) as $fun )
                if ( !is_null($fun['function']) )
                {
                    $args[1] = $value;
                    $value = call_user_func_array($fun['function'], array_slice($args, 1, (int) $fun['accepted_args']));
                }

        }
        while ( next(self::$filters[$tag]) !== false );

        return $value;
    }




    /**
     * Batch append filter
     +-----------------------------------------
     * @access public
     * @param  array $filter
     * @return void
     */
    static public function load($filter)
    {
        foreach ($filter as $tag => $handler)
        {
            if (isset($handler[0]))
            {
                foreach ($handler as $h)
                    self::add($tag, $h['handler'], $h['priority'], $h['accepted_args']);
            }
            else
            {
                self::add($tag, $handler['handler'], $handler['priority'], $handler['accepted_args']);
            }
        }
    }




    /**
     * Add a new handle of the filter
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function add($tag, $handler, $priority = 10, $accepted_args = 1)
    {
        $idx = getidx($handler);
        self::$filters[$tag][$priority][$idx] = array('function' => $handler, 'accepted_args' => $accepted_args);
        return true;
    }




    /**
     * Remove (all) handle of the filter
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function remove($tag, $handler = null)
    {
        if ( $handler )
        {
            $_idx = getidx($handler);
            foreach ( self::$filters[$tag] as $priority => $functions )
                foreach ( $functions as $idx => $fun )
                    if ( $idx == $_idx )
                        unset(self::$filters[$tag][$priority][$idx]);
        }
        else
        {
            unset( self::$filters[$tag] );
        }
    }

}
