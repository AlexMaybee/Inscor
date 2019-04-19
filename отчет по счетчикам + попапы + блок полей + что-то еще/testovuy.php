<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("CRM");

/*Get data from existed deal*/
$arFilter = Array('ID' => 301);
$arSelect = Array('ID','TITLE','STAGE_ID','UF_CRM_1533715533','UF_CRM_1533826216', //Причины провала + дата новой сделки
    'CONTACT_ID',// ID контактов
    'COMPANY_ID', //ID компании
    'UF_CRM_59EE4DC739368', //город
   // 'UF_CRM_5B6C16E90EB4E', //отрасль
    'UF_CRM_5B83AAA0DB130', //отрасль
    'UF_CRM_5B75CA95BD958', //ЕДРПОУ!!!
    'UF_CRM_5B75CA95D62D9', //Поиск по базе акцент!!!
    'UF_CRM_5B75CA95E1791', //Источники new!!!
    //'UF_CRM_1532613531', //Наличие брокера по МОТОР
    'UF_CRM_1535445683', //Наличие брокера по МОТОР NEW
    'UF_CRM_59DBEBA4374C2', //Брокеры
    'ASSIGNED_BY_ID', //ответственный
    'UF_CRM_1532613305575', //ТС по МТСБУ
   // 'UF_CRM_1532613353697', //Текущая СК по ОСГПО и с какого года по ОСГПО - ОТМЕНЕНО!!!
    'UF_CRM_1534156053', //Текущая СК по ОСГПО NEW!!!
    'UF_CRM_1534156848', //C какого года по ОСГПО NEW!!!
    'UF_CRM_1533131953485', //Количество ТС подтвержденное по КАСКО
    'UF_CRM_1532614252', //Целевой лид по мотор или не целевой
    'UF_CRM_1532614341', //К лиду
    'UF_CRM_1532614475', //Руководство
    'UF_CRM_1532614542', //ЛПР по мотор
    'UF_CRM_1532614584347' //Дата следующего этапа работы со сделками
);
$db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
$ar_result = $db_list->GetNext();
/*Get data from existed deal*/



/*Create new deal with relation to old*/
$fields = array(
    'TITLE' => $ar_result['TITLE'].' следующий период (+1 год)',
    'BEGINDATE' => $ar_result['UF_CRM_1533826216'], //дата новой сделки
    'CONTACT_ID' => $ar_result['CONTACT_ID'],
    'COMPANY_ID' => $ar_result['COMPANY_ID'],
    'ASSIGNED_BY_ID' => $ar_result['ASSIGNED_BY_ID'],
    'UF_CRM_59EE4DC739368' => $ar_result['UF_CRM_59EE4DC739368'],
    //'UF_CRM_5B6C16E90EB4E' => $ar_result['UF_CRM_5B6C16E90EB4E'],
    'UF_CRM_5B83AAA0DB130' => $ar_result['UF_CRM_5B83AAA0DB130'],
    'UF_CRM_5B75CA95BD958' => $ar_result['UF_CRM_5B75CA95BD958'],
    'UF_CRM_5B75CA95D62D9' => $ar_result['UF_CRM_5B75CA95D62D9'],
    'UF_CRM_5B75CA95E1791' => $ar_result['UF_CRM_5B75CA95E1791'],
    //'UF_CRM_1532613531' => $ar_result['UF_CRM_1532613531'],
    'UF_CRM_1535445683' => $ar_result['UF_CRM_1535445683'],
    'UF_CRM_59DBEBA4374C2' => $ar_result['UF_CRM_59DBEBA4374C2'],
    'UF_CRM_1532613305575' => $ar_result['UF_CRM_1532613305575'],
    //'UF_CRM_1532613353697' => $ar_result['UF_CRM_1532613353697'], РАЗДЕЛЕНО НА 2 ПОЛЯ НИЖЕ!!!
   // 'UF_CRM_1534156053' => $ar_result['UF_CRM_1534156053'],
    //'UF_CRM_1534156848' => $ar_result['UF_CRM_1534156848'],
    'UF_CRM_1533131953485' => $ar_result['UF_CRM_1533131953485'],
    'UF_CRM_1532614252' => $ar_result['UF_CRM_1532614252'],
    'UF_CRM_1532614341' => $ar_result['UF_CRM_1532614341'],
    'UF_CRM_1532614475' => $ar_result['UF_CRM_1532614475'],
    'UF_CRM_1532614542' => $ar_result['UF_CRM_1532614542'],
    //'UF_CRM_1532614584347' => $ar_result['UF_CRM_1532614584347'],
    'UF_CRM_1533826055' => 283,
);
$newObj = new CcrmDeal;
//$id = $newObj->Add($fields,true,array());
/*Create new deal with relation to old*/

