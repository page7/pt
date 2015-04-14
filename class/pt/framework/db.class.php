<?php
/**
 * datebase
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


class db extends base
{
    // dsn
    protected $dsn = '';

    // charset
    protected $charset = 'utf8';

    // username
    protected $username = '';

    // password
    protected $password = '';

    // prefix
    protected $prefix = '';


    /**
     * Construct
     +-----------------------------------------
     * @access public
     * @param array $config
     */
    public function __construct($config=array())
    {
        $this -> __config($config);

        if (empty($config['dsn']))
            trigger_error('invalid config: db', E_USER_ERROR);

        if (strpos($this -> dsn, '://') === false)
        {
            // pdo link
            if(isset($this -> _children))
                $this -> __ext('pdo', $config);
        }
        else
        {
            if (($dns = @parse_url($config['dsn'])) === FALSE)
                trigger_error('invalid pdo dsn:'.$config['dsn'], E_USER_ERROR);

            $_ext = array(
                'host'	    => (isset($dns['host'])) ? rawurldecode($dns['host']) : '',
                'port'	    => (isset($dns['port'])) ? rawurldecode($dns['port']) : '',
                'database'	=> (isset($dns['path'])) ? rawurldecode(substr($dns['path'], 1)) : ''
            );

            $config = array_merge($config, $_ext);
            $this -> __ext($dns['scheme'], $config);
        }

        $this -> connect();
    }


}