<?php
/**
 * datebase for oracle
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework\db
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework\db;

use pt\framework\debug as debug;


class oracle extends \pt\framework\db
{
    // connect params
    protected $host = '';

    protected $port = 1251;

    protected $sid  = '';

    protected $persistent = false;

    protected $seq_suffix = '_ID';

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


    // Construct
    public function __construct($config = array())
    {
        if(!empty($config['host']))
            $this -> host = $config['host'];

        if(!empty($config['port']))
            $this -> port = $config['port'];

        if(!empty($config['seq_suffix']))
            $this -> seq_suffix = $config['seq_suffix'];

        $this -> sid = $config['database'];

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
        $sn = ($this -> host ? '//'.$this -> host.':'.$this -> port.'/' : '').$this -> sid;

        if (!$this -> persistent)
            $this -> _connect = oci_connect($this -> username, $this -> password, $sn, $this -> charset);
        else
            $this -> _connect = oci_pconnect($this -> username, $this -> password, $sn, $this -> charset);

        if ($this -> _connect)
        {
            debug::log("PDO: DB connection is active({$this -> dsn}).");
            $this -> _active = true;
        }
        else
        {
            $e = oci_error();
            error_handler($e['code'], $e['message'], __FILE__, __LINE__);
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

        $this -> _statement = @oci_parse($this -> _connect, $sql);
        if (!$this -> _statement)
        {
            $e = oci_error($this -> _connect);
            error_handler($e['code'], $e['message'], __FILE__, __LINE__);
        }

        $this -> _sql = $sql;
        $this -> _sql_method = oci_statement_type($this -> _statement);

        return $this;
    }



    /**
     * bindValue
     +-----------------------------------------
     * @access public
     * @param string $param
     * @param mixed $value
     * @param string/int $type
     * @return void
     */
    public function bindValue($param, $value, $type=SQLT_CHR)
    {
        oci_bind_by_name($this -> _statement, $param, $value, -1, $type);

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

        $res = oci_execute($this -> _statement, $this -> _trans_active ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS);

        if ($res)
        {
            if ($fetch)
            {
                if ($this -> _sql_method == 'SELECT')
                {
                    $row = oci_fetch_all($this -> _statement, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
                    return $result;
                }
                elseif ($this -> _sql_method == 'INSERT')
                {
                    return ($lastid = $this -> getLastInsertId()) ? $lastid : oci_num_rows($this -> _statement);
                }
                else
                {
                    return oci_num_rows($this -> _statement);
                }
            }
        }
        else
        {
            $e = oci_error($this -> _statement);
            error_handler($e['code'], $e['message'], __FILE__, __LINE__);
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
            return oci_commit($this -> _connect);
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
        return oci_rollback($this -> _connect);
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
        if( preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+\"?([a-z0-9\_\-]+)\"?/is", $this -> _sql, $tablename) )
        {
            // Gets this table's last  value
            $query = 'SELECT "' . $tablename[1] . $this -> seq_suffix . '".currval AS "last_value" FROM "' . $tablename[1] . '"';
            $stm =  oci_parse($this -> _connect, $query);

            $r = @oci_execute($stm, $this -> _trans_active ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS);
            if($r)
            {
                $rs = oci_fetch_array($stm, OCI_ASSOC);
                return ( $rs ) ? $rs['last_value'] : false;
            }
            return oci_num_rows($this -> _statement);
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

        oci_free_statement($this -> _statement);
        oci_close($this -> _connect);
        debug::log('PDO: DB connection close.');
    }


}
