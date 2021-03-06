<?php
// Версия 1.3.2
// 29.12.2016
// Еще раз улучшено получение родительской группы
defined('HOSTCMS') || exit('HostCMS: access denied.');

require_once ('js/seo_update/excel.php'); // Модуль разбора Exell
require_once ('js/seo_update/class.php'); // Модуль обновления
Error_Reporting(E_ALL & ~E_NOTICE);
$Excel = new Spreadsheet_Excel_Reader(); // создаем объект
$Excel->setOutputEncoding('cp1251'); // устанавливаем кодировку
$Excel->read('js/seo_update/meta.xls'); // открываем файл
$count = $Excel->sheets[0]['numRows']; // узнаем количество строк в 1 листе

$updater = new Updater;

// Проходим по всем строкам файла
for ($rowNum = 1; $rowNum <= $count; $rowNum++) {

	$CurrentUrl = $Excel->sheets[0]['cells'][$rowNum][2]; 
	$CurrentUrl = str_replace('http://','',$CurrentUrl);
	$CurrentUrl=explode("/", $CurrentUrl); 

	if(isset($Excel->sheets[0]['cells'][$rowNum][3])){
		$title = strip_tags(mb_convert_encoding($Excel->sheets[0]['cells'][$rowNum][3], "UTF-8", "cp1251"));// TITLE
	}else{
		$title = '';
	}
	if(isset($Excel->sheets[0]['cells'][$rowNum][4])){
		$description = $Excel->sheets[0]['cells'][$rowNum][4]; // DESCRIPTION
		$description = strip_tags(mb_convert_encoding($Excel->sheets[0]['cells'][$rowNum][4], "UTF-8", "cp1251"));// TITLE
		$description = str_replace("&#9742;","☎",$description);
	}else{
		$description = '';
	}
	if(isset($Excel->sheets[0]['cells'][$rowNum][5])){
		$keywords = strip_tags(mb_convert_encoding($Excel->sheets[0]['cells'][$rowNum][5], "UTF-8", "cp1251")); // KEYWORDS
	}else{
		$keywords = ''; 
	}

	$informationsystem_id = '';
	$shop_id = '';
	$result = '';
	$structure_id = '';
	$url_level_1 = '';
	$url_level_2 = '';

	// Получаем ID структуры
	if(isset($CurrentUrl[1])){
		if($CurrentUrl[1] != ''){
			$url_level_1 = $CurrentUrl[1];
			$current_structure = $updater->getStructure($url_level_1);
		}
	}

	// Проверяем, есть ли еще уровни URL и система они или нет. Если нет - обновляем корневой уровень
	$countLevels = count($CurrentUrl, 1);
	if($CurrentUrl[1] == '' && $countLevels == 2){
		$updater->updateMainpage($title, $description, $keywords);
	}elseif($CurrentUrl[2] != '' && $countLevels <= 4){
		// Получаем массив в виде ID ИС или ИМ и разбиваем их на переменные
		$getID = $updater->checkSystem($current_structure);
		if($getID['is_id'] != ''){
			$informationsystem_id = $getID['is_id'];
		}else{
			$shop_id = $getID['shop_id'];
		}

		for ($levelsNum = 1; $levelsNum <= $countLevels-1; $levelsNum++) {
			if($levelsNum <= 2 && $levelsNum == $countLevels-2){
				//echo "<br>Итерация больше 2, а точнее: " .$levelsNum ." Название: " .$CurrentUrl[$levelsNum];
				$deepLevel = $CurrentUrl[$levelsNum];
				$prevLvl = $CurrentUrl[$levelsNum-1]; // Предыдущий уровень URL для получения родительской группы 
				
				if ($informationsystem_id != ''){
					// Если переменная с ID ИС не пуста - обновляем ИС
					$updater->isUpdate($deepLevel, $prevLvl, $informationsystem_id, $title, $description, $keywords);
				}elseif($shop_id != ''){
					// Если переменная с ID ИМ не пуста - обновляем ИМ
					$updater->shopUpdate($deepLevel, $prevLvl, $shop_id, $title, $description, $keywords);
				}else{
					// Обновляем вложенную структуру. 
					$updater->updateStructure($deepLevel, $prevLvl, $title, $description, $keywords);
				}
			}
		}		
	}elseif($CurrentUrl[2] != '' && $countLevels > 4){
		// Получаем массив в виде ID ИС или ИМ и разбиваем их на переменные
		$getID = $updater->checkSystem($current_structure);
		if($getID['is_id'] != ''){
			$informationsystem_id = $getID['is_id'];
		}else{
			$shop_id = $getID['shop_id'];
		}

		for ($levelsNum = 1; $levelsNum <= $countLevels-1; $levelsNum++) {
			if($levelsNum > 2 && $levelsNum == $countLevels-2){ // -2 изза того, что присутствует доменный уровень + последний слэш
				//echo "<br>Итерация больше 2, а точнее: " .$levelsNum ." Название: " .$CurrentUrl[$levelsNum];
				$deepLevel = $CurrentUrl[$levelsNum];
				$prevLvl = $CurrentUrl[$levelsNum-1]; // Предыдущий уровень URL для получения родительской группы 
				if ($informationsystem_id != ''){
					// Если переменная с ID ИС не пуста - обновляем ИС
					$updater->isUpdate($deepLevel, $prevLvl, $informationsystem_id, $title, $description, $keywords);
				}elseif($shop_id != ''){
					// Если переменная с ID ИМ не пуста - обновляем ИМ
					$updater->shopUpdate($deepLevel, $prevLvl, $shop_id, $title, $description, $keywords);
				}else{
					// Обновляем вложенную структуру. 
					$updater->updateStructure($deepLevel, $prevLvl, $title, $description, $keywords);
				}
			}
		}		
	}else{
		$updater->updateFirstLevel($url_level_1, $title, $description, $keywords);
	}
	echo 'Информация успешно обновлена. Страница: <a href="'. $Excel->sheets[0]['cells'][$rowNum][2] .'" target="_blank">'. $Excel->sheets[0]['cells'][$rowNum][2] .'</a><br />';
}
echo '<br /> Всего обновлено: ' .$count . ' записей';
?>