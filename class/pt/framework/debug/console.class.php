<?php
/**
 * debug by browser's console
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework\debug;


class console extends \pt\framework\debug
{

    // construct
    public function __construct($config=array()) {}


    /**
     * show the debug msg
     +-----------------------------------------
     * @access public
     * @return void
     */
    static function show()
    {
        $trace = self::log();

        echo '<script>';

        echo "console.log('%cpt-framework debug message', 'font-size:16px; font-weight:700; color:#C0C0C0;');\n";
        foreach ($trace as $value)
        {
            if (is_string($value['message']))
                $message = '\''.str_replace(array("\r\n", "\n"), '\n', addslashes($value['message'])).'\'';
            else
                $message = json_encode($value['message']);


            switch ($value['type'])
            {
                case 'Error':
                    echo "console.error({$message});\n";
                    break;

                case 'Warning':
                    echo "console.warn({$message});\n";
                    break;

                case 'Debug':
                    echo "console.log({$message});\n";
                    break;

                default:
                    if ($value['type'] && $message[0] == '\'')
                        echo "console.log('%c".substr($message, 1, -1)."', '{$value['type']}');\n";
                    else
                        echo "console.log({$message});\n";
            }
        }

        echo '</script>';
    }




}