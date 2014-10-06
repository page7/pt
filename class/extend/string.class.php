<?php
/**
 * string
 +-----------------------------------------
 * @category
 * @package string
 * @author page7 <zhounan0120@gmail.com>
 * @version $Id$
 */
class string
{

    // validation type
    static $regex = array(
            'username'=>'/^[a-zA-Z0-9_]\w{4}/',
            'password'=>'/^\w{6}/',
            'require'=> '/.+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'phone' => '/^((\(\d{2,5}\))|(\d{3,5}\-))?(\(\d{1,4}\)|\d{1,4}-)?[1-9]\d{5,10}(\-\d{1,4})?$/',
            'mobile' => '/^((\(\d{2,5}\))|(\d{3,5}\-))?(13|14|15|18)\d{9}$/',
            'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/\d+$/',
            'zip' => '/^[1-9]\d{5}$/',
            'qq' => '/^[1-9]\d{4,13}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
    );

    /**
     * get some random char
     +-----------------------------------------
     * @access public
     * @param int $len
     * @param string $type
     * @param string $addChars
     * @return void
     */
    static function rand_string($len=6, $type='', $addChars='') {
        $str ='';
        switch($type) {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 1:
                $chars = '0123456789';
                break;
            case 2:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 4:
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZ23456789'.$addChars;
                break;
            default :
                // remove some analogous chars, like : oOLl01
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
                break;
        }

        if ( $len > 10 )
        {
            $chars = $type==1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }

        $chars   =   str_shuffle($chars);
        $str     =   substr($chars, 0, $len);

        return $str;
    }

    /**
     * get a verify chars
     +-----------------------------------------
     * @access public
     * @param int $length
     * @param int $mode
     * @return void
     */
    static function build_verify($length=4, $mode=1)
    {
        return self::rand_string($length, $mode);
    }


    /**
     * validation
     +-----------------------------------------
     * @access public
     * @param mixed $value
     * @param mixed $checkName
     * @return void
     */
    static function check($value, $type)
    {
        $matchRegex = self::$regex[$type];
        return preg_match($matchRegex, trim($value));
    }


    /**
     * remove html and space
     +-----------------------------------------
     * @access public
     * @param mixed $string
     * @param int $length
     * @return void
     */
    static function text($string, $length=0)
    {
        $str = trim(strip_tags($string), " ¡¡\t\n\r");

        if ($length)
        {
            if(function_exists("mb_substr"))
                return mb_substr($str, 0, $length, 'utf-8');
            else
                return iconv_substr($str, 0, $length, 'utf-8');
        }

        return $str;
    }

}
?>