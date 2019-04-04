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






    

    //функция счетчиков
    public function getStageCounters($deal_id){

        $message = 'ничего!';
        $data = false;

        //запрос даты начала сделки
        $dealFilter = Array('ID' => $deal_id);
        $dealSelect = Array('ID','TITLE','STAGE_ID','BEGINDATE');
        $dealData = $this->getDealData('',$dealFilter,$dealSelect);

        if(!$dealData['BEGINDATE']) $message = 'Нет такой сделки?!';
        elseif(strtotime($dealData['BEGINDATE']) > strtotime('now')){
            $message = 'Ну не будет же светиться первая стадия до ';
        }
        else{
            //Запрос в историю сделки по id
            $arFilter1 = Array('ENTITY_ID' => $deal_id,'ENTIYY_TYPE_ID' => 2, 'EVENT_TYPE' => 1,'ENTITY_FIELD' => 'STAGE_ID'); // 'EVENT_TEXT_2' => 'Акты' - не ищет!
            $arSelect1 = Array('ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE');
            $history = $this->getDealHistoryForCounters($arFilter1,$arSelect1,$dealData['BEGINDATE']);
            if(!$history) $message = 'Что-то с массивом истории что-ли?';
            else{
                $data = $history;
                $message = 'Счетчики стадий работают как надо!';
            }
        }

        $result = array('COUNTERS' => $data, 'MESSAGE' => $message);

        $this->sentAnswer($result);
    }








    //ответ в консоль
    private function sentAnswer($answ){
        echo json_encode($answ);
    }

    //достаем данные по ID сделки
    private function getDealData($ID,$arFilter,$arSelect){
        $db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
        if($ar_result = $db_list->GetNext()) return $ar_result;
    }

    //получение истории сделки
    public function getDealHistoryForCounters($arFilter,$arSelect,$dealBeginDate){

        //массив данных пользователя
        $COUNTERS = array(
            'ACTUALIZATION' => array(
                'PRIHOD' => array($dealBeginDate),
                'STAGE_NAME' => 'Актуализация',
            ),
            'OJIDATION' => array(),
            'TENDERATION' => array(),
            'DOP_VOPROSION' => array(),
            'WONSION' => array(),
            'LOSION' => array(),
            'SLITION' => array(),
        );

        $db_list1 = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());

        //  $data = array();

        while($ar_result1 = $db_list1->GetNext()) {
          //  $data[] = $ar_result1;

            if($ar_result1['EVENT_TEXT_1'] == 'Актуализация'){
                $COUNTERS['ACTUALIZATION']['UHOD'][] = $ar_result1['DATE_CREATE'];

                /*//Добавляем время создания сделки первым элементом в масив переходов на стадию "Актуализация" для дальнейшего вычитания времени, если переход со стадии был
                $COUNTERS['ACTUALIZATION']['PRIHOD'][0] = $deal_start_date;*/

            }
            if($ar_result1['EVENT_TEXT_2'] == 'Актуализация'){
                $COUNTERS['ACTUALIZATION']['PRIHOD'][] = $ar_result1['DATE_CREATE'];
            }

            //Ожидание
            if($ar_result1['EVENT_TEXT_1'] == 'Ожидание'){
                $COUNTERS['OJIDATION']['UHOD'][] = $ar_result1['DATE_CREATE'];
            }
            if($ar_result1['EVENT_TEXT_2'] == 'Ожидание'){
                $COUNTERS['OJIDATION']['PRIHOD'][] = $ar_result1['DATE_CREATE'];
                $COUNTERS['OJIDATION']['STAGE_NAME'] = $ar_result1['EVENT_TEXT_2'];
            }

            //Тендер
            if($ar_result1['EVENT_TEXT_1'] == 'Тендер'){
                $COUNTERS['TENDERATION']['UHOD'][] = $ar_result1['DATE_CREATE'];
            }
            if($ar_result1['EVENT_TEXT_2'] == 'Тендер'){
                $COUNTERS['TENDERATION']['PRIHOD'][] = $ar_result1['DATE_CREATE'];
                $COUNTERS['TENDERATION']['STAGE_NAME'] = $ar_result1['EVENT_TEXT_2'];
            }

            //Дополнительные вопросы
            if($ar_result1['EVENT_TEXT_1'] == 'Дополнительные вопросы'){
                $COUNTERS['DOP_VOPROSION']['UHOD'][] = $ar_result1['DATE_CREATE'];
            }
            if($ar_result1['EVENT_TEXT_2'] == 'Дополнительные вопросы'){
                $COUNTERS['DOP_VOPROSION']['PRIHOD'][] = $ar_result1['DATE_CREATE'];
                $COUNTERS['DOP_VOPROSION']['STAGE_NAME'] = $ar_result1['EVENT_TEXT_2'];
            }

        }

        //Пересчет массива и вывод данных
        foreach($COUNTERS as $stage => $massive){

            if((count($massive['PRIHOD']) === count($massive['UHOD'])) && count($massive['PRIHOD']) > 0 ) {

                //   echo '<br>'.$stage.' (ПРИХОД/УХОД) : '.count($massive['PRIHOD']).' / '.count($massive['UHOD']);

                //Считатаем кол-во дней нахждения на стадии
                // $COUNTERS[$stage]['PER_DAYS'] = 0;
                for($i = 0; $i <= count($massive['PRIHOD']); $i++ ){

                    $datetime1 = new DateTime($massive['PRIHOD'][$i]);
                    $datetime2 = new DateTime($massive['UHOD'][$i]);
                    $interval = $datetime2->diff($datetime1);
                    $years = $interval->format('%y');
                    $months = $interval->format('%m');
                    $days = $interval->format('%d');
                    $hours = $interval->format('%h');

                    $COUNTERS[$stage]['COUNTER']['PER_YEARS'] += $years;
                    $COUNTERS[$stage]['COUNTER']['PER_MONTHS'] += $months;
                    $COUNTERS[$stage]['COUNTER']['PER_DAYS'] += $days;
                    $COUNTERS[$stage]['COUNTER']['PER_HOURS'] += $hours;


                    $COUNTERS[$stage]['CURRENT_STAGE'] = 0;

                }
            }

            if(count($massive['PRIHOD']) >count($massive['UHOD']) ) {
                //  echo '<br> Сейчас на стадии: '.$stage;
                $COUNTERS[$stage]['CURRENT_STAGE'] = 1;
                //Считаем сколько уже дней находится на текущей стадии
                $last_prihod = array_pop($massive['PRIHOD']);
                $datetime1 = new DateTime($last_prihod);
                $datetime2 = new DateTime(date('d.m.Y H:i:s'));
                $interval = $datetime1->diff($datetime2);

                $years = $interval->format('%y');
                $months = $interval->format('%m');
                $days = $interval->format('%d');
                $hours = $interval->format('%h');
                $minutes = $interval->format('%i');

                //здесь составляется
                $COUNTERS[$stage]['DAYS_ON_CUR_STAGE'] = '';
                if($years > 0)  $COUNTERS[$stage]['DAYS_ON_CUR_STAGE'] .= $years.' лет ';
                if($months > 0)  $COUNTERS[$stage]['DAYS_ON_CUR_STAGE'] .= $months.' мес. ';
                if($days > 0) $COUNTERS[$stage]['DAYS_ON_CUR_STAGE'] .= $days.' дней ';
                else $COUNTERS[$stage]['DAYS_ON_CUR_STAGE'] .= $hours.' часов '.$minutes.' мин.';

            }
        }

        //окончательный массив
        $anwer = array(
            'CUR_STAGE' => array(),
            'OTHER_STAGES' => array(),
        );

        foreach ($COUNTERS as $stage => $field){

            //вывод текущей стадии
            if(isset($field['DAYS_ON_CUR_STAGE'])) {
                $anwer['CUR_STAGE'] = array(
                    'NAME' => $field['STAGE_NAME'],
                    'COUNTER' => $field['DAYS_ON_CUR_STAGE'],
                );
            }
            //вывод прошедших стадий
            if($field['COUNTER']){
                $date = '';
                if($field['COUNTER']['PER_YEARS'] > 0) $date .= $field['COUNTER']['PER_YEARS'].' лет ';
                if($field['COUNTER']['PER_MONTHS'] > 0) $date .= $field['COUNTER']['PER_MONTHS'].' мес. ';
                if($field['COUNTER']['PER_DAYS'] > 0) $date .= $field['COUNTER']['PER_DAYS'].' дней ';

                if($field['COUNTER']['PER_HOURS'] > 0) $date .= $field['COUNTER']['PER_HOURS'].' часов';

                $anwer['OTHER_STAGES'][] = array(
                    'NAME' => $field['STAGE_NAME'],
                    'PERIOD' => $date,
                );
            }

        }

        return $anwer;
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
if($_POST['ACTION'] == 'GIVE_ME_STAGE_COUNTERS') $obj->getStageCounters($_POST['DEAL_ID']);