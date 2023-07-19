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
    static $default = 'en';

    // lang path
    static $path = '';

    // request key
    static $language_var = '';

    // check extension
    static protected $extension = false;

    // package selected
    static protected $package = 'pt';


    // config
    public function __construct($config=array())
    {
        $this -> __config($config);

        if (extension_loaded('gettext'))
        {
            self::$extension = true;
        }
        self::set();
    }



    // set language
    public static function set($lang=null)
    {
        $var = self::$language_var;

        if ($lang === null)
            $lang = isset($_GET[$var]) ? $_GET[$var] : (isset($_COOKIE[$var]) ? $_COOKIE[$var] : self::$default);

        putenv("LC_ALL={$lang}");

        putenv("LANG={$lang}");
        setlocale(LC_ALL, $lang);

        setcookie($var, $lang, time() + 86400);
    }



    // get language
    public static function get()
    {
        $var = self::$language_var;
        $lang = isset($_GET[$var]) ? $_GET[$var] : (isset($_COOKIE[$var]) ? $_COOKIE[$var] : self::$default);
        return $lang;
    }



    // set package (domain)
    public static function package($name, $path=null)
    {
        if (self::$extension)
        {
            if (DEBUG)
                $name = filter::apply('pt\framework\language:debug', $name, self::$path.$path);

            if ($path !== null)
            {
                bindtextdomain($name, self::$path.$path);
                bind_textdomain_codeset($name, 'UTF-8');
            }

            textdomain($name);
        }

        event::trigger('pt\framework\language:package', $name);
        self::$package = $name;
    }



    // translate
    public static function translate($key, $package=null)
    {
        if (!config('web.i18n'))
        {
            return $key;
        }
        else if (self::$extension)
        {
            if ($package)
                textdomain($package);

            // Gettext be support by extension in php.ini
            // and server-side os must install language package.
            $trans = gettext($key);

            if ($package)
                textdomain(self::$package);

            return $trans;
        }
        else
        {
            // if not config the extension, you can use a third-party support.
            // like : https://github.com/dsp/PHP-Gettext
            return filter::apply('pt\framework\language:untrans', $key, $package);
        }
    }




}
