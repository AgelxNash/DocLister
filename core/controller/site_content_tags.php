<?php
/**
 * site_content_tags controller with TagSaver plugin
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 26.08.2013
 * @version 1.0.23
 *
 * @TODO add parameter showFolder - include document container in result data whithout children document if you set depth parameter.
 */

include_once(dirname(__FILE__) . "/site_content.php");

class site_content_tagsDocLister extends site_contentDocLister
{
    private $tag = array();

    /**
     * @absctract
     * @todo link maybe include other GET parameter with use pagination. For example - filter
     */
    public function getUrl($id = 0)
    {
        $id = $id > 0 ? $id : $this->modx->documentIdentifier;
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : "";
        $tag = $this->checkTag();
        if ($tag != false && is_array($tag) && $tag['mode'] == 'get') {
            $link .= "&tag=" . urlencode($tag['tag']);
        }
        $url = ($id == $this->modx->config['site_start']) ? $this->modx->config['site_url'] . ($link != '' ? "?{$link}" : "") : $this->modx->makeUrl($id, '', $link, 'full');
        return $url;
    }

    /**
     * document
     */

    // @abstract
    public function getChildrenCount()
    {
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }
        $wheres = $this->whereTag($where);
        $tbl_site_content = $this->getTable('site_content', 'c');
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        $getCFGDef = $this->getCFGDef('showParent', '0') ? '' : "AND c.id NOT IN({$sanitarInIDs})";
        $fields = 'count(c.`id`) as `count`';
        $from = "{$tbl_site_content} {$wheres['join']}";
        $where = "{$wheres['where']} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 {$getCFGDef}";
        $rs = $this->modx->db->select($fields, $from, $where);
        return $this->modx->db->getValue($rs);
    }

    protected function getDocList()
    {
        /**
         * @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
         * @TODO: 5) Добавить фильтрацию по основным параметрам документа
         */
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }

        $tbl_site_content = $this->getTable('site_content', 'c');
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        $where = "WHERE {$where} c.id IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1";
        $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));
        $select = "c.*";
        $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
        if (preg_match("/^ORDER BY (.*) /", $sort, $match)) {
            $TVnames = $this->extender['tv']->getTVnames();
            if (isset($TVnames[$match[1]])) {
                $tbl_site_content .= " LEFT JOIN " . $this->getTable("site_tmplvar_contentvalues") . " as tv
                    on tv.contentid=c.id AND tv.tmplvarid=" . $TVnames[$match[1]];
                $sort = str_replace("ORDER BY " . $match[1], "ORDER BY tv.value", $sort);
            }
        }

        $rs = $this->modx->db->query("SELECT {$select}  FROM {$tbl_site_content} {$where} GROUP BY c.id {$sort} {$limit}");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['id']] = $item;
        }
        return $out;
    }

    private function getTag()
    {
        $tags = $this->getCFGDef('tagsData', '');
        $this->tag = array();
        if ($tags != '') {
            $tmp = explode(":", $tags, 2);
            if (count($tmp) == 2) {
                switch ($tmp[0]) {
                    case 'get':
                    {
                        $tag = (isset($_GET[$tmp[1]]) && !is_array($_GET[$tmp[1]])) ? $_GET[$tmp[1]] : '';
                        break;
                    }
                    case 'static':
                    default:
                        {
                        $tag = $tmp[1];
                        break;
                        }
                }
                $this->tag = array("mode" => $tmp[0], "tag" => $tag);
                $this->toPlaceholders($this->sanitarData($tag), 1, "tag");
            }
        }
        return $this->checkTag();
    }

    private function checkTag($reconst = false)
    {
        $data = (is_array($this->tag) && count($this->tag) == 2 && isset($this->tag['tag']) && $this->tag['tag'] != '') ? $this->tag : false;
        if ($data === false && $reconst === true) {
            $data = $this->getTag();
        }
        return $data;
    }

    private function whereTag($where)
    {
        $join = '';
        $tag = $this->checkTag(true);
        if ($tag !== false) {
            $join = "RIGHT JOIN " . $this->getTable('site_content_tags', 'ct') . " on ct.doc_id=c.id
					RIGHT JOIN " . $this->getTable('tags', 't') . " on t.id=ct.tag_id";
            $where .= "t.`name`='" . $this->modx->db->escape($tag['tag']) . "'" .
                (($this->getCFGDef('tagsData', '') > 0) ? "AND ct.tv_id=" . (int)$this->getCFGDef('tagsData', '') : "") . " AND ";
        }
        $out = array("where" => $where, "join" => $join);
        return $out;
    }

    /**
     * @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
     * @TODO: 5) Добавить фильтрацию по основным параметрам документа
     */
    protected function getChildrenList()
    {
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }
        $where = $this->whereTag($where);

        $sql = $this->modx->db->query("
			SELECT c.* FROM " . $this->getTable('site_content', 'c') . $where['join'] . "
			WHERE " . $where['where'] . "
				c.parent IN (" . $this->sanitarIn($this->IDs) . ")
				AND c.deleted=0 
				AND c.published=1 " .
                (($this->getCFGDef('showParent', '0')) ? "" : "AND c.id NOT IN(" . $this->sanitarIn($this->IDs) . ") ") .
                $this->SortOrderSQL('if(c.pub_date=0,c.createdon,c.pub_date)') . " " .
                $this->LimitSQL($this->getCFGDef('queryLimit', 0))
        );
        $rows = $this->modx->db->makeArray($sql);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['id']] = $item;
        }
        return $out;
    }
}