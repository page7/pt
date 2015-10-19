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

class action extends \pt\framework\event
{

    /**
     * do action
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function exec()
    {
        $args = array();
        for ( $a = 0; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        call_user_func_array("\\pt\\framework\\event::trigger", $args);
    }



    /**
     * add action
     +-----------------------------------------
     * @access public
     * @return void
     */
    static public function add($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return \pt\framework\event::bind($tag, $function_to_add, $priority, $accepted_args);
    }


}