<?php
/**
 * [[DLMonthFilter?
 * 		&idType=`parents`
 * 		&parents=`87`
 * 		&tpl=`listNews`
 * 		&paginate=`pages`
 * 		&display=`2`
 * 		&monthSource=`tv`
 * 		&monthField=`date`
 * 		&tvList=`date`
 * 		&sortDir=`DESC`
 * 	]]
 *  [+pages+]
*/

include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
if(!function_exists('validateMonth')){
	function validateMonth($val){
		$flag = false;
		if(is_string($val)){
			$val = explode("-", $val, 2);
			$flag = (count($val) && is_array($val) && strlen($val[0])==2 && strlen($val[1])==4); //Валидация содержимого массива
			$flag = ($flag && (int)$val[0]>0 && (int)$val[0]<=12); //Валидация месяца
			$flag = ($flag && (int)$val[1]>1900 && (int)$val[1]<=2100); //Валидация года
		}
		return $flag;
	}
}
$params = is_array($modx->event->params) ? $modx->event->params : array();

$monthSource = APIHelpers::getkey($params, 'monthSource', 'content');
$monthField = APIHelpers::getkey($params, 'monthField', 'if(pub_date=0,createdon,pub_date)');

$tmp = date("m-Y");
/**
* currentMonth: Текущий месяц в формате 00-0000, где:
*		00 - Номер месяца с ведущим нулем (01, 02, 03, ..., 12)
*		0000 - Год
* Если не указан в параметре, то генерируется автоматически текущий месяц
*/
$currentMonth = APIHelpers::getkey($params, 'currentMonth', $tmp); // Текущий месяц
if(!validateMonth($currentMonth)){
	$currentMonth = $tmp;
}
/**
* activeMonth
*		Месяц который выбрал пользователь.
*
*		Если параметр не задан, то в качестве значения по умолчанию используется значение параметра currentMonth
*		При наличии ГЕТ параметра month, приоритет отдается ему
*/
$tmp = APIHelpers::getkey($params, 'activeMonth', $currentMonth);
$tmpGet = APIHelpers::getkey($_GET, 'month', $tmp);
if(!validateMonth($tmpGet)){
	$activeMonth = $tmp;
	if(!validateMonth($activeMonth)){
		$activeMonth = $currentMonth;
	}
}else{
	$activeMonth = $tmpGet;
}

$m = $modx->db->escape($activeMonth);
if($monthSource == 'tv'){
	$params['tvSortType'] = 'TVDATETIME';
	$params['addWhereList'] = "DATE_FORMAT(STR_TO_DATE(`dltv_".$monthField ."_1`.`value`,'%d-%m-%Y %H:%i:%s'), '%m-%Y')='".$m."'";
}else{
	$params['addWhereList'] = "DATE_FORMAT(FROM_UNIXTIME(".$monthField."), '%m-%Y')='".$m."'";
}
$params['sortBy'] = $monthField;

return $modx->runSnippet('DocLister', $params);