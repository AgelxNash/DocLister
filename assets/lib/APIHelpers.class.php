<?php

class APIhelpers
{
    public function emailValidate($email, $dns = true)
    {
        return \DLHelper::emailValidate($email, $dns);
    }
    public function genPass($len, $data = '')
    {
        return \DLHelper::genPass($len, $data);
    }
    public function getUserIP($out = '127.0.0.1')
    {
        return \DLHelper::getUserIP($out);
    }

    public function sanitarTag($data)
    {
        return \DLHelper::sanitarTag($data);
    }

    public function checkString($value, $minLen = 1, $alph = array(), $mixArray = array(), $debug = false)
    {
        return \DLHelper::checkString($value, $minLen, $alph, $mixArray, $debug);
    }
}