<?php
/**
 * Upload
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


class upload
{

    // maxsize
    public $max_size = 0;

    // allow mime type
    public $allow_mime_type = array();

    // allow extension
    public $allow_extension = array();

    // name file function
    public $name_rule = '';

    // upload file count
    public $file_upload_count = 0;

    // upload path
    private $save_path = '';

    // web url
    private $web_path = '';

    // error
    public $error = 0;


    /**
     * start upload
     +-----------------------------------------
     * @access public
     * @param string $path
     * @return void
     */
    public function save($path='/', $web_path='')
    {
        // check dir
        if(!is_dir($path))
        {
            $this -> error = 2404;
            return false;
        }
        else
        {
            if(!file::_is_writable($path))
            {
                $this -> error  =  2655;
                return false;
            }
        }

        $files_rs = array();

        $this -> save_path = $path;
        $this -> web_path  = $web_path;
        $this -> file_upload_count = 0;

        foreach ($_FILES as $name => $file)
        {
            $files_rs[$name] = array();

            if (is_array($file['name']))
            {
                $this -> multiple($file['name'], $file['type'], $file['tmp_name'], $file['error'], $file['size'], $files_rs[$name]);
            }
            else
            {
                $files_rs[$name] = $this -> _save($file);
            }
        }

        return $files_rs;
    }



    /**
     * multiple files upload
     * key like <input
     +-----------------------------------------
     * @access private
     * @param array $files
     * @param array $type
     * @param array $tmp
     * @param array $error
     * @param array $size
     * @return void
     */
    private function multiple($files, $type, $tmp, $error, $size, &$result)
    {
        foreach ($files as $k => $f)
        {
            if (is_array($f))
            {
                $result[$k] = array();
                $this -> multiple($f, $type[$k], $tmp[$k], $error[$k], $size[$k], $result[$k]);
            }
            else
            {
                $result[$k] = $this -> _save(array('name'=>$f, 'type'=>$type[$k], 'tmp_name'=>$tmp[$k], 'error'=>$error[$k], 'size'=>$size[$k]));
            }
        }
    }



    /**
     * save upload file
     +-----------------------------------------
     * @access private
     * @param mixed $file
     * @return void
     */
    private function _save($file)
    {
        static $max_size;

        // filter empty
        if(empty($file['name']))
            return false;

        $pathinfo = pathinfo($file['name']);

        // add meta data
        $file['extension']  =  $pathinfo['extension'];
        $file['savepath']   =  $this -> web_path ? $this -> web_path : $this -> save_path;
        $file['savename']   =  $this -> filename($file);

        // check size
        if ($max_size != $this -> max_size)
        {
            $unit = array('B'=>1, 'KB'=>1024, 'MB'=>1048576);
            $max_size = is_numeric($this -> max_size) ? $this -> max_size : intval($this -> max_size) * $unit[substr(strtoupper(trim($this -> max_size)), -2)];
        }

        if ($max_size && $file['size'] > $max_size)
            $file['error'] = 11;

        // check mime
        if ($this -> allow_mime_type && !in_array(strtolower($file['type']), $this -> allow_mime_type))
            $file['error'] = 12;

        // check ext
        if ($this -> allow_extension && !in_array(strtolower($file['extension']), $this -> allow_extension))
            $file['error'] = 13;

        // temp not uploaded
        if (!is_uploaded_file($file['tmp_name']))
            $file['error'] = 14;

        // file exist
        $i = 0;
        while (file_exists($this -> save_path.$file['savename']))
        {
            if( $i >= 10 )
            {
                $file['error'] = 15;
                break;
            }
            $file['savename'] = $this -> filename($file);
            $i ++;
        }

        if ($file['error'])
        {
            $file['savename'] = null;
            return $file;
        }

        if (!move_uploaded_file($file['tmp_name'], $this -> save_path.$file['savename']))
        {
            $file['error'] = 7;
            $file['savename'] = null;
            return $file;
        }

        unset($file['tmp_name']);
        $this -> file_upload_count ++;

        return $file;
    }



    /**
     * get new file name
     +-----------------------------------------
     * @access protected
     * @param mixed $file
     * @return void
     */
    protected function filename($file)
    {
        if(is_callable($this -> name_rule))
        {
            return call_user_func($this -> name_rule, $file).".".$file['extension'];
        }else {
            return date('Gis').substr(md5($file['name']), 0, 8).".".$file['extension'];
        }
    }



    /**
     * get error message
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function get_error($code=0)
    {
        $error = array(
            1    => __('This is larger than the maximum size. Please try another.'),
            2    => __('Upload file xceeds the maximum upload size for the multi-file uploader when used in your browser..'),
            3    => __('Upload stopped.'),
            4    => __('This file is empty. Please try another.'),
            6    => __('Failed to write request to temporary file.'),
            7    => __('Could not write file.'),
            11   => __('This is larger than the maximum size. Please try another.'),
            12   => __('This file type is not allowed. Please try another.'),
            13   => __('Invalid file type'),
            14   => __('Failed to read from temporary file.'),
            15   => __('File already exists!'),
            2404 => __('Destination directory does not exist.'),
            2655 => __('Destination directory is not writable'),
        );

        if (!$code)
            return $error[$this -> error];
        else
            return $error[$code];
    }


}
?>