/*echo '<pre>';
print_r($ar_result);
echo '</pre>';
echo $id;
echo $newObj->LAST_ERROR;*/

CModule::IncludeModule("tasks");

$arFields1 = Array(
    "TITLE" => "New task title",
    "DESCRIPTION" => "New description",
    "UF_CRM_TASK" => 'D_322', //слетает при обновлении
);

$ID = 96;

$obTask = new CTasks;
//$success = $obTask->Update($ID, $arFields1);



    $rsTask = CTasks::GetByID(111);
    if ($arTask = $rsTask->GetNext()) {
       /* echo '<pre>';
        print_r($arTask);
        echo '</pre>';*/
    }

$res = CTaskReminders::GetList(
    Array("DATE" => "ASC"),
    Array("TASK_ID" => 105, "USER_ID" => $USER->GetID())
);
$arReminder = $res->GetNext();
/*echo '<pre>';
print_r($arReminder);
echo '</pre>';*/
/*
  [ID] => 2
    [~ID] => 2
    [USER_ID] => 11
    [~USER_ID] => 11
    [TASK_ID] => 102
    [~TASK_ID] => 102
    [REMIND_DATE] => 05.09.2018 14:15:00
    [~REMIND_DATE] => 05.09.2018 14:15:00
    [TYPE] => A
    [~TYPE] => A
    [TRANSPORT] => J
    [~TRANSPORT] => J
    [RECEPIENT_TYPE] => R
    [~RECEPIENT_TYPE] => R
 * */


/*********************
$arFields = Array(
    "TASK_ID" => 104,
    "USER_ID" => 11,
    "REMIND_DATE" => "05.09.2018"." 16:05:00",
    "TYPE" => 'A',
    "TRANSPORT" => 'J',
    'RECEPIENT_TYPE' => 'R'
);

$obTaskReminders = new CTaskReminders;
$ID = $obTaskReminders->Add($arFields);
$success = ($ID>0);



if($success)
{
    echo "Ok!";
}
else
{
    if($e = $APPLICATION->GetException())
        echo "Error: ".$e->GetString();
}
********************/

global $USER;

$userId = $USER->GetID();
$taskId = 105
;
/*$fields1 = array(
    "TITLE" => "Еще раз 222 Новый заголовок, потому как...",
    "UF_CRM_TASK" => 'D_322', //слетает при обновлении
);
$oTaskItem = new CTaskItem($taskId, $userId);
try
{
    $rs = $oTaskItem->Update($fields1);
}
catch(Exception $e)
{
    print('Error');
}*/

/*Удаление Remainders по ID задачи*/
//$delRem = CTaskReminders::DeleteByTaskID(105);
/*Удаление Remainders по ID задачи*/


//################## тест истории ПОДСЧЕТ СЧЕТЧИКОВ!!!!!


echo date('d.m.Y H:i:s').'<br>';


//получение стадий
$stages = getCategoryStages(0);
// $result['stages'] = $stages; //возвращает массив 'STAGE_ID' => 'NAME'

//т.к. почему-то во vue идет авто сортировка по ключам(цифры по возр. -> буквы по алфавиту), приходится переформатировать массив --
// -- т.к. php возвращает стадии в нужном порядке
foreach ($stages as $key => $value){
    $result['stages'][] = ['STAGE_ID' => $key, 'STAGE_NAME' => $value];
}


//получение сделок
$deals_filter = [
    'ID' => 1906, //1727 та, на которой не показывало выигрыш/проигрыш
   // 'ID' => 1882, //1882, создана на Актуализации и на ней висит
];
$deals_select = array('ID','TITLE','STAGE_ID','DATE_CREATE','CLOSEDATE','CLOSED');
$dealMassive = getDealDataByFilter($deals_filter,$deals_select);

