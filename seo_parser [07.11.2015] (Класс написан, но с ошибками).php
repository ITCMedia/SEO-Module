<? 

defined('HOSTCMS') || exit('HostCMS: access denied.');

require_once ('js/excel.php'); // подключаем класс
Error_Reporting(E_ALL & ~E_NOTICE);
$Excel = new Spreadsheet_Excel_Reader(); // создаем объект
$Excel->setOutputEncoding('utf-8'); // устанавливаем кодировку
$Excel->read('js/test_seo.xls'); // открываем файл
$count = $Excel->sheets[0]['numRows']; // узнаем количество строк в 1 листе

class Updater{
	public $informationsystem_id, $shop_id, $structure_id;
	
	public function getStructure($url_level_1){
		$query_selection = mysql_query("SELECT `id` FROM `structures` WHERE `path` ='{$url_level_1}'");
		$result = mysql_fetch_array($query_selection); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
		$structure_id = $result['id'];
		echo '<br>ID Структуры: ' .$structure_id;
		return $structure_id;
	}
	
	public function updateFirstLevel($url_level_1, $title, $description, $keywords){
		$result = mysql_query ("UPDATE `structures` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_1}'");
		if ($result == 'true')
		{
			echo "<br>Данные для корневого узла " . $url_level_1 . " успешно обновлены.";
		}
	}
	
	public function checkSystem($current_structure){
		$systemReturn = array();
		$query_selection = mysql_query("SELECT `id` FROM `informationsystems` WHERE `structure_id` ='{$current_structure}'");
		$result = mysql_fetch_array($query_selection);
		$systemReturn['is_id'] = $result['id'];
		echo '<br>ID ИС: ' .$systemReturn['is_id'];
		if($systemReturn['is_id'] == ''){
			$query_selection = mysql_query("SELECT `id` FROM `shops` WHERE `structure_id` ='{$current_structure}'");
			$result = mysql_fetch_array($query_selection); 
			$systemReturn['shop_id'] = $result['id'];
			echo '<br>ID Магазин: ' .$systemReturn['shop_id'];
		}
		return $systemReturn;
	}

		
}

$updater = new Updater;
// с помощью цикла выводим все ячейки
for ($rowNum = 1; $rowNum <= $count; $rowNum++) {

	$CurrentUrl = $Excel->sheets[0]['cells'][$rowNum][2]; 
	$CurrentUrl = str_replace('http://','',$CurrentUrl);
	$CurrentUrl=explode("/", $CurrentUrl); 

	$title = $Excel->sheets[0]['cells'][$rowNum][3]; // TITLE
	$description = $Excel->sheets[0]['cells'][$rowNum][4]; // DESCRIPTION
	$keywords = $Excel->sheets[0]['cells'][$rowNum][5]; // KEYWORDS

	$url_level_1 = $CurrentUrl[1];
	
	$current_structure = $updater->getStructure($url_level_1);

	if (!empty($CurrentUrl[2])){
		$url_level_2 = $CurrentUrl[2];
		$updater->checkSystem($current_structure);
	}else{
		$updater->updateFirstLevel($url_level_1, $title, $description, $keywords);
	}
	print_r($updater->checkSystem($current_structure));
	$getID = $updater->checkSystem($current_structure);
	echo "<br />echo:" .$getID['is_id'];
	// Ниже идет проверка для групп информационной системы
	if (!empty($informationsystem_id)){
		$query_item = mysql_query("SELECT `informationsystem_group_id` FROM `informationsystem_items` WHERE `path` ='{$url_level_2}' AND `informationsystem_id` ='{$informationsystem_id}'");
		$output = mysql_fetch_array($query_item); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
		$informationsystem_group_id = $output['informationsystem_group_id'];
		if($informationsystem_group_id != ''){
			$result = mysql_query ("UPDATE `informationsystem_items` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_2}' AND `informationsystem_id` ='{$informationsystem_id}'");
			if ($result == 'true')
			{
				echo "<br>Данные для инфоэлемента " . $url_level_2 . " успешно обновлены.";
			}
		}else{
			$result = mysql_query ("UPDATE `informationsystem_groups` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_2}' AND `informationsystem_id` ='{$informationsystem_id}'");
			if ($result == 'true')
			{
				echo "<br>Данные для группы " . $url_level_2 . " обновлены.";
			}
		}
	}
	
	
	if(!empty($shop_id)){
		$query_item = mysql_query("SELECT `shop_group_id` FROM `shop_items` WHERE `path` ='{$url_level_2}' AND `shop_id` ='{$shop_id}'");
		$output = mysql_fetch_array($query_item); 
		$shop_group_id = $output['shop_group_id'];
		if($shop_group_id != ''){
			$result = mysql_query ("UPDATE `shop_items` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_2}' AND `shop_id` ='{$shop_id}'");
			if ($result == 'true')
			{
				echo "<br>Данные для товара " . $url_level_2 . " успешно обновлены.";
			}
		}else{
			$result = mysql_query ("UPDATE `shop_groups` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_2}' AND `shop_id` ='{$shop_id}'");
			if ($result == 'true')
			{
				echo "<br>Данные для группы магазина " . $url_level_2 . " обновлены.";
			}
		}
	}
	
	echo "<br>_________________________<br>";
	$informationsystem_id = '';
	$shop_id = '';
	$result = '';
	//$structure_id = '';
}
// Сейчас мы остановились на том, что создали класс и методы. Метод проверки ИС или ИМ возвращает массив, который надо занести в переменную для дальнейшей проверки. Брать ее надо по названию ячейки.

?>

