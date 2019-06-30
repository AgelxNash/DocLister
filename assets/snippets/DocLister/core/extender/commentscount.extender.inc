<?php
/**
 * Jot extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Pathologic <maxx@np.by>
 */
class commentscount_DL_Extender extends extDocLister
{

    /**
     * @return bool
     */
    protected function run()
    {
        $context = $this->DocLister->getCFGDef('context', 'site_content');
        $context = $this->modx->db->escape($context);
        $join = "LEFT JOIN (SELECT `thread`, COUNT(`id`) AS `comments_count` FROM {$this->DocLister->getTable('comments')} WHERE `context`='{$context}' AND `published`=1 AND `deleted`=0 GROUP BY `thread`) `cc` ON `cc`.`thread` = {$this->DocLister->getPK()}";
        $this->DocLister->setFiltersJoin($join);
        $fields = $this->DocLister->getCFGDef('selectFields', 'c.*');
        $this->DocLister->config->setConfig(array(
            "selectFields" => $fields . ",IFNULL(`cc`.`comments_count`,0) AS `comments_count`"
        ));

        return true;
    }
}
