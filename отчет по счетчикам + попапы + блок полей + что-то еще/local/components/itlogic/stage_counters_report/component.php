<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['categories'] = $this->mainFunct();


$arFilter1 = Array('ENTITY_ID' => 1432,'ENTIYY_TYPE_ID' => 2, 'EVENT_TYPE' => 1,'ENTITY_FIELD' => 'STAGE_ID'); // 'EVENT_TEXT_2' => 'Акты' - не ищет!
$arSelect1 = Array('ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE');
$arResult['history'] = $this->getDealHistoryByFilter1($arFilter1,$arSelect1);



//подключение шаблона Все нужно получать до него!
$this->IncludeComponentTemplate();