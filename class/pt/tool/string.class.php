<?php
/**
 * string
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


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
        switch ($type) {
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

        if ($len > 10)
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
    static function text($string, $length=0, $more='')
    {
        $str = trim(strip_tags($string), " ¡¡\t\n\r");

        if ($length)
        {
            if (function_exists("mb_substr"))
            {
                if (mb_strlen($str, 'utf-8') > $length)
                    return mb_substr($str, 0, $length, 'utf-8').$more;
            }
            else
            {
                if (iconv_strlen($str, 'utf-8') > $length)
                    return iconv_substr($str, 0, $length, 'utf-8').$more;
            }
        }

        return $str;
    }


    /**
     * charset covert
     +-----------------------------------------
     * @access public
     * @param string $content
     * @param string $from
     * @param string $to
     * @return void
     */
    static function charset_convert($content, $from='gbk', $to='utf-8')
    {
        if (function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($content, $to, $from);
        }
        elseif (function_exists('iconv'))
        {
            return iconv($from, $to, $content);
        }
        else
        {
            return $content;
        }
    }


    /**
     * get a deep path of id or hash
     +-----------------------------------------
     * @param string $str
     * @param int $split_length
     * @param int $length
     * @param string $pad_string
     * @param int $pad_type
     * @return string
     */
    static function pad_split($str, $split_length = 3, $length = 9, $pad_string = '0', $pad_type = STR_PAD_LEFT)
    {
        $string = str_pad($str, $length, $pad_string, $pad_type);
        $dirs = str_split($string, $split_length);
        return implode('/', $dirs);
    }


}
?>
