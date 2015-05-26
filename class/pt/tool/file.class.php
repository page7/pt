<?php
/**
 * file
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


class file
{

    /**
     * check a path / file is writable
     +-----------------------------------------
     * @access public
     * @param  string $path
     * @return void
     */
    static function is_writable($path)
    {
        // is not Windows, and not safe mode, use is_writable
        if (!strstr(PHP_OS, 'WIN') && @ini_get("safe_mode") == false)
            return is_writable($path);

        // is a dir, create a file
        if ($path{strlen($path)-1} == '/')
            return self::is_writable($path.uniqid(mt_rand()).'.tmp');

        if (file_exists($path))
        {
            if (!($f = @fopen($path, 'r+')))
                return false;
            fclose($f);
            return true;
        }

        if (!($f = @fopen($path, 'w')))
            return false;

        fclose($f);
        unlink($path);
        return true;
    }



    /**
     * a better mkdir
     +-----------------------------------------
     * @param string $dir
     * @param int $mode
     * @return void
     */
    static protected function mkdir($dir, $mode = 0755)
    {
        if (is_dir($dir) || @mkdir($dir, $mode))
        {
            if (config("web.build_dir_secure") && self::is_writable($dir) && !file_exists("{$dir}/index.html"))
                @file_put_contents("{$dir}/index.html", "");

            return true;
        }

        if (!self::mkdir(dirname($dir), $mode)) return false;

        return @mkdir($dir, $mode);
    }



    /**
     * mkdir for a deep path
     +-----------------------------------------
     * @param string $dir
     * @return void
     */
    static function mkdirs($dir)
    {
        if (is_dir($dir)) return true;

        $dir = explode('/', $dir);
        $temp = '';
        foreach ($dir as $value)
        {
            $temp .= $value.'/';
            if ($value=='.' || $value == '..' || !$value) continue;
            $return = self::mkdir($temp);
        }

        return $return;
    }


}