<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-type: application/json');

CModule::IncludeModule("CRM");

class MyDealClass{

    public function giveMeDealData($ID){
        $arFilter = Array('ID' => $ID);
        $arSelect = Array('ID','STAGE_ID','UF_CRM_1533715533','UF_CRM_1533826216','UF_CRM_1533826350'); //Причины провала + Дата следующей сделки + Причина слива
        $dealData = Array('result' => $this->getDealData($ID,$arFilter,$arSelect));
        $this->sentAnswer($dealData);
    }

    public function saveReasonsToDeal($id,$popupType,$reasons = false,$new_deal_date = false){

        //Объект класса для обновления и создания сделок
        $entity = new CCrmDeal(true);//true - проверять права на доступ

        $deal_create_result = 'сделку создавать и не ну';


        if($popupType == 'LOSE' || $popupType == 'INSURED'){
            $arFilter = Array('ID' => $id);
            $arSelect = Array('ID','TITLE','STAGE_ID','UF_CRM_1533715533','UF_CRM_1533826216', //Причины провала + дата новой сделки
                'CONTACT_ID',// ID контактов
                'COMPANY_ID', //ID компании
                'UF_CRM_59EE4DC739368', //город
                'UF_CRM_5BB1DE9BCB83A', //город (л)
                //'UF_CRM_5B83AAA0DB130', //отрасль НОВ Эмиль
                'UF_CRM_5B8E54EF007FC', //отрасль НОВ Эмиль 04.09
                'UF_CRM_5BB1DE9C49D2C', //отрасль (л) НОВ Эмиль 04.09
                'UF_CRM_5B75CA95BD958', //ЕДРПОУ!!!
                'UF_CRM_5B75CA95D62D9', //Поиск по базе акцент!!!
                'UF_CRM_5B75CA95E1791', //Источники new!!!
                'UF_CRM_1535445683', //Наличие брокера по МОТОР NEW
                'UF_CRM_59DBEBA4374C2', //Брокеры
                'ASSIGNED_BY_ID', //ответственный
                'UF_CRM_1532613305575', //ТС по МТСБУ
                'UF_CRM_1534156053', //Текущая СК по ОСГПО NEW
                'UF_CRM_1534156848', //Начало страхования ОСГПО NEW
                'UF_CRM_1533131953485', //Количество ТС подтвержденное по КАСКО
                'UF_CRM_1532614252', //Целевой лид по мотор или не целевой
                'UF_CRM_1532614341', //К лиду
              //  'UF_CRM_1532614475', //Руководство
               // 'UF_CRM_1532614542', //ЛПР по мотор
            );
            //Поля для будующей сделки
            $savedDealData = $this->getDealData($id,$arFilter,$arSelect);

            //Создаем новую сделку и получаем ее ID, чтобы вставить в поле ниже
            $newFields = array(
                'TITLE' => $savedDealData['TITLE'].' следующий период (+1 год)',
                'BEGINDATE' => $new_deal_date, //дата новой сделки
                'CONTACT_ID' => $savedDealData['CONTACT_ID'],
                'COMPANY_ID' => $savedDealData['COMPANY_ID'],
                'ASSIGNED_BY_ID' => $savedDealData['ASSIGNED_BY_ID'],
                'UF_CRM_59EE4DC739368' => $savedDealData['UF_CRM_59EE4DC739368'],
                'UF_CRM_5BB1DE9BCB83A' => $savedDealData['UF_CRM_5BB1DE9BCB83A'],
                //'UF_CRM_5B83AAA0DB130' => $savedDealData['UF_CRM_5B83AAA0DB130'],
                'UF_CRM_5B8E54EF007FC' => $savedDealData['UF_CRM_5B8E54EF007FC'],
                'UF_CRM_5BB1DE9C49D2C' => $savedDealData['UF_CRM_5BB1DE9C49D2C'],
                'UF_CRM_5B75CA95BD958' => $savedDealData['UF_CRM_5B75CA95BD958'],
                'UF_CRM_5B75CA95D62D9' => $savedDealData['UF_CRM_5B75CA95D62D9'],
                'UF_CRM_5B75CA95E1791' => $savedDealData['UF_CRM_5B75CA95E1791'],
                'UF_CRM_1535445683' => $savedDealData['UF_CRM_1535445683'],
                'UF_CRM_59DBEBA4374C2' => $savedDealData['UF_CRM_59DBEBA4374C2'],
                'UF_CRM_1532613305575' => $savedDealData['UF_CRM_1532613305575'],
                'UF_CRM_1534156053' => $savedDealData['UF_CRM_1534156053'],//Текущая СК по ОСГПО NEW
                'UF_CRM_1534156848' => $savedDealData['UF_CRM_1534156848'],//Начало страхования ОСГПО NEW
                'UF_CRM_1533131953485' => $savedDealData['UF_CRM_1533131953485'],
                'UF_CRM_1532614252' => $savedDealData['UF_CRM_1532614252'],
                'UF_CRM_1532614341' => $savedDealData['UF_CRM_1532614341'],
             //   'UF_CRM_1532614475' => $savedDealData['UF_CRM_1532614475'],
             //   'UF_CRM_1532614542' => $savedDealData['UF_CRM_1532614542'],
                'UF_CRM_1533826055' => $id,
            );

            //создаем новую сделку и получаем ее ID, который вставляем в старую
            $newDealId = $entity->add($newFields);


            $newDealId == false ? $deal_create_result = $entity->LAST_ERROR : $deal_create_result = $newDealId;
            //$deal_create_result = $newDealId;

            //если новая сделка не создалась, не вносим причины и дату новой сделки в текущую
            if($newDealId == true){
                //вставляем в поле UF_CRM_1533715533 (Причины провала при Lose Popup) или в UF_CRM_1533826350 (Причины слива при Waisted Popup)
                $reasonField = '';
               // $popupType == 'LOSE' ? $reasonField = 'UF_CRM_1533715533' : $reasonField = 'UF_CRM_1533826350';
                //Заполняем поля в текущей сделке
                if($popupType == 'LOSE'){
                    $fields = array(
                        'UF_CRM_1533715533' => $reasons, // причины провала
                        'UF_CRM_1533826216' => $new_deal_date, // дата след. сделки
                        'UF_CRM_1533826141' => $newDealId, // ID след. сделки
                    );
                }
                else{
                    $fields = array(
                        'UF_CRM_1533826216' => $new_deal_date, // дата след. сделки
                        'UF_CRM_1533826141' => $newDealId, // ID след. сделки
                    );
                }
            }
            else{
                if($popupType == 'LOSE'){
                    $fields = array(
                        'UF_CRM_1533715533' => '', // причины провала
                        'UF_CRM_1533826216' => '', // дата след. сделки
                        'UF_CRM_1533826141' => '', // ID след. сделки
                    );
                }
                else{
                    $fields = array(
                        'UF_CRM_1533826216' => '', // дата след. сделки
                        'UF_CRM_1533826141' => '', // ID след. сделки
                    );
                }
            }
            //если новая сделка не создалась, не вносим причины и дату новой сделки в текущую

        }
        else{
          //  $entity = new CCrmDeal(true);//true - проверять права на доступ

            //Если POPUP != LOSE and != INSURED, т.е. POPUP = WAISTED
            $fields = array(
                'UF_CRM_1533826350' => $reasons, // причины провала
            );
        }

        //Обновление текущей сделки
        $res = $entity->update($id,$fields);

        //отправление true
        $test = array('result' => $res,'create_deal_result' => $deal_create_result);
        $this->sentAnswer($test);
    }






    

