<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}
/**
 * Jot extender for DocLister
 *
 * @category extender
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Pathologic <maxx@np.by>
 */

class jotcount_DL_Extender extends extDocLister
{
    protected function run()
    {
        return true;
    }
	
	public function countComments($docs)
	{
		$comments = array();
		$from = $this->DocLister->getTable('jot_content');
		$in = (count($ids)) ? 'uparent IN ('.implode(',',$docs).') AND' : '';
        $rs = $this->DocLister->dbQuery("SELECT uparent, COUNT(*) FROM {$from} WHERE {$in} published=1 AND deleted=0 GROUP BY uparent");
        $counts = $this->modx->db->makeArray($rs);
		if ($counts) foreach($counts as $k=>$v) $comments[$v['uparent']] = $v['COUNT(*)'];
        return $comments;
	}
}