<?php
/**
 * page
 +-----------------------------------------
 * @category    pt
 * @package     pt\tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\tool;


class page
{
    protected $total;

    protected $var_name = 'page';

    public $rows;

    public $params = '';

    public $first_row = 0;

    protected $now_page = 1;

    public $other_pages_count = 5;


    public function __construct($total, $rows=15, $params=array(), $var_name='page')
    {
        $this -> total = (int)$total;
        $this -> rows  = !empty($rows) ? (int)$rows : 15;
        $this -> params = $params ? http_build_query($params).'&' : '';

        if ($var_name)
            $this -> var_name = $var_name;

        $this -> now_page = !empty($_GET[$var_name]) && ($_GET[$var_name] > 0) ? (int)$_GET[$var_name] : 1;

        $this -> first_row = $this -> rows * ($this -> now_page - 1);
    }


    /**
     * get mysql Limit
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function limit()
    {
        if(0 == $this -> total)
            return '0 , '.$this -> rows;

        $total_pages = ceil($this -> total / $this -> rows);
        if ($this -> now_page > $total_pages)
            $this -> now_page = $total_pages;

        $first_row = ($this -> now_page - 1) * $this -> rows;
        $this -> first_row = $first_row;

        return "{$first_row}, {$this->rows}";
    }



    /**
     * get page array
     +-----------------------------------------
     * @access public
     * @param bool $array
     * @return void
     */
    public function show()
    {
        $total_pages = ceil($this -> total / $this -> rows);
        if ($this -> now_page > $total_pages)
            $this -> now_page = $total_pages;

        $first_row = ($this -> now_page - 1) * $this -> rows;
        $this -> first_row = $first_row;

        if(strpos($_SERVER['REQUEST_URI'], "&{$this->var_name}=") === false && strpos($_SERVER['REQUEST_URI'], "?{$this->var_name}=") === false)
            $url  =  $_SERVER['REQUEST_URI'];
        else
            $url  =  preg_replace("/([&]|[?]){$this->var_name}=[0-9]+/", '', $_SERVER['REQUEST_URI']);

        $url = $url . (strpos($url, '?') ? '&' : '?') . $this -> params;

        $pageArray['url']   =   "{$url}{$this->var_name}=";
        $pageArray['rows']  =   $this -> total;
        $pageArray['prev']  =   $this -> now_page - 1 < 1 ? 1 : $this -> now_page - 1;;
        $pageArray['next']  =   $this -> now_page + 1 > $total_pages ? $total_pages : $this -> now_page + 1;
        $pageArray['total'] =   $total_pages;
        $pageArray['now']   =   $this -> now_page;
        return $pageArray;
    }

}
?>