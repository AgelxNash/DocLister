<?php
/** 
* [[DLReflect? 
*	&idType=`parents`
*	&parents=`87`
*	&monthSource=`tv`
*	&monthField=`date`
*	&limitBefore=`1`
*	&limitAfter=`3`
* ]] 
*/
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLCollection.class.php');
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

$debug = APIHelpers::getkey($params, 'debug', 0);
/**
* wrapTPL: Шаблон обертка для списка месяцев. Поддерживается плейсхолдеры: 
*		[+wrap+] - Список месяцев
*		[+years+] - Всего месяцев
*		[+displayYears] - Отображено месяцев в списке
*/
$wrapTPL = APIHelpers::getkey($params, 'wrapTPL', '@CODE: <div class="month-list"><ul>[+wrap+]</ul></div>');
/**
* monthTPL: Шаблон месяца. Поддерживается плейсхолдеры: 
*		[+url+] - ссылка на страницу где настроена фильтрация по документам за выбранный месяц
*		[+monthName+] - Название месяца
*		[+monthNum+] - Номер месяца с ведущим нулем (01, 02, 03, ..., 12)
*		[+year+] - Год
*		[+years+] - Общее число месяцев которое возможно отобразить в списке
*		[+displayYears] - Число месяцев отображаемое в общем списке
*/
$monthTPL = APIHelpers::getkey($params, 'monthTPL', '@CODE: <li><a href="[+url+]" title="[+monthName+] [+year+]">[+monthName+] [+year+]</a></li>');
/**
* activeMonthTPL: Шаблон месяца. 
* Поддерживается такие же плейсхолдеры, как и в шаблоне monthTPL
*/
$activeMonthTPL = APIHelpers::getkey($params, 'activeMonthTPL', '@CODE: <li><span>[+monthName+] [+year+]</span></li>');

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
* appendCurrentMonth
*		Если в спске месяцев не встречается указанный через параметр currentMonth, то
*		этот параметр определяет - стоит ли добавлять ли его или нет
* Возможные значения: 
*		0 - не добавлять, 
* 		1 - добавлять
* Этот параметр тесно связан с параметром activeMonth
*/
$appendCurrentMonth = APIHelpers::getkey($params, 'appendCurrentMonth', 1);

/**
* activeMonth
*		Месяц который выбрал пользователь.
*
*		Если параметр не задан, то в качестве значения по умолчанию используется значение параметра currentMonth
*		При наличии ГЕТ параметра month, приоритет отдается ему
*
* 		При отсутствии выбранного месяца в общем списке месяцев и совпадении значений параметров currentMonth и activeMonth,
*		месяц будет автоматически добавлен в общий список. Тем самым значение параметра appendCurrentMonth будет расцениваться как 1
* Возможные значения: Текущий месяц в формате 00-0000
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

/**
* monthSource
*		Источник даты. 
* Возможные значения:
*	tv: ТВ параметр
*	content или любое другое значение: Основные параметры документа
*/
$monthSource = APIHelpers::getkey($params, 'monthSource', 'content');

/**
* monthField
*		Имя поля из которого берется дата документа. 
* Возможные значения:
*		Любое имя существующего ТВ параметра или поля документа
* Значение по умолчанию:
*		Если не указана дата публикации, то использовать дату создания документа
*		Актуально только для таблицы site_content
*/
$monthField = APIHelpers::getkey($params, 'monthField', 'if(pub_date=0,createdon,pub_date)');

/**
* targetID
*		ID документа на котором настроена фильтрация по месяцам
* Значение по умолчанию:
*		ID текущего документа
*/
$targetID = APIHelpers::getkey($params, 'targetID', $modx->documentObject['id']);

/**
* limitBefore
*		Число элементов до месяца указанного в activeMonth параметре
* Возможные значения: Любое число. 0 расценивается как все доступные месяцы
* Значение по умолчанию: 0
*/
$limitBefore = (int)APIHelpers::getkey($params, 'limitBefore', 0);
/**
* limitAfter
*		Число элементов после месяца указанного в activeMonth параметре
* Возможные значения: Любое число. 0 расценивается как все доступные месяцы
* Значение по умолчанию: 0
*/
$limitAfter = (int)APIHelpers::getkey($params, 'limitAfter', 0);
$display = $limitBefore + 1 + $limitAfter;

$out = '';

$DLParams = $params;
$DLParams['debug'] = $debug;
$DLParams['api'] = 'id';
$DLParams['orderBy'] = $monthField;
$DLParams['saveDLObject'] = 'DLAPI';
if($monthSource == 'tv'){
	$DLParams['sortType'] = 'TVDATETIME';
	$DLParams['selectFields'] = "DATE_FORMAT(STR_TO_DATE(`dltv_".$monthField ."_1`.`value`,'%d-%m-%Y %H:%i:%s'), '%m-%Y') as `id`";
}else{
	$DLParams['orderBy'] = $monthField;
	$DLParams['selectFields'] = "DATE_FORMAT(FROM_UNIXTIME(".$monthField."), '%m-%Y') as `id`";
}
$totalMonths = $modx->runSnippet('DocLister', $DLParams);

