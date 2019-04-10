
<?php

class DealCategoryStageCounters{

    //ID технических пользователей
    public $techAccounts = [1,2,12];

    public function test(){
        $this->sentAnswer('DealCategoryStageCounters class test method!');
    }

    //получение направелний сделко для вставки в список ыудусе
    public function getCategoriesForSelect(){
        $categoryIds = \Bitrix\Crm\Category\DealCategory::getAllIDs();
        foreach ($categoryIds as $categoryId){

            //проверяем, чтобы в направлении были сделки
            $hasDeals = $this->checkDealsInCategoryById($categoryId);
            if($hasDeals){
                $massive[] = [
                    'ID' => $categoryId,
                    'NAME' => $this->getCategoryNameById($categoryId),
                    // 'STAGES' => $this->getCategoryStages($catId),
                ];
            }

        }
        $this->sentAnswer($massive);
    }

    //получение стадий по id направления + вывод статистики
    public function getStatisticsByFilter($data){
        $result = [
            'statistics' => false,
            'stages' => false,
            'error' => false,
        ];

        //стадии сделок для направления
        $stages = $this->getCategoryStages($data['CATEGORY_ID']);
       // $result['stages'] = $stages; //возвращает массив 'STAGE_ID' => 'NAME'

        //т.к. почему-то во vue идет авто сортировка по ключам(цифры по возр. -> буквы по алфавиту), приходится переформатировать массив --
        // -- т.к. php возвращает стадии в нужном порядке
        foreach ($stages as $key => $value){
            $result['stages'][] = ['STAGE_ID' => $key, 'STAGE_NAME' => $value];
        }


        //массив сделок по фильтру (направление, дата с, дата по)
        $deals_filter = [
            'CATEGORY_ID' => $data['CATEGORY_ID'], //стадии из 3-х направлений - Акты, отзывы, завершено (выграно)
            ">=BEGINDATE" => date('d.m.Y', strtotime($data['DATE_START'])), //date('m.Y',strtotime('-1 month'))
            "<=BEGINDATE" => date('d.m.Y', strtotime($data['DATE_FINISH'])), //date('m.Y',strtotime('-1 month'))
          //  "<=CLOSEDATE" => date('d.m.Y', strtotime($data['DATE_FINISH'])),
          //  'CLOSED' => ['N','Y'],
        ];

        //Если выбран пользователь в фильтре, учитываем его тоже
        if($data['ASSIGNED_BY_ID']) $deals_filter['ASSIGNED_BY_ID'] = $data['ASSIGNED_BY_ID']; //ВОЗВРАЩАЕТ TRUE/FALSE в ВИДЕ СТРОКИ
        //Если выбрана галка "Учитывать закрытые сделки", добавляем в фильтр
        if($data['ONLY_OPENED_DEALS'] == 'true') $deals_filter['CLOSED'] = 'N'; //ВОЗВРАЩАЕТ TRUE/FALSE в ВИДЕ СТРОКИ

        $deals_select = array('ID','TITLE','STAGE_ID','DATE_CREATE','CLOSEDATE','CLOSED');
        $dealMassive = $this->getDealDataByFilter($deals_filter,$deals_select);

        //теперь считаем кол-во дней и т.д. на каждой стадии
        foreach ($dealMassive as $index => $value){

            $historyFilter = Array('ENTITY_ID' => $value['ID'],'ENTIYY_TYPE_ID' => 2, 'EVENT_TYPE' => 1,'ENTITY_FIELD' => 'STAGE_ID'); // 'EVENT_TEXT_2' => 'Акты' - не ищет!
            $historySelect = Array('ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE');

            //массив событий по переходам по воронке
            //!!! Закончить здесь с подсчетом срока пребывания на каждой стадии
            $res = $this->getDealHistoryByFilter($historyFilter,$historySelect,$result['stages'],$value['DATE_CREATE'],$value['STAGE_ID']); //,$value['DATE_CREATE'],$result['stages']
            $dealMassive[$index]['HISTORY'] = $res;
        }

        $result['statistics'] = $dealMassive;
        $result['FILTER_DATA'] = $data;
        $this->sentAnswer($result);
    }

