<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use \Bitrix\Main;
use \Bitrix\Main\Loader;
use Bitrix\Main\UserTable;



$APPLICATION->SetTitle("Счетчики по стадиям");


$APPLICATION->IncludeComponent(
    "itlogic:stage_counters_report",
    ".default",
    Array(
    ),
    false
);


?>




