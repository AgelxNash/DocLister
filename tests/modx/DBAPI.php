<?php

class DBAPI
{
    public function query($sql)
    {
        return false;
    }

    public function makeArray($result, $index = false)
    {
        return array();
    }

    public function getRow($result, $mode = 'assoc')
    {
        return array();
    }

    public function escape($data)
    {
        return $data;
    }

    public function getValue($result)
    {
        return null;
    }
}