    //список ответственных в селект
    public function getAssignedForSelect(){
        $result = [];
        $cUser = new CUser;
        $sort_by = "ID";
        $sort_ord = "ASC";
        $arFilter = [];
        $dbUsers = $cUser->GetList($sort_by, $sort_ord, $arFilter);
        $users = [
            ['ID' => '', 'NAME' => 'Не выбрано'],
        ];
        while ($arUser = $dbUsers->Fetch())
        {
            //убираем ненужные тех аккаунты, свой оставляем
            if(!in_array($arUser['ID'],$this->techAccounts)) $users[] = $arUser;
        }
        $result = $users;
        $this->sentAnswer($result);
    }


    //ответ в консоль
    private function sentAnswer($answ){
        echo json_encode($answ);
    }

    //для проверки наличия сделок в направлении
    private function checkDealsInCategoryById($category_id){
        $result = \Bitrix\Crm\Category\DealCategory::hasDependencies($category_id);
        return $result;
    }

    private function getCategoryNameById($category_id){
        return $name = \Bitrix\Crm\Category\DealCategory::getName($category_id);
    }

    //ломается порядок вывода во vue, хотя php дает правильтный порядок, но без id
    private function getCategoryStages($category_id){
        $stages = \Bitrix\Crm\Category\DealCategory::getStageList($category_id);
        return $stages;
    }

    private function getCategoryStagesWithIds($category_id){
        $stages = \Bitrix\Crm\Category\DealCategory::getStageInfos($category_id);
        return $stages;
    }

    //получение сделок специалиста по фильтру и указанным к выдаче полям
    private function getDealDataByFilter($arFilter,$arSelect){
        $deals = [];
        $db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
        while($ar_result = $db_list->GetNext()){
            $ar_result['HREF'] = '/crm/deal/details/'.$ar_result['ID'].'/'; //формируем ссылку для открытия во фрейме сделки
            $deals[] = $ar_result;
        }
        return $deals;
    }

    //получение истории сделок
    private function getDealHistoryByFilter($arFilter,$arSelect,$stagesMassive,$dealDateCreate,$curDealStage){
        $deal_history_list = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());

