<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

require_once ('lib/customevent.php');

use Crmgenesis\Newworktimecontrol\customevent;

$obj = new customevent;