//для каждой сделки получаем массив истории переходов
foreach ($dealMassive as $index => $value){



    echo '<br>stage: '.$value['STAGE_ID'].' create: '.$value['DATE_CREATE'];




    $historyFilter = Array('ENTITY_ID' => $value['ID'],'ENTIYY_TYPE_ID' => 2,'ENTITY_FIELD' => 'STAGE_ID'); // 'EVENT_TEXT_2' => 'Акты' - не ищет!
    $historySelect = Array('ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE');
    //$res = getDealHistoryByFilter($historyFilter,$historySelect,$result['stages'],$value['DATE_CREATE'],$value['STAGE_ID']);

    //попытка номер хуй знает сколько!
    $res = getDealHistoryByFilter_2($historyFilter,$historySelect);


    //!!!если истори нет, то отдаем массив стадий с  выбранной текущей и ее счетчиками
    if(!$res){
        $dealMassive[$index]['HISTORY'] = getCurrentStageAndTimeOnIt($result['stages'],$value['DATE_CREATE'],$value['STAGE_ID']);
    }
    //!!!если история есть, нужно считать каждую стадию и выявить текущую
    else{
        //приходы/уходы со стадий
        $counters = calculateEachStageTimeOn($res,$result['stages'],$value['STAGE_ID']);
        $dealMassive[$index]['HISTORY'] = calculateEachStageTime($counters,$value['DATE_CREATE'],$value['STAGE_ID']);
    }


    //($res) ? $dealMassive[$index]['HISTORY'] = $res : $dealMassive[$index]['HISTORY'] = 'НУ НОЛЬ БЛЯТЬ!';
}



echo '<pre>';
print_r($dealMassive);
//print_r($result['stages']);
//print_r($counters);
echo '</pre>';




//ФУНКЦИИ!!!
//получение сделок специалиста по фильтру и указанным к выдаче полям
function getDealDataByFilter($arFilter,$arSelect){
    $deals = [];
    $db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
    while($ar_result = $db_list->GetNext()){
        $ar_result['HREF'] = '/crm/deal/details/'.$ar_result['ID'].'/'; //формируем ссылку для открытия во фрейме сделки
        $deals[] = $ar_result;
    }
    return $deals;
}

//ломается порядок вывода во vue, хотя php дает правильтный порядок, но без id
function getCategoryStages($category_id){
    $stages = \Bitrix\Crm\Category\DealCategory::getStageList($category_id);
    return $stages;
}

