<?php namespace DLCity;
class Action
{
    protected static $modx = null;
    public static $TPL = null;
    protected static $_mode = null;

    public static function init(\DocumentParser $modx, Template $tpl)
    {
        self::$modx = $modx;
        self::$TPL = \DLCity\Template::showLog();
        self::$_mode = \DLCity\Helper::getMode();
    }

    protected static function checkObj($id)
    {
        $q = self::$modx->db->select('hide', self::$modx->getFullTableName(self::$_mode), "id = " . $id);
        return (self::$modx->db->getRecordCount($q) == 1);
    }

    protected static function getValue($field, $id)
    {
        $q = self::$modx->db->select($field, self::$modx->getFullTableName(self::$_mode), "id = " . $id);
        return self::$modx->db->getValue($q);
    }

    public static function add()
    {
        if (!empty($_POST['dataname']) && is_scalar($_POST['dataname'])) {
            $insert = array(
                'name' => self::$modx->db->escape($_POST['dataname']),
                'hide' => 0
            );
            switch (self::$_mode) {
                case 'city':
                {
                    $sql = "SELECT count(id) FROM " . self::$modx->getFullTableName(self::$_mode) . " WHERE `name`='" . self::$modx->db->escape($_POST['dataname']) . "'";
                    if (self::$modx->db->getValue($sql) > 0) {
                        $insert = array();
                        $data['log'] = 'Такой город уже имеется в списке';
                    }
                    break;
                }
                case 'street':
                {
                    $insert['parent_id'] = (int)\DLCity\Template::getParam('dataID', $_REQUEST);
                    $sql = "SELECT count(id) FROM " . self::$modx->getFullTableName(self::$_mode) . " WHERE `name`='" . self::$modx->db->escape($_POST['dataname']) . "' AND parent_id='" . $insert['parent_id'] . "'";
                    if (self::$modx->db->getValue($sql) > 0) {
                        $insert = array();
                        $data['log'] = 'Такая запись уже имеется в списке';
                    }
                    break;
                }
                default:
                    {
                    $insert = array();
                    $data['log'] = 'Для этого режима не определены правила добавления записей';
                    }
            }
            if (!empty($insert)) {
                $insert = self::$modx->db->insert($insert, self::$modx->getFullTableName(self::$_mode));
                if (!empty($insert)) {
                    $data['log'] = 'Добавлена новая запись';
                } else {
                    $data['log'] = 'Не удалось распознать устанавливаемый пакет';
                }
            }
        } else {
            $data['log'] = 'Необходимо указать пакет который следует установить';
        }
        return $data;
    }

    public static function delete()
    {
        $dataID = (int)\DLCity\Template::getParam('editID', $_REQUEST);
        if (!empty($dataID)) {
            switch (self::$_mode) {
                case 'city':
                {
                    self::deleteCity($dataID);
                    $data['log'] = 'Город удален';
                    break;
                }
                case 'street':
                {
                    self::deleteStreet($dataID);
                    $data['log'] = 'Улица удалена';
                    break;
                }
                default:
                    {
                    $data['log'] = 'Для этого режима не задано действие удаления';
                    break;
                    }
            }
        } else {
            $data['log'] = 'Не указан ID записи которую необходимо удалить';
        }
        return $data;
    }

    public static function edit()
    {
        $dataID = (int)\DLCity\Template::getParam('editID', $_REQUEST);
        if (self::checkObj($dataID)) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $q = self::$modx->db->update(
                    array(
                        'name' => self::$modx->db->escape(\DLCity\Template::getParam('dataname', $_POST)),
                        'hide' => (int)\DLCity\Template::getParam('hide', $_POST)
                    ),
                    self::$modx->getFullTableName(self::$_mode),
                    "id = " . $dataID
                );
                $data['log'] = $q ? 'Информация обновлена' : 'Не удалось обновить информацию';
            } else {
                self::$TPL = 'EditForm';
                $q = self::$modx->db->select("*", self::$modx->getFullTableName(self::$_mode), "id = " . $dataID);
                $data = self::$modx->db->getRow($q);
            }
        } else {
            $data['log'] = 'Не удалось определить обновляему запись';
        }
        return $data;
    }

    public static function display()
    {
        $dataID = (int)\DLCity\Template::getParam('editID', $_REQUEST);
        if ($dataID > 0 && self::checkObj($dataID)) {
            $q = self::$modx->db->update(array(
                    'hide' => !self::getValue('hide', $dataID),
                ), self::$modx->getFullTableName(self::$_mode), "id = " . $dataID);
            $data['log'] = $q ? 'Информация обновлена' : 'Не удалось обновить информацию';
        } else {
            $data['log'] = 'Не удалось определить обновляему запись';
        }
        return $data;
    }


    protected static function deleteCity($id)
    {
        self::$modx->db->delete(self::$modx->getFullTableName('street'), "parent_id = " . $id);
        self::$modx->db->delete(self::$modx->getFullTableName('city'), "id = " . $id);
    }

    protected static function deleteStreet($id)
    {
        self::$modx->db->delete(self::$modx->getFullTableName('street'), "id = " . $id);
    }
}