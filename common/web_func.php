<?php

/**
 * format a array for a insert query string
 +-----------------------------------------
 * @param array $data
 * @param bool $multiple
 * @return string
 */
function insert_array($data, $multiple=false, $mukey='')
{
    if ($multiple || isset($data[0]))
    {
        $value = array();
        $sql = array();
        foreach ($data as $i => $v)
        {
            $rs = insert_array($v, false, $i+1);
            $value = array_merge($value, $rs['value']);
            $sql[] = $rs['sql'];
        }

        $key = array_keys($data[0]);
        $key = '(`'.implode('`,`', $key).'`)';

        return array('column'=>$key, 'sql'=>implode(',', $sql), 'value'=>$value);
    }
    else
    {
        $value = array();
        foreach ($data as $k => $v)
        {
            if($v === null)
            {
                $data[$k] = 'NULL';
            }
            else
            {
                $value[":{$k}{$mukey}"] = $v;
                $data[$k] = ":{$k}{$mukey}";
            }
        }

        $key = '';
        if ($mukey === '')
        {
            $key = array_keys($data);
            $key = '(`'.implode('`,`', $key).'`)';
        }

        return array('column'=>$key, 'sql'=>'('.implode(',', $data).')', 'value'=>$value);
    }
}


/**
 * format a array for a update query string
 +-----------------------------------------
 * @param array $data
 * @return string
 *
 * create sephiroth 2014-03-11
 */
function update_array($data)
{
    $value = array();
    foreach ($data as $k => $v)
    {
        if ( $v === null )
        {
            $data[$k] = "`{$k}` = NULL";
        }
        else
        {
            $value[":{$k}"] = $v;
            $data[$k] = "`{$k}` = :{$k}";
        }
    }

    return array('sql'=>implode(',', $data), 'value'=>$value);
}




?>