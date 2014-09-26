<?php namespace RedirectMap;

class Action
{
    protected static $modx = null;
    public static $TPL = null;
    protected static $_tplObj = null;
    const TABLE = "redirect_map";

    public static function init(\DocumentParser $modx, Template $tpl)
    {
        self::$modx = $modx;
        self::$_tplObj = $tpl;
        self::$TPL = Template::showLog();
    }

    protected static function _checkObj($id)
    {
        $q = self::$modx->db->select('id', self::$modx->getFullTableName(self::TABLE), "id = " . $id);
        return (self::$modx->db->getRecordCount($q) == 1);
    }

    protected static function _getValue($field, $id)
    {
        $q = self::$modx->db->select($field, self::$modx->getFullTableName(self::TABLE), "id = " . $id);
        return self::$modx->db->getValue($q);
    }

    protected static function _workValue($callback, $data = null)
    {
        self::$TPL = 'ajax/getValue';
        if (is_null($data)) {
            $data = Helper::jeditable('data');
        }
        $out = array();
        if (!empty($data)) {
            $modSEO = new modRedirectMap(self::$modx);
            $modSEO->edit($data['id']);
            if ($modSEO->getID() && ((is_object($callback) && ($callback instanceof \Closure)) || is_callable($callback))) {
                $out = call_user_func($callback, $data, $modSEO);
            }
        }
        return $out;
    }

    public static function saveValue()
    {
        return self::_workValue(function ($data, $modAR) {
            $out = array();
            if (isset($_POST['value']) && is_scalar($_POST['value'])) {
                $modAR->set($data['key'], $_POST['value'])->save();

                $insert = Action::checkPageID($modAR->get('uri'), $modAR->get('page'));
                if ($modAR->fromArray($insert)->save()) {
                    $out['value'] = $modAR->get($data['key']);
                }
            }
            return $out;
        });
    }

    public static function getValue()
    {
        return self::_workValue(function ($data, $modSEO) {
            return array(
                'value' => $modSEO->get($data['key'])
            );
        });
    }

    public static function checkPageID($uri, $page, $active = 1)
    {
        $modx = self::$modx;
        $insert = array(
            'page' => $page,
            'uri' => $uri,
            'active' => $active
        );
        $selfID = $modx->runSnippet('getPageID', array('uri' => $insert['uri']));
        $insert['active'] = (!empty($insert['page']));
        if (!empty($selfID)) {
            $insert['active'] = 0;
            $insert['page'] = $selfID;
        }
        return $insert;
    }

