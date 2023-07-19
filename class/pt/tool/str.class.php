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


class str
{

    // validation type
    static $regex = array(
        'require'   => '/.+/',
        'email'     => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'url'       => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
        'currency'  => '/^\d+(\.\d+)?$/',
        'number'    => '/\d+$/',
        'integer'   => '/^[-\+]?\d+$/',
        'double'    => '/^[-\+]?\d+(\.\d+)?$/',
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
    static function random($len=6, $type='', $addChars='')
    {
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
     * validation
     +-----------------------------------------
     * @access public
     * @param string $value
     * @param string $regex
     * @return void
     */
    static function check($value, $regex)
    {
        $matchRegex = isset(self::$regex[$regex]) ? self::$regex[$regex] : $regex;
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