        $result = [];
        if($historyRes = $deal_history_list->GetNext()) {
            $counters = []; //переменная, которая будет содержать дату прихода и ухода со стадии


            foreach ($stagesMassive as $key => $value){

                //В отчете нужно отображать ВСЕ СТАДИИ, даже если на них не находилась сделка
                $counters[$value['STAGE_ID']]['PRIHOD'] = [];
                $counters[$value['STAGE_ID']]['UHOD'] = [];

                //подсчет уходов со стадий
                if($historyRes['EVENT_TEXT_1'] === $value['STAGE_NAME']){
                    if(preg_match('/NEW/',$value['STAGE_ID']) /*$value['STAGE_NAME'] === 'Актуализация'*/){
                        $counters[$value['STAGE_ID']]['PRIHOD'][] = $dealDateCreate;
                        $counters[$value['STAGE_ID']]['UHOD'][] = $historyRes['DATE_CREATE'];
                    }
                    else $counters[$value['STAGE_ID']]['UHOD'][] = $historyRes['DATE_CREATE'];
                }


                //подсчет приходов
                if($historyRes['EVENT_TEXT_2'] === $value['STAGE_NAME']){
                    $counters[$value['STAGE_ID']]['PRIHOD'][] = $historyRes['DATE_CREATE'];
                }

            }

            //подсчет уже дней
            foreach ($counters as $stageID => $massve){
                if((count($massve['PRIHOD']) === count($massve['UHOD'])) /*&& count($massve['PRIHOD']) > 0*/ ) {

                    //Считатаем кол-во дней нахждения на стадии
                    for($i = 0; $i <= count($massve['PRIHOD']); $i++ ){

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

                        $counters[$stageID]['CURRENT_STAGE'] = 0;
                    }
                }
                if(count($massve['PRIHOD']) >count($massve['UHOD']) ) {
                    //  echo '<br> Сейчас на стадии: '.$stage;
                    $counters[$stageID]['CURRENT_STAGE'] = 1;
                    //Считаем сколько уже дней находится на текущей стадии
                    $last_prihod = array_pop($massve['PRIHOD']);
                    $datetime1 = new DateTime($last_prihod);
                    $datetime2 = new DateTime(date('d.m.Y H:i:s'));
                    $interval = $datetime1->diff($datetime2);

                    $years = $interval->format('%y');
                    $months = $interval->format('%m');
                    $days = $interval->format('%d');
                    $hours = $interval->format('%h');
                    $minutes = $interval->format('%i');
                    $seconds = $interval->format('%s');

                    //здесь составляется
                    $counters[$stageID]['DAYS_ON_CUR_STAGE'] = '';
                    if($years != 0)  $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $years.' лет ';
                    if($months != 0)  $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $months.' мес ';
                    if($days != 0) $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $days.' дн ';
                    if($hours != 0) $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $hours.' ч ';
                    if($minutes != 0) $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $minutes.' мин ';
                    if($hours == 0 && $minutes == 0) $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $seconds.' сек ';
                  //  else $counters[$stageID]['DAYS_ON_CUR_STAGE'] .= $hours.' ч '.$minutes.' мин';

                }

            }

            foreach ($counters as $stage => $field){

                //вывод текущей стадии
                if(isset($field['DAYS_ON_CUR_STAGE'])) {

                    $anwer[] = array(
                        'NAME' => $stage,
                        'PERIOD' => $field['DAYS_ON_CUR_STAGE'],
                        'IS_CURRENT_STAGE' => 1,
                    );
                }
                //вывод прошедших стадий
                if($field['COUNTER']){
                    $date = '';
                    if($field['COUNTER']['PER_YEARS'] > 0) $date .= $field['COUNTER']['PER_YEARS'].' лет ';
                    if($field['COUNTER']['PER_MONTHS'] > 0) $date .= $field['COUNTER']['PER_MONTHS'].' мес ';
                    if($field['COUNTER']['PER_DAYS'] > 0) $date .= $field['COUNTER']['PER_DAYS'].' дн ';

                    if($field['COUNTER']['PER_HOURS'] > 0) $date .= $field['COUNTER']['PER_HOURS'].' ч ';
                    if($field['COUNTER']['PER_MINS'] > 0) $date .= $field['COUNTER']['PER_MINS'].' мин ';

                    if($field['COUNTER']['PER_HOURS'] == 0 && $field['COUNTER']['PER_MINS'] == 0 && $field['COUNTER']['PER_SECS'] > 0)
                        $date .= $field['COUNTER']['PER_SECS'].' сек';

                    if(empty($date)) $date = ' - ';

                    $anwer[] = [
                        'NAME' => $stage,
                        'PERIOD' => $date,
                        'IS_CURRENT_STAGE' => 0,
                    ];
                }

            }

          //  $this->logging([$counters,$anwer]);

            //$result[] = $historyRes;
          //  $result = ['answ' => $anwer, 'counters' => $counters];
            $result = $anwer;
        }
        //Это если нет истории смены стадий в сделке, считаем сколько уже находится на выбранной стадии при создании сделки
        else{
            foreach ($stagesMassive as $key => $value){
                if($value['STAGE_ID'] === $curDealStage){

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
                    if($years > 0)  $period .= $years.' лет ';
                    if($months > 0)  $period .= $months.' мес ';
                    if($days > 0) $period .= $days.' дн ';
                    if($hours > 0) $period .= $hours.' ч ';
                    if($minutes > 0) $period .= $minutes.' мин ';
                    if($hours == 0 && $minutes == 0) $period .= $seconds.' сек ';

                    // $period .= $hours.' ч '.$hours.' мин';

                    $anwer[] = [
                        'NAME' => $value['STAGE_ID'],
                        'PERIOD' => $period,
                        'IS_CURRENT_STAGE' => 1,
                    ];
                }
                else
                $anwer[] = [
                    'NAME' => $value['STAGE_ID'],
                    'PERIOD' => ' x ',
                    'IS_CURRENT_STAGE' => 0,
                ];
            }
            $result = $anwer;
        }

        return $result;
    }


    private function logging($data){
        $file = $_SERVER['DOCUMENT_ROOT'].'/custom_reports/stage_counters/logData.log';
        file_put_contents($file, print_r($data,true), FILE_APPEND | LOCK_EX);
    }

}