//<?
/** 
 * MODxAPI
 * 
 * MODxAPI
 *
 * @category    plugin 
 * @version     1.0
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL) 
 * @internal    @properties 
 * @internal    @events OnWebPageInit,OnManagerPageInit,OnPageNotFound
 * @internal    @modx_category API
 * @internal    @legacy_names MODxAPI
 * @internal    @installset base
 * @author      Agel_Nash <modx@agel-nash.ru>
 */

include_once(MODX_BASE_PATH."assets/lib/MODxAPI/modResource.php");
if(!isset($modx->doc)){
 $modx->doc = new modResource($modx);
}