    //функции счетчиков

    //ответ в консоль
    private function sentAnswer($answ){
        echo json_encode($answ);
    }

    //достаем данные по ID сделки
    private function getDealData($ID,$arFilter,$arSelect){
        $db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
        if($ar_result = $db_list->GetNext()) return $ar_result;
    }



    //30.04.2019
    public function getStageCountersNew($deal_id){
        $result = [
            'result' => false,
            'error' => false,
        ];

        //запрос даты начала сделки
        $dealFilter = ['ID' => $deal_id];
        $dealSelect = ['ID','TITLE','STAGE_ID','CATEGORY_ID','DATE_CREATE'];
        $dealData = $this->getDealData('',$dealFilter,$dealSelect);
        if(!$dealData) $result['error'] = 'По ID '. $deal_id.' не найдена сделка!';
        else{
           // $result['result'] = $dealData;

            //получаем массив стадий для направления
            $stagesMass = $this->getCategoryStages($dealData['CATEGORY_ID']);
            $stagesNew = [];
            foreach ($stagesMass as $key => $value){
                $stagesNew[] = ['STAGE_ID' => $key, 'STAGE_NAME' => $value];
            }
           // $result['stages'] = $stagesNew;

            //получение списка истории сделки по фильтру
            $historyFilter = ['ENTITY_ID' => $dealData['ID'],'ENTIYY_TYPE_ID' => 2, 'EVENT_TYPE' => 1,'ENTITY_FIELD' => 'STAGE_ID']; // 'EVENT_TEXT_2' => 'Акты' - не ищет!
            $historySelect = ['ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE'];
            $historyMassive = $this->getDealHistoryByFilter($historyFilter,$historySelect);

            //это если создали и не переходили на другие стадии
            if(!$historyMassive){
                $result['result'] = $this->getCurrentStageAndTimeOnIt($stagesNew,$dealData['DATE_CREATE'],$dealData['STAGE_ID']);
            }
            else{
                //приходы/уходы со стадий
                $counters = $this->calculateEachStageTimeOn($historyMassive,$stagesNew);
             //   $result['counters'] = $counters;

                $result['result'] = $this->calculateEachStageTime($counters,$dealData['DATE_CREATE']);
            }

            //$result['result'] = $historyMassive;

        }


            $this->sentAnswer($result);
    }

