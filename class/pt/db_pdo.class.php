<?php
/**
 * PDO
 +-----------------------------------------
 * @category    Pt
 * @package     db_pdo
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */
class db_pdo extends db
{
    // PDO
    protected $_pdo = null;

    // connect status
    protected $_active = false;

    // PDOStatement
    protected $_statement = null;

    // trans status
    protected $_trans_active = false;   // useless. PDO have inTransaction.

    // persistent connect
    protected $persistent = false;

    // driver type
    protected $_driver_type = '';

    // record query method type
    protected $_sql_method = '';


    // Construct
    public function __construct($config = array())
    {
        if (!empty($config['PDO::ATTR_PERSISTENT']))
            $this -> persistent = true;
    }


    // __call rewrite
    public function _call($method, $args=array())
    {
        if (isset($this -> _child_methods[$method]))
        {
            $class = $this -> _child_methods[$method];
            $object = &$this -> _children[$class];
            return call_user_func_array(array($object, $method), $args);
        }
    }



    /**
     * connect
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function connect()
    {
        if (!$this -> pdo)
        {
            $params = array();

            // persistent
            if ($this -> persistent)
                $params[PDO::ATTR_PERSISTENT] = true;

            $params[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

            try
            {
                $dsn = $this -> dsn . ((version_compare(PHP_VERSION, '5.3.6') >= 0) ? ";charset={$this -> charset}" : '');
                $this -> _pdo = new PDO($dsn, $this->username, $this->password, $params);
                trace("PDO: DB connection is active({$this -> dsn}).");
            }
            catch (PDOException $e)
            {
                _exception::append($e);
            }

            $this -> _pdo -> exec('SET NAMES '.$this -> _pdo -> quote($this -> charset));
            $this -> _active = true;
        }

        // extend driver method
        $this -> _driver_type = $this -> getAttribute(PDO::ATTR_DRIVER_NAME);

        $this -> __ext('db_pdo_'.strtolower($this -> _driver_type));

        return $this -> _pdo;
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
        if ($sql instanceof sql)
        {
            // 添加表前缀
            if ($sql -> prefix === '')
                $sql -> prefix = $this -> prefix;

            // 设定解析类
            $sql -> parseClass = $this;

            $this -> _sql_method = $sql -> _method;
            $sql = (string)$sql;
        }
        else
        {
            if (DEBUG)
                trace('PDO: There is a risk that the query with a string SQL: <code>'.str_replace(array(PHP_EOL, "\t"), ' ', $sql).'</code>');

            $sql = str_replace(array(PHP_EOL, "\t"), ' ', trim((string)$sql));

            // Not Support : SELECT .. INTO ..
            if (stripos(trim((string)$sql), 'SELECT') === 0)
                $this -> _sql_method = 'SELECT';
            else if (stripos(trim((string)$sql), 'INSERT') === 0)
                $this -> _sql_method = 'INSERT';
            else if (stripos(trim((string)$sql), 'REPLACE') === 0)
                $this -> _sql_method = 'UPDATE';
            else if (stripos(trim((string)$sql), 'UPDATE') === 0)
                $this -> _sql_method = 'UPDATE';
            else if (stripos(trim((string)$sql), 'DELETE') === 0)
                $this -> _sql_method = 'DELETE';
        }

        if (!empty($this -> _statement)) $this -> _statement = null;

        $this -> _statement = $this -> _pdo -> prepare((string)$sql);

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
    public function bindValue($param, $value, $type=null)
    {
        $this -> _statement -> bindValue($param, $value, (int)$type);

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
            $this -> bindValue($param, $value, PDO::PARAM_STR);
        }

        try
        {
            $this -> _statement -> execute();

            if ($fetch)
            {
                if ($this -> _sql_method == 'SELECT')
                {
                    return $this -> _statement -> fetchAll(PDO::FETCH_ASSOC);
                }
                elseif ($this -> _sql_method == 'INSERT')
                {
                    if($this -> _statement -> rowCount())
                        return $this -> getLastInsertId();
                    else
                        return false;
                }
                else
                {
                    return $this -> _statement -> rowCount();
                }
            }

            return $this -> _statement;
        }
        catch (Exception $e)
        {
            _exception::append($e);
            return false;
        }
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
        try
        {
            $statement -> $this -> _pdo -> query($sql);
            return  $statement -> fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $e)
        {
            _exception::append($e);
            return array();
        }
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
            'bool'    => PDO::PARAM_BOOL,
            'int'     => PDO::PARAM_INT,
            'string'  => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB,
            'NULL'     => PDO::PARAM_NULL,
        );

        return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
    }



    /**
     * beginTrans
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function beginTrans()
    {
        return $this -> _pdo -> beginTransaction();
    }



    /**
     * commit
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function commit()
    {
        return $this -> _pdo -> commit();
    }



    /**
     * rollback
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function rollback()
    {
        return $this -> _pdo -> rollBack();
    }


    /**
     * inTrans
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function inTrans()
    {
        return $this -> _pdo -> inTransaction();
    }



    /**
     * getAttribute
     *      only in PDO
     +-----------------------------------------
     * @access protected
     * @param int $name
     * @return void
     */
    public function getAttribute($name)
    {
        return $this -> _pdo -> getAttribute($name);
    }



    /**
     * setAttribute
     *      only in PDO
     +-----------------------------------------
     * @access public
     * @param int $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute($name, $value)
    {
        return $this -> _pdo -> setAttribute($name, $value);
    }



    /**
     * close
     +-----------------------------------------
     * @access protected
     * @return void
     */
    public function close()
    {
        $this->_pdo = null;
        $this->_active = false;
        trace('PDO: DB connection close.');
    }


}
?>