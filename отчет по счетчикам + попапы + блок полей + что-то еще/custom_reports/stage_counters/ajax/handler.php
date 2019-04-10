<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');


CModule::IncludeModule("crm");

require_once $_SERVER['DOCUMENT_ROOT'].'/custom_reports/stage_counters/ajax/class.php';

$obj = new DealCategoryStageCounters;

//1. запрос для селекта с категориями
if($_POST['ACTION'] === 'GIVE_ME_CATEGORIES_FOR_SELECT') $obj->getCategoriesForSelect();

//2. Запрос инфы по фильтрам (направеление, дата начала, дата конца)
if($_POST['ACTION'] === 'GIVE_ME_STATISTICS_BY_CATEGORY_ID') $obj->getStatisticsByFilter($_POST);

//3. запрос для селекта с ответственными
if($_POST['ACTION'] === 'GIVE_ME_ASSIGNED_LIST_FOR_SELECT') $obj->getAssignedForSelect();
