<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 19.12.12
 * Time: 15:01
 * To change this template use File | Settings | File Templates.
 */
 
class exampleDocLister extends extDocLister{

    protected function run(){
        echo "Параметры";
        print_r($this->_cfg);
    }
}