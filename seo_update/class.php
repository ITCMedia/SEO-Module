<?php
// Версия 1.3
// 16.12.2016
// Добавлено получение ID родительской группы
class Updater{
	public $informationsystem_id, $shop_id, $structure_id;
	
	public function getStructure($url_level_1){
		$query_selection = mysql_query("SELECT `id` FROM `structures` WHERE `path` ='{$url_level_1}' AND `deleted` ='0'");
		$result = mysql_fetch_array($query_selection); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
		$structure_id = $result['id'];
		//echo '<br>ID Структуры: ' .$structure_id;
		return $structure_id;
	}
	
	public function updateStructure($deepLevel, $title, $description, $keywords){
		$result = mysql_query ("UPDATE `structures` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$deepLevel}' AND `deleted` ='0'");
		//if ($result == 'true') echo "<br>Данные для корневого узла $deepLevel успешно обновлены.";
	}
	
	public function updateFirstLevel($url_level_1, $title, $description, $keywords){
		$result = mysql_query ("UPDATE `structures` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$url_level_1}' AND `deleted` ='0'");
		//if ($result == 'true') echo "<br>Данные для корневого узла $url_level_1 успешно обновлены."; 
	}
	
	public function updateMainpage($title, $description, $keywords){
		$result = mysql_query ("UPDATE `structures` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='/' AND `deleted` ='0'");
	}
	
	public function checkSystem($current_structure){
		$systemReturn = array();
		$query_selection = mysql_query("SELECT `id` FROM `informationsystems` WHERE `structure_id` ='{$current_structure}' AND `deleted` ='0'");
		$result = mysql_fetch_array($query_selection);
		$systemReturn['is_id'] = $result['id'];
		if($systemReturn['is_id'] == ''){
			$query_selection = mysql_query("SELECT `id` FROM `shops` WHERE `structure_id` ='{$current_structure}' AND `deleted` ='0'");
			$result = mysql_fetch_array($query_selection); 
			$systemReturn['shop_id'] = $result['id'];
			//echo '<br>ID Магазин: ' .$systemReturn['shop_id'];
		}else{
			//echo '<br>ID ИС: ' .$systemReturn['is_id'];
		}
		return $systemReturn;
	}
	
	public function shopUpdate($deepLevel, $prevLvl, $shop_id, $title, $description, $keywords){
		$query_item = mysql_query("SELECT `shop_group_id` FROM `shop_items` WHERE `path` ='{$deepLevel}' AND `shop_id` ='{$shop_id}' AND `deleted` ='0'");
		$output = mysql_fetch_array($query_item); 
		$shop_group_id = $output['shop_group_id'];
		
		if($shop_group_id != ''){
			$result = mysql_query ("UPDATE `shop_items` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$deepLevel}' AND `shop_id` ='{$shop_id}' AND `deleted` ='0'");
			//if ($result == 'true') echo "<br>Данные для товара $deepLevel успешно обновлены."; 
		}else{
			$query_item = mysql_query("SELECT `id` FROM `shop_groups` WHERE `path` ='{$prevLvl}' AND `shop_id` ='{$shop_id}' AND `deleted` ='0'");
			$output = mysql_fetch_array($query_item); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
			$parent_group_id = $output['id'];

			$result = mysql_query ("UPDATE `shop_groups` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `parent_id` = '{$parent_group_id}' AND `path` ='{$deepLevel}' AND `shop_id` ='{$shop_id}' AND `deleted` ='0'");
			//if ($result == 'true') echo "<br> Данные для группы магазина (ID родительской группы: $parent_group_id, Путь: $deepLevel) обновлены. <br>";
		}
	}
	
	public function isUpdate($deepLevel, $prevLvl, $informationsystem_id, $title, $description, $keywords){
		$query_item = mysql_query("SELECT `informationsystem_group_id` FROM `informationsystem_items` WHERE `path` ='{$deepLevel}' AND `informationsystem_id` ='{$informationsystem_id}' AND `deleted` ='0'");
		$output = mysql_fetch_array($query_item); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
		$informationsystem_group_id = $output['informationsystem_group_id'];

		if($informationsystem_group_id != ''){
			$result = mysql_query ("UPDATE `informationsystem_items` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `path` ='{$deepLevel}' AND `informationsystem_id` ='{$informationsystem_id}' AND `deleted` ='0'");
			//if ($result == 'true') echo "<br>Данные для инфоэлемента $deepLevel успешно обновлены. <br>";
		}else{
			$query_item = mysql_query("SELECT `id` FROM `informationsystem_groups` WHERE `path` ='{$prevLvl}' AND `informationsystem_id` ='{$informationsystem_id}' AND `deleted` ='0'");
			$output = mysql_fetch_array($query_item); // Преобразование выборки в ассоциативный массив для дальнейшего взятия ячейки.
			$parent_group_id = $output['id'];

			$result = mysql_query ("UPDATE `informationsystem_groups` SET `seo_title`='{$title}', `seo_description`='{$description}', `seo_keywords`='{$keywords}'  WHERE `parent_id` = '{$parent_group_id}' AND `path` ='{$deepLevel}' AND `informationsystem_id` ='{$informationsystem_id}' AND `deleted` ='0'");
			//if ($result == 'true')  echo "<br> Данные для группы ИС (ID родительской группы: $parent_group_id, Путь: $deepLevel) обновлены. <br>";
		}
	}
}
?>