//получение истории сделок
function getDealHistoryByFilter($arFilter,$arSelect,$stagesMassive,$dealDateCreate,$curDealStage){
    $deal_history_list = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());

    //!!!ПРОБЛЕМА В ИТЕРАЦИИ!!! нужно вынести foreach стадий за while!!!


    $result = [];
    echo '<pre>';

    $counters = [];
    foreach ($stagesMassive as $key => $value) {

        $counters[$value['STAGE_ID']] = [
            'NAME' => $value['STAGE_NAME'],
            'PERIOD' => '',
            'IS_CURRENT_STAGE' => 0,
        ];

    }

        $historyRes = $deal_history_list->GetNext();

        if($historyRes == false) {
            //  $result[] = 'ПУСТО!123!!';

            foreach ($counters as $stage_id => $field_value){

                if ($stage_id === $curDealStage) {

                    $datetime1 = new DateTime($dealDateCreate);
                    $datetime2 = new DateTime(date('d.m.Y H:i:s'));
                    $interval = $datetime1->diff($datetime2);

                    $years = $interval->format('%y');
                    $months = $interval->format('%m');
                    $days = $interval->format('%d');
                    $hours = $interval->format('%h');
                    $minutes = $interval->format('%i');
                    $seconds = $interval->format('%s');


                    //здесь составляется
                    $period = '';
                    if ($years > 0) $period .= $years . ' лет ';
                    if ($months > 0) $period .= $months . ' мес ';
                    if ($days > 0) $period .= $days . ' дн ';
                    if ($hours > 0) $period .= $hours . ' ч ';
                    if ($minutes > 0) $period .= $minutes . ' мин ';
                    if ($hours == 0 && $minutes == 0) $period .= $seconds . ' сек ';

                    // $period .= $hours.' ч '.$hours.' мин';

//                    $counters[$curDealStage] = [
//                        'NAME' => $curDealStage,
//                        'PERIOD' => $period,
//                        'IS_CURRENT_STAGE' => 1,
//                    ];
                    $counters[$stage_id]['PERIOD'] = $period;
                    $counters[$stage_id]['IS_CURRENT_STAGE'] = 1;

                } else {

                    //                $counters[$value['STAGE_ID']] = [
                    //                    //'NAME' => $value['STAGE_ID'],
                    //                    'PERIOD' => ' x ',
                    //                    'IS_CURRENT_STAGE' => 0,
                    //                ];
                    $counters[$stage_id]['PERIOD'] = ' x ';
                    $counters[$stage_id]['IS_CURRENT_STAGE'] = 0;
                }
            }
            $result = $counters;
        }





        //если были переходы по стадиям после создания сделки
        else{

            foreach ($counters as $stage_id => $field_value){
                $count[$stage_id]['PRIHOD'] = [];
                $count[$stage_id]['UHOD'] = [];

                while ($historyRes = $deal_history_list->GetNext()) {

                    //подсчет уходов со стадий
                    if($historyRes['EVENT_TEXT_1'] === $field_value['NAME']){
//                        if(preg_match('/NEW/',$value['STAGE_ID'])){
//                            $counters[$value['STAGE_ID']]['PRIHOD'][] = $dealDateCreate;
//                            $counters[$value['STAGE_ID']]['UHOD'][] = $historyRes['DATE_CREATE'];
//                        }
//                        else $counters[$value['STAGE_ID']]['UHOD'][] = $historyRes['DATE_CREATE'];
                        $count[][$field_value['NAME']] = $historyRes['EVENT_TEXT_1'];
                    }

                    //подсчет приходов
                    if($historyRes['EVENT_TEXT_2'] === $field_value['NAME']){
                        $counters[$field_value['NAME']]['PRIHOD'][] = $historyRes['DATE_CREATE'];
                    }

                  //  $count[]['name'] = $field_value['NAME'].' - '.$historyRes['EVENT_TEXT_1'];

                }
            }
            $result = $count;
        }


    return $result;
}

//альтернатива от 11.04.2019
function getDealHistoryByFilter_2($arFilter,$arSelect)
{
    $deal_history_list = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());
    $result = 0;
    $massive = [];
    while ($historyRes = $deal_history_list->GetNext()) {
        if($historyRes) $massive[] = $historyRes;
    }
    if($massive) $result = $massive;
    return $result;
}

//выявление текущей стадии и вычисление дней для нее (это если история пустая)
function getCurrentStageAndTimeOnIt($stages,$dealDateCreate,$curDealStage){

    //переформатируем массив стадий в нужный и считаем
    $counters = [];
    foreach ($stages as $key => $value) {

        //вывод всех стадий
        $counters[$key] = [
            'NAME' => $value['STAGE_NAME'],
            'STAGE_ID' => $value['STAGE_ID'],
            'PERIOD' => ' x ',
            'IS_CURRENT_STAGE' => 0,
        ];

        //счетчик в стадии, если она найдена
        if ($value['STAGE_ID'] === $curDealStage) {

            $datetime1 = new DateTime($dealDateCreate);
            $datetime2 = new DateTime(date('d.m.Y H:i:s'));
            $interval = $datetime1->diff($datetime2);

            $years = $interval->format('%y');
            $months = $interval->format('%m');
            $days = $interval->format('%d');
            $hours = $interval->format('%h');
            $minutes = $interval->format('%i');
            $seconds = $interval->format('%s');

            //здесь составляется счетчик для тек. стадии
            $period = '';
            if ($years > 0) $period .= $years . ' лет ';
            if ($months > 0) $period .= $months . ' мес ';
            if ($days > 0) $period .= $days . ' дн ';
            if ($hours > 0) $period .= $hours . ' ч ';
            if ($minutes > 0) $period .= $minutes . ' мин ';
            if ($hours == 0 && $minutes == 0) $period .= $seconds . ' сек ';

            $counters[$key]['PERIOD'] = $period;
            $counters[$key]['IS_CURRENT_STAGE'] = 1;


        }
    }

    return $counters;
}

