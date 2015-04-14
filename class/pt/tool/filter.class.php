<?php
/**
 * filter
 * like wordpress filter
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


class filter
{
    static $filters = array();


    /**
     * do action
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
     * add action
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function add($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        $idx = getidx($function_to_add);
        self::$filters[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
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
