<?php
/**
 * pdo for mysql
 +-----------------------------------------
 * @category    pt
 * @package     pt\framework\db\pdo
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework\db\pdo;


class mysql extends \pt\framework\db\pdo
{

    public function __construct($config = array()){}


    /**
     * get last insert id
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function getLastInsertId()
    {
        return $this -> _pdo -> lastInsertId();
    }



    /**
     * get columns
     +-----------------------------------------
     * @access public
     * @param string $table
     * @param string $prefix
     * @return void
     */
    public function getColumns($table, $prefix='')
    {
        $tablename = ($prefix ? $prefix : $this -> prefix).$table;
        $result = $this -> query("DESCRIBE `{$tablename}`");

        $columns = array();
        foreach ($result as $key => $val)
        {
            $val = array_change_key_case($val);

            // debug. I dont check in mysql for low version
            $name = $val['field'];

            $columns[$name]    =   array(
                'name'    => $name,
                'type'    => $this -> _columnsType($val['type']),
                'notnull' => (bool) ($val['null'] == 'NO'),
                'primary' => (bool) ($val['key'] == 'PRI'),
                'autoinc' => (bool) ($val['extra'] == 'auto_increment'),
            );
        }

        return $columns;
    }



    /**
     * get column type
     +-----------------------------------------
     * @access public
     * @param mixed $type
     * @return void
     */
    private function _columnsType($type)
    {
        if ( strpos($type, 'int') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false )
            return PDO::PARAM_INT;
        else if ( strpos($type, 'bool') !== false )
            return PDO::PARAM_BOOL;
        else if ( strpos($type, 'blob') !== false )
            return PDO::PARAM_LOB;

        return PDO::PARAM_STR;
    }



    /**
     * get tables info
     +-----------------------------------------
     * @access public
     * @return void
     */
    public function getTables()
    {
        $result = $this -> query('SHOW TABLES');
        $tables = array();
        foreach ($result as $key => $val)
        {
            $tables[$key] = current($val);
        }
        return $tables;
    }


}