//а это функция для вычисления переходов по стадиям (если они были)
function calculateEachStageTimeOn($historyMassive,$stages,$curDealStage){
    //вывод всех стадий

    $result = false;

    $counters = [];
    foreach ($stages as $key => $value) {
        $counters[$key] = [
            'NAME' => $value['STAGE_NAME'],
            'STAGE_ID' => $value['STAGE_ID'],
            'PERIOD' => ' - ',
            'IS_CURRENT_STAGE' => 0,
            'UHOD' => [],
            'PRIHOD' => [],
        ];

        foreach ($historyMassive as $index => $historyField){

            //уход со стадии $historyField['EVENT_TEXT_1']
            if($value['STAGE_NAME'] == $historyField['EVENT_TEXT_1']){
                $counters[$key]['UHOD'][] = $historyField['DATE_CREATE'];
            }

            //приход на стадию
            if($value['STAGE_NAME'] == $historyField['EVENT_TEXT_2']){
                $counters[$key]['PRIHOD'][] = $historyField['DATE_CREATE'];
            }
        }

    }

    $result = $counters;

    return $result;
}

//продолжение верхней функции (подсчет дней на каждой стадии)
function calculateEachStageTime($counters,$dealDateCreate,$curDealStage){
    foreach ($counters as $stageID => $massve){
        //определяем текущую стадию
//        if($stageID === $curDealStage)
//            $counters[$stageID]['IS_CURRENT_STAGE'] = 1;


    //считаем кол-во дней на каждой стадии при переходах туда-сюда


        //это стадии проходные (на которые пришли и ушли)
        if((count($massve['PRIHOD']) === count($massve['UHOD'])) && count($massve['PRIHOD']) > 0 ) {

            //Считатаем кол-во дней нахждения на стадии
            for($i = 0; $i <= count($massve['PRIHOD']); $i++ ){

                //берем приход и уход в массивах под одним index (i)
                $datetime1 = new DateTime($massve['PRIHOD'][$i]);
                $datetime2 = new DateTime($massve['UHOD'][$i]);
                $interval = $datetime2->diff($datetime1);
                $years = $interval->format('%y');
                $months = $interval->format('%m');
                $days = $interval->format('%d');
                $hours = $interval->format('%h');
                $mins = $interval->format('%i');
                $secs = $interval->format('%s');

                $counters[$stageID]['COUNTER']['PER_YEARS'] += $years;
                $counters[$stageID]['COUNTER']['PER_MONTHS'] += $months;
                $counters[$stageID]['COUNTER']['PER_DAYS'] += $days;
                $counters[$stageID]['COUNTER']['PER_HOURS'] += $hours;
                $counters[$stageID]['COUNTER']['PER_MINS'] += $mins;
                $counters[$stageID]['COUNTER']['PER_SECS'] += $secs;

               // $counters[$stageID]['CURRENT_STAGE'] = 0;
            }

            //приводим числа в надлежащий вид (чтобы не было типа "6 дн 20 ч 103 мин 58 сек")
            if($counters[$stageID]['COUNTER']['PER_SECS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_MINS'] += round(($counters[$stageID]['COUNTER']['PER_SECS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_SECS'] = $counters[$stageID]['COUNTER']['PER_SECS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_MINS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_HOURS'] += round(($counters[$stageID]['COUNTER']['PER_MINS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_MINS'] = $counters[$stageID]['COUNTER']['PER_MINS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_HOURS'] >= 60) {
                $counters[$stageID]['COUNTER']['PER_DAYS'] += round(($counters[$stageID]['COUNTER']['PER_HOURS'] / 60), 0);
                $counters[$stageID]['COUNTER']['PER_HOURS'] = $counters[$stageID]['COUNTER']['PER_HOURS'] % 60;
            }


            //здесь составляется
            $counters[$stageID]['PERIOD'] = '';
            if($counters[$stageID]['COUNTER']['PER_YEARS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_YEARS'].' лет ';
            if($counters[$stageID]['COUNTER']['PER_MONTHS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MONTHS'].' мес ';
            if($counters[$stageID]['COUNTER']['PER_DAYS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_DAYS'].' дн ';
            if($counters[$stageID]['COUNTER']['PER_HOURS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_HOURS'].' ч ';
            if($counters[$stageID]['COUNTER']['PER_MINS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MINS'].' мин '
                .$counters[$stageID]['COUNTER']['PER_SECS'].' сек ';
            if(
                $counters[$stageID]['COUNTER']['PER_HOURS'] == 0 && $counters[$stageID]['COUNTER']['PER_MINS'] == 0
            )
                $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_SECS'].' сек ';
            //  else $counters[$stageID]['PERIOD'] .= $hours.' ч '.$minutes.' мин';
        }


        //считаем сколько находилось на начальной стадии (при создании), т.е. уходов со стадии > чем приходов
        if(count($massve['UHOD']) > count($massve['PRIHOD'])) {


            for($i = 0; $i <= count($massve['UHOD']); $i++ ) {

            //    $counters[$stageID]['TEST'] .= $massve['UHOD'][$i].'; ';

                if ($i == 0) {
                    $datetime1 = new DateTime($massve['UHOD'][$i]);
                    $datetime2 = new DateTime($dealDateCreate);
                } else {
                    $datetime1 = new DateTime($massve['PRIHOD'][$i-1]);
                    $datetime2 = new DateTime($massve['UHOD'][$i]);
                }

                $interval = $datetime1->diff($datetime2);

                $years = $interval->format('%y');
                $months = $interval->format('%m');
                $days = $interval->format('%d');
                $hours = $interval->format('%h');
                $mins = $interval->format('%i');
                $secs = $interval->format('%s');



                //здесь составляется
                $counters[$stageID]['COUNTER']['PER_YEARS'] += $years;
                $counters[$stageID]['COUNTER']['PER_MONTHS'] += $months;
                $counters[$stageID]['COUNTER']['PER_DAYS'] += $days;
                $counters[$stageID]['COUNTER']['PER_HOURS'] += $hours;
                $counters[$stageID]['COUNTER']['PER_MINS'] += $mins;
                $counters[$stageID]['COUNTER']['PER_SECS'] += $secs;

            }

            //приводим числа в надлежащий вид (чтобы не было типа "6 дн 20 ч 103 мин 58 сек")
            if($counters[$stageID]['COUNTER']['PER_SECS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_MINS'] += round(($counters[$stageID]['COUNTER']['PER_SECS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_SECS'] = $counters[$stageID]['COUNTER']['PER_SECS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_MINS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_HOURS'] += round(($counters[$stageID]['COUNTER']['PER_MINS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_MINS'] = $counters[$stageID]['COUNTER']['PER_MINS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_HOURS'] >= 60) {
                $counters[$stageID]['COUNTER']['PER_DAYS'] += round(($counters[$stageID]['COUNTER']['PER_HOURS'] / 60), 0);
                $counters[$stageID]['COUNTER']['PER_HOURS'] = $counters[$stageID]['COUNTER']['PER_HOURS'] % 60;
            }


            //здесь составляется
            $counters[$stageID]['PERIOD'] = '';
            if($counters[$stageID]['COUNTER']['PER_YEARS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_YEARS'].' лет ';
            if($counters[$stageID]['COUNTER']['PER_MONTHS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MONTHS'].' мес ';
            if($counters[$stageID]['COUNTER']['PER_DAYS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_DAYS'].' дн ';
            if($counters[$stageID]['COUNTER']['PER_HOURS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_HOURS'].' ч ';
            if($counters[$stageID]['COUNTER']['PER_MINS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MINS'].' мин '
                .$counters[$stageID]['COUNTER']['PER_SECS'].' сек ';
            if(
                $counters[$stageID]['COUNTER']['PER_HOURS'] == 0 && $counters[$stageID]['COUNTER']['PER_MINS'] == 0
            )
                $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_SECS'].' сек ';
            //  else $counters[$stageID]['PERIOD'] .= $hours.' ч '.$minutes.' мин';



        }


        //вычисляем текущую стадию сделки
        if(count($massve['PRIHOD']) > count($massve['UHOD']) ) {
            //  echo '<br> Сейчас на стадии: '.$stage;

            $num = count($massve['PRIHOD']);

            //Считаем сколько уже дней находится на текущей стадии (с учетом переходов туда-сюда)
            for($i = 0; $i <= $num; $i++ ) {

                if ($i == $num) {
                    $datetime1 = new DateTime($massve['PRIHOD'][$i]);
                    $datetime2 = new DateTime(date('d.m.Y H:i:s'));
                } else {
                    $datetime1 = new DateTime($massve['PRIHOD'][$i]);
                    $datetime2 = new DateTime($massve['UHOD'][$i]);
                }


                $interval = $datetime1->diff($datetime2);

                $years = $interval->format('%y');
                $months = $interval->format('%m');
                $days = $interval->format('%d');
                $hours = $interval->format('%h');
                $mins = $interval->format('%i');
                $secs = $interval->format('%s');

                //здесь составляется

//                $counters[$stageID]['COUNTER'][] = [
//                    'PER_YEARS' => $years,
//                    'PER_MONTHS' => $months,
//                    'PER_DAYS' => $days,
//                    'PER_HOURS' => $hours,
//                    'PER_MINS' => $mins,
//                    'PER_SECS' => $secs,
//                ];
//            }
                $counters[$stageID]['COUNTER']['PER_YEARS'] += $years;
                $counters[$stageID]['COUNTER']['PER_MONTHS'] += $months;
                $counters[$stageID]['COUNTER']['PER_DAYS'] += $days;
                $counters[$stageID]['COUNTER']['PER_HOURS'] += $hours;
                $counters[$stageID]['COUNTER']['PER_MINS'] += $mins;
                $counters[$stageID]['COUNTER']['PER_SECS'] += $secs;
            }

            //приводим числа в надлежащий вид (чтобы не было типа "6 дн 20 ч 103 мин 58 сек")
            if($counters[$stageID]['COUNTER']['PER_SECS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_MINS'] += round(($counters[$stageID]['COUNTER']['PER_SECS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_SECS'] = $counters[$stageID]['COUNTER']['PER_SECS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_MINS'] >= 60){
                $counters[$stageID]['COUNTER']['PER_HOURS'] += round(($counters[$stageID]['COUNTER']['PER_MINS'] / 60),0);
                $counters[$stageID]['COUNTER']['PER_MINS'] = $counters[$stageID]['COUNTER']['PER_MINS'] % 60;
            }
            if($counters[$stageID]['COUNTER']['PER_HOURS'] >= 60) {
                $counters[$stageID]['COUNTER']['PER_DAYS'] += round(($counters[$stageID]['COUNTER']['PER_HOURS'] / 60), 0);
                $counters[$stageID]['COUNTER']['PER_HOURS'] = $counters[$stageID]['COUNTER']['PER_HOURS'] % 60;
            }

                //здесь составляется
            $counters[$stageID]['PERIOD'] = '';
            if($counters[$stageID]['COUNTER']['PER_YEARS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_YEARS'].' лет ';
            if($counters[$stageID]['COUNTER']['PER_MONTHS'] != 0)  $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MONTHS'].' мес ';
            if($counters[$stageID]['COUNTER']['PER_DAYS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_DAYS'].' дн ';
            if($counters[$stageID]['COUNTER']['PER_HOURS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_HOURS'].' ч ';
            if($counters[$stageID]['COUNTER']['PER_MINS'] != 0) $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_MINS'].' мин ';
            if(
                $counters[$stageID]['COUNTER']['PER_HOURS'] == 0 && $counters[$stageID]['COUNTER']['PER_MINS'] == 0
            )
                $counters[$stageID]['PERIOD'] .= $counters[$stageID]['COUNTER']['PER_SECS'].' сек ';
            //  else $counters[$stageID]['PERIOD'] .= $hours.' ч '.$minutes.' мин';

            $counters[$stageID]['IS_CURRENT_STAGE'] = 1;
        }


    }
    return $counters;
}