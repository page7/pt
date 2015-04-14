<?php
/**
 * debug
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class debug extends base
{


    // construct
    public function __construct($config=array()) {}



    /**
     * log
     +-----------------------------------------
     * @access public
     * @param mixed $data
     * @param string $type
     * @return void
     */
    static function log($data=null, $type='Debug')
    {
        static $log = array();

        if (is_null($data))
            return $log;
        else
            $log[] = array('message'=>$data, 'type'=>$type);
    }



    /**
     * trace
     +-----------------------------------------
     * @access public
     * @param mixed $data
     * @return void
     */
    static function trace($data=null)
    {
        $trace = debug_backtrace();
        array_shift($trace);

        if ($data) self::log($data);

        self::log($trace);
    }



    /**
     * show the debug msg
     +-----------------------------------------
     * @access public
     * @return void
     */
    static function show()
    {
        $trace = self::log();
        ?>
        <style type="text/css">
        #-pt-debug { position:relative; z-index:2147483584; background-color:rgba(255,255,255,0.9); margin:20px; padding:20px 20px 40px 20px; font:14px/20px Tahoma; border:#666 dashed 1px; opacity:0.2; text-align:left; }
        #-pt-debug:hover { opacity:1; }
        #-pt-debug legend { text-shadow:1px -2px 0px #FFF; font:bold 16px Tahoma; margin:0px; border:0px; width:auto; }
        #-pt-debug p { margin-top:5px; }
        #-pt-debug p code { display:block; background:#FAFAFA; border:#DDD solid 1px; font-size:12px; padding:10px; margin:5px 0px 0px 30px; }
        #-pt-debug .pt-error { color:#CB1818; }
        #-pt-debug .pt-warning { color:#E88500; }
        #-pt-debug ._cls { position:absolute; top:20px; right:15px; color:#E00; font-weight:bold; font-size:16px; }
        </style>
        <fieldset id="-pt-debug">
            <legend>DEBUG MESSAGE</legend>
            <?php foreach ($trace as $value) { ?>
                    <p class="pt-<?php echo $value['type']; ?>"><?php echo is_string($value['message']) ? $value['message'] : var_export($value['message'], true); ?></p>
            <?php } ?>
            <a class="_cls" href="javascript:;" onclick="this.parentNode.style.display='none'">CLOSE</a>
        </fieldset>
        <?php
    }

}