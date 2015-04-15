<?php namespace SimpleTab;

require_once (MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');

abstract class AbstractController {
    public $rfName = '';
    public $rid = 0;
    public $data = null;
    public $FS = null;
    public $isExit = false;
    public $output = null;
    public $params = null;

    protected $modx = null;

    public function __construct(\DocumentParser $modx){
        $this->FS = \Helpers\FS::getInstance();
        $this->modx = $modx;
        $this->params = $modx->event->params;
        $this->rid = isset($_REQUEST[$this->rfName]) ? (int)$_REQUEST[$this->rfName] : 0;
    }

    public function callExit(){
        if($this->isExit){
            echo $this->output;
            exit;
        }
    }

    public function remove()
    {
        $out = array();
        $ids = isset($_REQUEST['ids']) ? (string)$_REQUEST['ids'] : '';
        $ids = isset($_REQUEST['id']) ? (string)$_REQUEST['id'] : $ids;
        $out['success'] = false;
        if (!empty($ids)) {
            if ($this->data->deleteAll($ids, $this->rid)) {
                $out['success'] = true;
            }
        }
        return $out;
    }

    public function place()
    {
        $out = array();
        $ids = isset($_REQUEST['ids']) ? (string)$_REQUEST['ids'] : '';
        $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'top';
        $out['success'] = false;
        if (!empty($ids)) {
            if ($this->data->place($ids, $dir, $this->rid)) {
                $out['success'] = true;
            }
        }
        return $out;
    }

    public function reorder() 
    {
        $out = array();
        $source = $_REQUEST['source'];
        $target = $_REQUEST['target'];
        $point = $_REQUEST['point'];
        $orderDir = $_REQUEST['orderDir'];
        $rows = $this->data->reorder($source, $target, $point, $this->rid, $orderDir);

        if ($rows) {
            $out['success'] = true;
        } else {
            $out['success'] = false;
        }
        return $out;
    }

    public function listing() {
        if (!$this->rid) {
            $this->isExit = true;
            return;
        }
        $param = array(
            "controller" 	=> 	"onetable",
            "table" 		=> 	$this->data->tableName(),
            'idField' 		=> 	$this->data->fieldPKName(),
            "api" 			=> 	'1',
            "idType"		=>	"documents",
            'ignoreEmpty' 	=> 	"1",
            'JSONformat' 	=> 	"new"
        );
        $display = 10;
        $display = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : $display;
        $offset = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
        $offset = $offset ? $offset : 1;
        $offset = $display*abs($offset-1);

        $param['display'] = $display;
        $param['offset'] = $offset;

        if(isset($_REQUEST['sort'])){
            $sort = $_REQUEST['sort'];
            $param['sortBy'] = preg_replace('/[^A-Za-z0-9_\-]/', '', $sort);
            if(''==$param['sortBy']){
                unset($param['sortBy']);
            }
        }
        if(isset($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), array("ASC","DESC"))){
            $param['sortDir'] = $_REQUEST['order'];
        }
        $param['addWhereList'] = "`{$this->rfName}`={$this->rid}";
        $out = $this->modx->runSnippet("DocLister", $param);
        return $out;
    }

    public function getLanguageCode() {
        $manager_language = $this->modx->config['manager_language'];
        if(file_exists(MODX_MANAGER_PATH."includes/lang/".$manager_language.".inc.php")) {
            include_once MODX_MANAGER_PATH."includes/lang/".$manager_language.".inc.php";
        }
        return $modx_lang_attribute;
    }
}