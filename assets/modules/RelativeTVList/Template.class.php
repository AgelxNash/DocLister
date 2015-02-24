<?php namespace DLCity;

class Template extends \Module\Template
{
    public function Lists()
    {
        $out = '';
        $this->_modx->documentIdentifier = '1';
        $this->_modx->config['site_url'] = MODX_MANAGER_URL;
        $data = array();
        switch (Helper::getMode()) {
            case 'street':
            {
                $dataID = (int)self::getParam('dataID', $_GET);
                $data = array('table' => 'street',
                    'addWhereList' => 'parent_id=' . $dataID,
                    'tpl' => '@CODE: ' . $this->showBody('table/StreetBody'),
                    'ownerTPL' => '@CODE: ' . $this->showBody('table/StreetTable')
                );
                break;
            }
            case 'city':
            {
                $data = array('table' => 'city',
                    'tpl' => '@CODE: ' . $this->showBody('table/CityBody'),
                    'ownerTPL' => '@CODE: ' . $this->showBody('table/CityTable')
                );
                break;
            }
        }
        if (!empty($data)) {
            $out = $this->_modx->runSnippet('DocLister', array_merge(array(
                'controller' => 'onetable',
                'sortBy' => 'name',
                'sortDir' => 'ASC',
                'altItemClass' => 'gridAltItem',
                'itemClass' => 'gridItem',
                'display' => '10',
                'id' => 'dl',
                'noneTPL' => '@CODE: База пуста',
                'noneWrapOuter' => 0,
                'paginate' => 'pages',
                'prepare' => function (array $data = array(), \DocumentParser $modx, \onetableDocLister $_DocLister) {
                        if ($_DocLister->getCFGDef('table') == 'city') {
                            $q = $modx->db->query("SELECT count(id) FROM " . $_DocLister->getTable('street') . " WHERE parent_id='" . (int)$data['id'] . "'");
                            $data['total'] = $modx->db->getValue($q);
                            $q = $modx->db->query("SELECT count(id) FROM " . $_DocLister->getTable('street') . " WHERE parent_id='" . (int)$data['id'] . "' AND `hide`='0'");
                            $data['show'] = $modx->db->getValue($q);
                        }
                        $data['action'] = $data['hide'] ? 'add' : 'stop';

                        $data['class'] = (isset($data['iteration']) && $data['iteration'] % 2) ? $_DocLister->getCFGDef('itemClass') : $_DocLister->getCFGDef('altItemClass');
                        return $data;
                    },
                'idType' => 'documents',
                'ignoreEmpty' => 1
            ), $data));
            $out .= $this->_modx->getPlaceholder('dl.pages');
        }
        return $out;
    }
}