    public static function addUri()
    {
        $out = array();
        if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['page']) && !empty($_POST['uri'])) {
            $modRedirect = new modRedirectMap(self::$modx);
            $insert = array(
                'page' => $_POST['page'],
                'uri' => $_POST['uri']
            );
            $insert = Action::checkPageID($insert['uri'], $insert['page']);
            $flag = $modRedirect->create($insert)->save();
            if ($flag) {
                $out['log'] = 'Добавлено новое правило';
            } else {
                $out['uriField'] = $_POST['uri'];
                $out['pageField'] = $_POST['page'];
                $log = $modRedirect->getLog();
                if (isset($log['UniqueUri'])) {
                    $out['log'] = 'Правило для заданного URI уже есть в базе';
                } else {
                    $out['log'] = 'Во время добавления нового правила произошла ошибка';
                }
            }
        } else {
            $out['log'] = 'Не удалось получить данные для нового правила';
        }
        return $out;
    }

    public static function checkUniq()
    {
        return self::_workValue(function ($data, $modSEO) {
            $out = array();
            if (isset($_POST['value']) && is_scalar($_POST['value'])) {
                if ($modSEO->isUniq($_POST['value'])) {
                    $out['value'] = 'true';
                } else {
                    $out['value'] = 'Вы пытаетесь сохранить правило которое уже есть в базе. Удалите эту запись если она лишная.';
                }
            } else {
                $out['value'] = 'Не установлено значение';
            }
            return $out;
        });
    }

    public static function fullRequest()
    {
        $data = array();
        $dataID = (int)Template::getParam('docId', $_GET);
        if ($dataID > 0 && self::_checkObj($dataID)) {
            $oldValue = self::_getValue('full_request', $dataID);
            self::$modx->db->update(array(
                    'full_request' => !$oldValue
                ), self::$modx->getFullTableName(self::TABLE), "id = " . $dataID);
            $data['log'] = $oldValue ? 'Для правила с ID ' . $dataID . ' отключен поиск с учетом GET параметров' : 'Для правила с ID ' . $dataID . ' активирован поиск без учета GET параметров';
        } else {
            $data['log'] = 'Не удалось определить обновляемое правило';
        }
        return $data;
    }

    public static function saveGet()
    {
        $data = array();
        $dataID = (int)Template::getParam('docId', $_GET);
        if ($dataID > 0 && self::_checkObj($dataID)) {
            $oldValue = self::_getValue('save_get', $dataID);
            self::$modx->db->update(array(
                    'save_get' => !$oldValue
                ), self::$modx->getFullTableName(self::TABLE), "id = " . $dataID);
            $data['log'] = $oldValue ? 'Для правила с ID ' . $dataID . ' отключено сохранение GET параметров' : 'Для правила с ID ' . $dataID . ' активировано сохранение GET параметров';
        } else {
            $data['log'] = 'Не удалось определить обновляемое правило';
        }
        return $data;
    }

    public static function isactive()
    {
        $data = array();
        $dataID = (int)Template::getParam('docId', $_GET);
        if ($dataID > 0 && self::_checkObj($dataID)) {
            $oldValue = self::_getValue('active', $dataID);
            if (self::_getValue('page', $dataID) > 0) {
                $q = self::$modx->db->update(array(
                        'active' => !$oldValue
                    ), self::$modx->getFullTableName(self::TABLE), "id = " . $dataID);
            } else {
                $q = false;
            }
            if ($q) {
                $data['log'] = $oldValue ? 'Правило с ID ' . $dataID . ' отключено' : 'Правило с ID ' . $dataID . ' активировано';
            } else {
                $data['log'] = $oldValue ? 'Не удалось отключить правило с ID ' . $dataID : 'Не удалось активировать правило с ID ' . $dataID;
            }
        } else {
            $data['log'] = 'Не удалось определить обновляемое правило';
        }
        return $data;
    }

    public static function lists()
    {
        self::$TPL = 'ajax/lists';
    }

    public static function fullDelete()
    {
        $data = array();
        $dataID = (int)Template::getParam('docId', $_GET);
        if ($dataID > 0 && self::_checkObj($dataID)) {
            $modRedirect = new modRedirectMap(self::$modx);
            $modRedirect->delete($dataID);
            if (!self::_checkObj($dataID)) {
                $data['log'] = 'Удалена запись с ID: <strong>' . $dataID . '</strong>';
            } else {
                $data['log'] = 'Не удалось удалить запись с ID: <strong>' . $dataID . '</strong>';
            }
        } else {
            $data['log'] = 'Не удалось определить обновляему запись';
        }
        return $data;
    }

    public static function csv()
    {
        header('Content-Type: application/json');
        $json = array();
        self::$TPL = 'ajax/getValue';
        $file = Template::getParam('filedata', $_FILES);
        $name = strtolower(end(explode(".", Template::getParam('name', $file))));
        $stat = array();

        switch ($name) {
            case 'txt':
            {
                $stat = Helper::readFileLine(Template::getParam('tmp_name', $file), function (array $params) {
                    $line = trim(Template::getParam('line', $params));
                    if (!empty($line)) {
                        /**
                         * @var \DocumentParser $modx
                         */
                        $modx = Template::getParam('modx', $params);
                        /**
                         * Создавать новую запись
                         */
                        $modRM = new modRedirectMap($modx);
                        $insert = array(
                            'uri' => $line,
                            'active' => 0,
                            'page' => 0
                        );
                        $insert = Action::checkPageID($insert['uri'], $insert['page']);
                        $isNew = $modRM->create($insert)->save();

                        $uri = $modRM->get('uri');

                        $q = $modx->db->select('id', $modx->getFullTableName("redirect_map"), "`uri` = '" . $modx->db->escape($uri) . "'");
                        return (false !== $isNew && !empty($uri) && $modx->db->getRecordCount($q) == 1);
                    }
                }, array('modx' => self::$modx), 10000);
                break;
            }
            case 'csv':
            {
                ini_set('auto_detect_line_endings', TRUE);
                set_time_limit(0);
                ini_set('max_execution_time', 0);

                $stat = Helper::readFileLine(Template::getParam('tmp_name', $file), function (array $params) {
                    $flag = false;
                    $line = trim(Template::getParam('line', $params));
                    if (!empty($line)) {
                        $data = str_getcsv($line, ';');
                        if (count($data) == 5) {
                            /**
                             * @var \DocumentParser $modx
                             */
                            $modx = Template::getParam('modx', $params);
                            /**
                             * Создавать новую запись
                             */
                            $modRM = new modRedirectMap($modx);
                            $insert = array(
                                'page' => Template::getParam(0, $data, '0'),
                                'save_get' => Template::getParam(1, $data, '1'),
                                'full_request' => Template::getParam(2, $data, '1'),
                                'active' => Template::getParam(3, $data, '1'),
                                'uri' => iconv('windows-1251', 'UTF-8//IGNORE', Template::getParam(4, $data)),
                            );
                            $insert = Action::checkPageID($insert['uri'], $insert['page']);
                            $isNew = $modRM->create($insert)->save();
                            $uri = $modRM->get('uri');
                            $q = $modx->db->select('id', $modx->getFullTableName("redirect_map"), "`uri` = '" . $modx->db->escape($uri) . "'");

                            return (false !== $isNew && !empty($uri) && $modx->db->getRecordCount($q) == 1);
                        }
                    }
                    return $flag;
                }, array('modx' => self::$modx), 10000);
                break;
            }
            default:
                {
                $log[] = 'Некорректный тип файла';
                }
        }
        if (empty($log)) {
            $log = array(
                'Число строк обработанных из загружаемого файла: ' . Template::getParam('line', $stat, 0),
                'Число обновленных или добавленных ключей: ' . Template::getParam('add', $stat, 0)
            );
        }
        $json['message'] = self::$_tplObj->showBody('log', array('log' => $log));
        return array('value' => json_encode($json));
    }
}