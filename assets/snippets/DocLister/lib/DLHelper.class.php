<?php

 class DLHelper{
     /**
      * @param  mixed  $data
      * @param string $key
      * @param mixed $default null
      * @return mixed
      */
     public static function getkey($data, $key, $default = null)
     {
         $out = $default;
         if(is_array($data) && array_key_exists($key, $data)){
             $out = $data[$key];
         }
         return $out;
     }
 }