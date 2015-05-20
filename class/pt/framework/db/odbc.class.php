<?php
/**
 * database for ODBC
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework\db
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework\db;

use pt\framework\debug as debug;


class odbc extends \pt\framework\db
{
    // connect params
    protected $host = '';

    protected $port = 1433;

    protected $datebase  = '';

    protected $persistent = false;

    public $last_insert_id_call = null;

    // connect status
    protected $_active = false;

    // connect resource
    protected $_connect;

    // statement resource
    protected $_statement;

    // trans status
    protected $_trans_active = 0;

    // record query method type
    protected $_sql_method = 'SELECT';

    // record sql
    protected $_sql = '';

    // bind value
    protected $_bind_values = array();


    // Construct
    public function __construct($config = array())
    {
        if(!empty($config['host']))
            $this -> host = $config['host'];

        if(!empty($config['port']))
            $this -> port = $config['port'];

        $this -> datebase = $config['database'];

        if (!empty($config['persistent']))
            $this -> persistent = true;
    }

    /**
     * connect
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function connect()
    {
        $sn = substr($this -> dsn, strlen('odbc://'));

        if (!$this -> persistent)
            $this -> _connect = odbc_connect($sn, $this -> username, $this -> password);
        else
            $this -> _connect = odbc_pconnect($sn, $this -> username, $this -> password);

        if ($this -> _connect)
        {
            debug::log("PDO: DB connection is active({$this -> dsn}).");
            $this -> _active = true;
        }
        else
        {
            $ecode = odbc_error();
            $emessage = odbc_errormsg();
            error_handler($ecode, $emessage, __FILE__, __LINE__);
        }

        return $this -> _connect;
    }


    /**
     * prepare
     +-----------------------------------------
     * @access public
     * @param mixed $sql
     * @return void
     */
    public function prepare($sql)
    {
        $sql = str_replace(array(PHP_EOL, "\t"), ' ', trim((string)$sql));

        $this -> _statement = @odbc_prepare($this -> _connect, $sql);
        if (!$this -> _statement)
        {
            $ecode = odbc_error();
            $emessage = odbc_errormsg();
            error_handler($ecode, $emessage, __FILE__, __LINE__);
        }

        $this -> _sql = $sql;
        $this -> _bindvalue = array();

        if (stripos($sql, 'SELECT') === 0)
            $this -> _sql_method = 'SELECT';
        else (stripos($sql, 'UPDATE') === 0)
            $this -> _sql_method = 'UPDATE';
        else (stripos($sql, 'INSERT') === 0)
            $this -> _sql_method = 'INSERT';

        return $this;
    }



    /**
     * bindValue
     +-----------------------------------------
     * @access public
     * @param int $param
     * @param mixed $value
     * @param string/int $type
     * @return void
     */
    public function bindValue($param, $value, $type=SQLT_CHR)
    {
        $this -> _bind_values[$param] = $value;

        return $this;
    }



    /**
     * execute
     +-----------------------------------------
     * @access public
     * @param array $parameters
     * @return void
     */
    public function execute($parameters = array(), $fetch = true)
    {
        foreach ($parameters as $param => $value)
        {
            $this -> bindValue($param, $value);
        }

        $res = odbc_execute($this -> _statement, $this -> _bind_values);

        if ($res)
        {
            if ($fetch)
            {
                if ($this -> _sql_method == 'SELECT')
                {
                    $result = array();
                    while (odbc_fetch_row($this -> _statement))
                    {
                        $ar = array();
                        for ($j = 1; $j <= odbc_num_fields($this -> _statement); $j++)
                        {
                            $field_name = odbc_field_name($this -> _statement, $j);
                            $ar[$field_name] = odbc_result($this -> _statement, $field_name);
                        }
                        $result[] = $ar;
                    }
                    return $result;
                }
                elseif ($this -> _sql_method == 'INSERT')
                {
                    return $lastid = $this -> getLastInsertId() ? $lastid : odbc_num_rows($this -> _statement);
                }
                else
                {
                    return odbc_num_rows($this -> _statement);
                }
            }
        }
        else
        {
            $ecode = odbc_error();
            $emessage = odbc_errormsg();
            error_handler($ecode, $emessage, __FILE__, __LINE__);
            return false;
        }

        return $this;
    }


    /**
     * query
     * use inside class
     * like : SHOW [table][column]
     +-----------------------------------------
     * @access protected
     * @param mixed $sql
     * @return void
     */
    protected function query($sql)
    {
        return $this -> prepare($sql) -> execute();
    }



    /**
     * get columns type
     +-----------------------------------------
     * @access protected
     * @param string $type
     * @return void
     */
    protected function columnsType($type)
    {
        $map = array
        (
            'bool'     => SQLT_INT,
            'int'      => SQLT_INT,
            'string'   => SQLT_CHR,
            'resource' => SQLT_BLOB,
            'NULL'     => SQLT_CHR,
        );

        return isset($map[$type]) ? $map[$type] : SQLT_CHR;
    }



    /**
     * beginTrans
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function beginTrans()
    {
        return $this -> _trans_active += 1;
    }



    /**
     * commit
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function commit()
    {
        $this -> _trans_active -= 1;

        if($this -> _trans_active == 0)
            return odbc_commit($this -> _connect);
        else
            return true;
    }



    /**
     * rollback
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function rollback()
    {
        return odbc_rollback($this -> _connect);
    }




    /**
     * inTrans
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function inTrans()
    {
        return $this -> _trans_active > 0;
    }



    /**
     * get last insert id
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function getLastInsertId()
    {
        if( $this -> last_insert_id_call )
        {
            return call_user_func_array($this -> last_insert_id_call, array($this -> _statement, $this -> _connect));
        }
        else
        {
            return odbc_num_rows($this -> _statement);
        }
        return false;
    }




    /**
     * close
     +-----------------------------------------
     * @access protected
     * @return void
     */
    public function close()
    {
        $this -> _active = false;

        odbc_free_result($this -> _statement);
        odbc_close($this -> _connect);
        debug::log('PDO: DB connection close.');
    }


}

