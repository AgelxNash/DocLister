<?php
/**
 * DocLister abstract extender class
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 09.03.2012
 * @version 1.0.1
 *
 */
abstract class extDocLister
{
    /*
     * Объект унаследованный от абстрактоного класса DocLister
     * @var DocLister
     * @access protected
     */
    protected $DocLister;

    /*
     * Объект DocumentParser - основной класс MODX
     * @var DocumentParser
     * @access protected
     */
    protected $modx;

    /*
     * Массив параметров экстендера полученных при инициализации класса
     * @var array
     * @access protected
     */
    protected $_cfg = array();

    /*
    * @TODO description extDocLister::run();
    */
    abstract protected function run();

    public function __construct($DocLister){
        if ($DocLister instanceof DocLister) {
            $this->DocLister = $DocLister;
            $this->modx = $this->DocLister->getMODX();
        }
    }
    /*
    * @TODO description extDocLister::init();
    */
    final public function init($DocLister)
    {
        $flag = false;
        if ($DocLister instanceof DocLister) {
            $this->DocLister = $DocLister;
            $this->modx = $this->DocLister->getMODX();
            $this->checkParam(func_get_args());
            $flag = $this->run();
        }
        return $flag;
    }

    /*
    * @TODO description extDocLister::checkParam();
    */
    final protected function checkParam($args)
    {
        if (isset($args[1])) {
            $this->_cfg = $args[1];
        }
    }

    /*
    * @TODO description extDocLister::getCFGDef();
    */
    final protected function getCFGDef($name, $def)
    {
        return isset($this->_cfg[$name]) ? $this->_cfg[$name] : $def;
    }
}