//Получаем объект DocLister'a
$DLAPI = $modx->getPlaceholder('DLAPI');
//Загружаем лексикон с месяцами
$DLAPI->loadLang('months');

//Разбираем API ответ от DocLister'a
$totalMonths = json_decode($totalMonths, true);
if(is_null($totalMonths)){
	$totalMonths = array();
}
$totalMonths = new DLCollection($modx, $totalMonths);

/** Добавляем активный месяц в коллекцию */
$totalMonths->add(array('id' => $activeMonth), $activeMonth);

/** Добавляем текущий месяц в коллекцию */
if($appendCurrentMonthnth){
	$totalMonths->add(array('id' => $currentMonth), $currentMonth);
}

/** Сортируем месяца по возрастанию */
$totalMonths->sort(function($a, $b){
	$aDate = DateTime::createFromFormat("m-Y", $a['id']);
    $bDate = DateTime::createFromFormat("m-Y", $b['id']);
    return $aDate->getTimestamp() - $bDate->getTimestamp();
})->reindex();

/** Разделяем коллекцию месяцев на 2 части (до текущего месяца и после) */
list($lMonth, $rMonth) = $totalMonths->partition(function($key, $val) use($activeMonth){
	$aDate = DateTime::createFromFormat("m-Y", $val['id']);
    $bDate = DateTime::createFromFormat("m-Y", $activeMonth);
    return $aDate->getTimestamp() < $bDate->getTimestamp();
});
//Удаляем текущий активный месяц из списка месяцев идущих за текущим
$rMonth->reindex()->remove(0); 
//Разворачиваем в обратном порядке список месяцев до текущего месяца
$lMonth = $lMonth->reverse();

//Расчитываем сколько месяцев из какого списка взять
$showBefore = ($lMonth->count() < $limitBefore || empty($limitBefore)) ? $lMonth->count() : $limitBefore;
if( ($rMonth->count() < $limitAfter) || empty($limitAfter)){
	$showAfter = $rMonth->count();
	$showBefore += !empty($limitAfter) ? ($limitAfter - $rMonth->count()) : 0;
}else{
	if($limitBefore > 0){
		$showAfter = $limitAfter + ($limitBefore - $showBefore);
	}else{
		$showAfter = $limitAfter;
	}
}
$showBefore += (($showAfter >= $limitAfter || $limitAfter>0) ? 0 : ($limitAfter - $showAfter));

echo $showBefore.'-'.$showAfter;
//Создаем новую коллекцию месяцев
$outMonths = new DLCollection($modx);
//Берем нужное число элементов с левой стороны
$i=0;
foreach($lMonth as $item){
	if((++$i) > $showBefore) break;
	$outMonths->add($item['id']);
}

//Добавляем текущий месяц
$outMonths->add($activeMonth);

//Берем оставшее число позиций с правой стороны
$i=0;
foreach($rMonth as $item){
	if((++$i) > $showAfter) break;
	$outMonths->add($item['id']);
}

//Сортируем результатирующий список по возрастанию
$outMonths->sort(function($a, $b){
	$aDate = DateTime::createFromFormat("m-Y", $a);
    $bDate = DateTime::createFromFormat("m-Y", $b);
    return $aDate->getTimestamp() - $bDate->getTimestamp();
})->reindex();

//Применяем шаблон к каждому отображаемому месяцу
foreach($outMonths as $month){
	$tpl = $activeMonth == $month ? $activeMonthTPL : $monthTPL;
	list($vMonth, $vYear) = explode('-', $month, 2);
	$data = array(
		'url' => $modx->makeUrl($targetID, '', http_build_query(array('month'=>$month))),
		'monthNum' => $vMonth,
		'monthName' => $DLAPI->getMsg('months.'.(int)$vMonth),
		'year' => $vYear,
		'years' => $totalMonths->count(),
		'displayYears' => $outMonths->count()
	);
	$out .= $DLAPI->parseChunk($tpl, $data);
}

//Заворачиваем в шаблон обертку весь список месяцев
$out = $DLAPI->parseChunk($wrapTPL, array(
	'wrap' => $out,
	'years' => $totalMonths->count(),
	'displayYears' => $outMonths->count()
));

//Ну и выводим стек отладки если это нужно
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'manager') {
    $debug = $DLAPI->debug->showLog();
} else {
    $debug = '';
}

return $debug.$out;