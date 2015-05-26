<?php
/**
 * a empty class
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework\db\pdo
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework\db\pdo;


class blank extends \pt\framework\db\pdo
{

    public function __construct($config = array()){}


    public function __call($method, $args)
    {
        return $this;
    }

}