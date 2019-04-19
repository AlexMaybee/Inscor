
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
                //получаем стадии для селекта


                $massive[] = [
                    'ID' => $categoryId,
                    'NAME' => $this->getCategoryNameById($categoryId),
                    //'STAGES' => $stagesMassive,
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
        if($data['ASSIGNED_BY_ID']) $deals_filter['ASSIGNED_BY_ID'] = $data['ASSIGNED_BY_ID'];

        //Если выбрана стадия в фильтре, учитываем ее тоже
        if($data['STAGE_ID']) $deals_filter['STAGE_ID'] = $data['STAGE_ID'];

        //Если выбрана галка "Учитывать закрытые сделки", добавляем в фильтр
        if($data['ONLY_OPENED_DEALS'] == 'true') $deals_filter['CLOSED'] = 'N'; //ВОЗВРАЩАЕТ TRUE/FALSE в ВИДЕ СТРОКИ

        $deals_select = array('ID','TITLE','STAGE_ID','DATE_CREATE','CLOSEDATE','CLOSED','ASSIGNED_BY_ID');
        $dealMassive = $this->getDealDataByFilter($deals_filter,$deals_select);

        //теперь считаем кол-во дней и т.д. на каждой стадии
        foreach ($dealMassive as $index => $value){

            $historyFilter = Array('ENTITY_ID' => $value['ID'],'ENTIYY_TYPE_ID' => 2, 'EVENT_TYPE' => 1,'ENTITY_FIELD' => 'STAGE_ID'); // 'EVENT_TEXT_2' => 'Акты' - не ищет!
            $historySelect = Array('ID','EVENT_TEXT_1','EVENT_TEXT_2','DATE_CREATE');

            $assignedName = $this->getUserFullName($value['ASSIGNED_BY_ID']);
            $dealMassive[$index]['ASSIGNED_NAME'] = 'Ответственный - '.$assignedName['LAST_NAME'].' '.$assignedName['NAME'];

            //массив событий по переходам по воронке

            //alternative 14.04
            $res = $this->getDealHistoryByFilter_2($historyFilter,$historySelect);

            //!!!если истори нет, то отдаем массив стадий с  выбранной текущей и ее счетчиками
            if(!$res){
                $dealMassive[$index]['HISTORY'] = $this->getCurrentStageAndTimeOnIt($result['stages'],$value['DATE_CREATE'],$value['STAGE_ID']);
            }
            //!!!если история есть, нужно считать каждую стадию и выявить текущую
            else{
                //приходы/уходы со стадий
                $counters = $this->calculateEachStageTimeOn($res,$result['stages'],$value['STAGE_ID']);
                $dealMassive[$index]['HISTORY'] = $this->calculateEachStageTime($counters,$value['DATE_CREATE'],$value['STAGE_ID']);
            }

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
        $arFilter = [
           // 'ACTIVE' => 'Y', //убрать уволенных
        ];
        $dbUsers = $cUser->GetList($sort_by, $sort_ord, $arFilter);
        $users = [
            ['ID' => '0', 'NAME' => 'Не выбрано'],
        ];
        while ($arUser = $dbUsers->Fetch())
        {
            //убираем ненужные тех аккаунты, свой оставляем
            if(!in_array($arUser['ID'],$this->techAccounts))
                $users[] = [
                    'ID' => $arUser['ID'],
                    'NAME' => $arUser['LAST_NAME'].' '.$arUser['NAME'],
                ];
        }
        $result = $users;
        $this->sentAnswer($result);
    }

    //14.04 Стадии в селект по ID направления
    function getStagesByCategoryId($category_id){
        $result = false;

        $stages = $this->getCategoryStages($category_id);
        $stagesMassive = [
            [
                'ID' => 0,
                'NAME' => 'Не выбрано',
            ],
        ];
        foreach ($stages as $id => $name){
            $stagesMassive[] = [
                'ID' => $id,
                'NAME' => $name,
            ];
        }

        if($stagesMassive) $result = $stagesMassive;

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


    private function logging($data){
        $file = $_SERVER['DOCUMENT_ROOT'].'/custom_reports/stage_counters/logData.log';
        file_put_contents($file, print_r($data,true), FILE_APPEND | LOCK_EX);
    }


    //14.04 alternative
    //выявление текущей стадии и вычисление дней для нее (это если история пустая)
    private function getCurrentStageAndTimeOnIt($stages,$dealDateCreate,$curDealStage){

        //переформатируем массив стадий в нужный и считаем
        $counters = [];
        foreach ($stages as $key => $value) {

            //вывод всех стадий
            $counters[$key] = [
                'NAME' => $value['STAGE_NAME'],
                'STAGE_ID' => $value['STAGE_ID'],
                'PERIOD' => ' x ',
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
    private function calculateEachStageTimeOn($historyMassive,$stages,$curDealStage){
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
    private function calculateEachStageTime($counters,$dealDateCreate,$curDealStage){
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

    //Имя пользоватея по ID
    private function getUserFullName($user_id){
        $rsUser = CUser::GetByID($user_id);
        $arUser = $rsUser->Fetch();
        return $arUser;
    }

}