    private function getDealHistoryByFilter($arFilter,$arSelect)
    {
        $deal_history_list = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());
        $result = false;
        $massive = [];
        while ($historyRes = $deal_history_list->GetNext()) {
            if($historyRes) $massive[] = $historyRes;
        }
        if($massive) $result = $massive;
        return $result;
    }

    private function getCategoryStages($category_id){
        $stages = \Bitrix\Crm\Category\DealCategory::getStageList($category_id);
        return $stages;
    }

    //выявление текущей стадии и вычисление дней для нее (это если история пустая)
    private function getCurrentStageAndTimeOnIt($stages,$dealDateCreate,$curDealStage){

        //переформатируем массив стадий в нужный и считаем
        $counters = [];
        foreach ($stages as $key => $value) {

            //вывод всех стадий
            $counters[$key] = [
                'NAME' => $value['STAGE_NAME'],
                'STAGE_ID' => $value['STAGE_ID'],
                'PERIOD' => 0,
                'IS_CURRENT_STAGE' => 0,
                'OVER_TIME' => 0,
            ];

            //счетчик в стадии, если она найдена
            if ($value['STAGE_ID'] == $curDealStage) {

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

                //14.04.2019
                //выделяем стадию "актуализация", если от 10 до дней и от 30 дней
                if(preg_match('/NEW/',$curDealStage) && $days >= 10 && $days < 30) $counters[$key]['OVER_TIME'] = 1;
                if((preg_match('/NEW/',$curDealStage) && $days >= 30)
                    || (preg_match('/NEW/',$curDealStage) && $months > 0)
                    || (preg_match('/NEW/',$curDealStage) && $years > 0)
                ) $counters[$key]['OVER_TIME'] = 2;


            }
        }

        return $counters;
    }


    //а это функция для вычисления переходов по стадиям (если они были)
    private function calculateEachStageTimeOn($historyMassive,$stages){
        //вывод всех стадий

        $result = false;

        $counters = [];
        foreach ($stages as $key => $value) {
            $counters[$key] = [
                'NAME' => $value['STAGE_NAME'],
                'STAGE_ID' => $value['STAGE_ID'],
                'PERIOD' => 0,
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
    private function calculateEachStageTime($counters,$dealDateCreate){
        foreach ($counters as $stageID => $massve){

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

                //Считаем сколько уже дней находится на текущей стадии (с учетом переходов туда-сюда)
                for($i = 0; $i <= count($massve['PRIHOD']); $i++ ){

                    if($i == count($massve['PRIHOD'])){
                        $datetime1 = new DateTime($massve['PRIHOD'][$i]);
                        $datetime2 = new DateTime(date('d.m.Y H:i:s'));
                    }
                    else{
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

}

$obj = new MyDealClass();

//сохранение причин провала из popup
if($_POST['POPUP'] == 'LOSE') $obj->saveReasonsToDeal($_POST['ID'],$_POST['POPUP'],$_POST['DATA'],$_POST['DEAL_DATE']);
if($_POST['POPUP'] == 'WAISTED') $obj->saveReasonsToDeal($_POST['ID'],$_POST['POPUP'],$_POST['DATA'],false);
if($_POST['POPUP'] == 'INSURED') $obj->saveReasonsToDeal($_POST['ID'],$_POST['POPUP'],false,$_POST['DEAL_DATE']); //РАЗОБРАТЬСЯ С ЭТИМ ПРИКОЛОМ!


//получение причин провала и стадии до сохранения
if(!$_POST['POPUP'] && $_POST['ID']) $obj->giveMeDealData($_POST['ID']);


//Счетчики стадий для сделок

//30.04.2019 Переделка счетчиков в сделках, чтобы универсально и для всех направлений работало!
if($_POST['ACTION'] == 'GIVE_ME_STAGE_COUNTERS_NEW') $obj->getStageCountersNew($_POST['DEAL_ID']);