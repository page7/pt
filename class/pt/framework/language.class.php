<?php
/**
 * language
 * translations by .po / .so file
 * need extension gettext
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class language extends base
{
    // lang
    public $default = 'en-US';

    // lang path
    public $path = '';

    // log send type
    protected $type = 3;

    // request key
    protected $language_var = '';



    // config
    public function __construct($config=array())
    {
        $this -> __config($config);
        $this -> set();

        $GLOBALS['_lang'] = $this;
    }



    // set language
    public function set($lang=null)
    {
        $var = $this -> language_var;

        if ($lang === null)
            $lang = isset($_GET[$var]) ? $_GET[$var] : (isset($_COOKIE[$var]) ? $_COOKIE[$var] : $this -> default);

        putenv("LANG={$lang}");
        setlocale(LC_ALL, str_replace('_', '-', $lang));

        setcookie($var, $lang, time() + 86400);
    }



    // get language
    public function get()
    {
        $var = $this -> language_var;
        $lang = isset($_GET[$var]) ? $_GET[$var] : (isset($_COOKIE[$var]) ? $_COOKIE[$var] : $this -> default);
        return $lang;
    }



    // set package (domain)
    static function package($name, $path=null)
    {
        if ($path !== null)
            bindtextdomain($name, $GLOBALS['_lang'] -> path.$path);

        textdomain($name);
    }



    // translate
    static function translate($key)
    {
        return gettext($key);
    }


}
