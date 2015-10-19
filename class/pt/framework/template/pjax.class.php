<?php
// This is a Experimental product

namespace pt\framework\template;

class pjax extends \pt\framework\template
{
    // wrap template order
    static protected $wraps = array();

    // wrap now template
    static protected $used = '';


    public function __construct($config=array())
    {
        eval("
        if(!function_exists('tpl_wrap'))
        {
            function tpl_wrap()
            {
                \\pt\\framework\\template::wrap();
            }

            function tpl_unpjax(\$wrap, \$vars=array())
            {
                \\pt\\framework\\template::unpjax(\$wrap, \$vars);
            }

            \$template = new \\pt\\framework\\template();
            \$template -> extend('pjax');

            if (IS_AJAX && \$_SERVER['HTTP_X_PJAX'] == 'true')
                define('IS_PJAX', true);
            else
                define('IS_PJAX', false);
        }");
    }


    // include template without pjax
    static public function unpjax($wrap_tmpl, $vars=array())
    {
        $trace = debug_backtrace();
        foreach ($trace as $v)
            if ($v['function'] == 'include' && strpos($v['file'], 'pt\framework\template'))
                break;
            else if (!empty($v['file']))
                $tmpl  = $v['file'];

        if (IS_AJAX)
        {
            ob_start();
            ob_implicit_flush(0);

            $echo = function(){
                $content = ob_get_clean();
                header('PJAX-content-length:'. strlen($content));
                echo $content;
            };

            \pt\framework\event::bind('pt\framework\template:display', $echo, 2);
            return;
        }

        if ($tmpl == self::$used) return;

        self::$wraps[] = $tmpl;

        self::assign($vars);

        self::display($wrap_tmpl);
        exit;
    }


    // after wrap be included
    // content template will be include again
    static public function wrap()
    {
        $tmpl = array_pop(self::$wraps);
        self::$used = $tmpl;

        extract(self::$vars, EXTR_OVERWRITE | EXTR_REFS );

        include $tmpl;
    }

}