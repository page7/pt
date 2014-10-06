<?php
/**
 * template
 +-----------------------------------------
 * @category    Pt
 * @package     log
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */
class template
{
    static $path = PT_PATH;

    static $dirname = 'template';

    static $vars = array();


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
     * check file exist
     +-----------------------------------------
     * @access public
     * @param mixed $file
     * @param string $suffix
     * @return void
     */
    static function check($file, $suffix='.tpl.php')
    {
        return @is_file(self::$path.self::$dirname.'/'.$file.$suffix);
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
        include(self::$path.self::$dirname.'/'.$file.$suffix);

        ob_start();
        ob_implicit_flush(0);

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
        extract(self::$vars, EXTR_OVERWRITE);
        include(self::$path.self::$dirname.'/'.$file.$suffix);

        if(DEBUG && $debug)
        {
            self::trace();
        }
    }


    /**
     * debug trace
     +-----------------------------------------
     * @access public
     * @return void
     */
    static function trace()
    {
        $trace = trace();
        ?>
        <style type="text/css">
        #_pt_debug { position:relative; z-index:9999; background-color:rgba(255,255,255,0.9); margin:20px; padding:10px 20px 40px 20px; font:14px/20px Tahoma; border:#666 dashed 1px; opacity:0.2; text-align:left; }
        #_pt_debug:hover { opacity:1; }
        #_pt_debug legend { text-shadow:1px -2px 0px #FFF; font:bold 16px Tahoma; margin:0px; border:0px; width:auto; }
        #_pt_debug p { margin-top:5px; }
        #_pt_debug p code { display:block; background:#FAFAFA; border:#DDD solid 1px; font-size:12px; padding:10px; margin:5px 0px 0px 30px; }
        #_pt_debug ._cls { position:absolute; top:20px; right:15px; color:#E00; font-weight:bold; font-size:16px; }
        </style>
        <fieldset id="_pt_debug">
            <legend>DEBUG MESSAGE</legend>
            <?php foreach ($trace as $value) { ?>
                    <p><?php echo $value; ?></p>
            <?php } ?>
            <a class="_cls" href="javascript:;" onclick="this.parentNode.style.display='none'">CLOSE</a>
        </fieldset>
        <?php
    }

}
?>