<?
CJSCore::Init(array("jquery"));
CModule::IncludeModule("CRM");
CModule::IncludeModule("tasks");

$APPLICATION->AddHeadScript('/local/lib/js/script.js');

/*Делаем поля лида обязательными ПРИ СОЗДАНИИ*/
AddEventHandler("crm", "OnBeforeCrmLeadAdd", "reqFieldsBeforCreateLead");

/*Делаем поля сделки обязательными на конкретных стадиях*/
AddEventHandler("crm", "OnBeforeCrmDealAdd", "reqFieldsBeforCreateDeal");
AddEventHandler("crm", "OnBeforeCrmDealUpdate", "changeDealStageControl");

/*Счетчики сделки при переходе на разных стадиях*/
AddEventHandler("crm", "OnBeforeCrmDealUpdate", "stageCounters"); //счетчики стадий по дням



function changeDealStageControl(&$arFields) {


    $arFilter = Array('ID' => $arFields['ID']);
    $arSelect = Array(
        'ID','STAGE_ID','TITLE',
        'UF_CRM_1532614252','UF_CRM_1532614341',/*'UF_CRM_1532613531'*/ 'UF_CRM_1535445683','UF_CRM_59DBEBA4374C2','ASSIGNED_BY_ID','UF_CRM_1532613305575',/*'UF_CRM_1532613353697',*/ 'UF_CRM_1534156053','UF_CRM_1534156848','UF_CRM_1533131953485', //Это стадия "Актуализация", поле "Целевой/НеЦелевой лид" + "К лиду"
        /*'UF_CRM_1532614475','UF_CRM_1532614542','UF_CRM_1532614584347',*/'UF_CRM_1533549547', //продолжение "Актуализация"
        'UF_CRM_1533040894', 'UF_CRM_1536058832', // Это стадия "Ожидание", поле даты + поле ID задачи (скрытое)
        'UF_CRM_1532615106','UF_CRM_1532615151'/*,'UF_CRM_1532615192'*/,'UF_CRM_1554372334','UF_CRM_1532615220',/*'UF_CRM_1532615266'*/'UF_CRM_1534154476',/*'UF_CRM_1532615453',*/'UF_CRM_1532615500', //Это поля "Тендер"
        'UF_CRM_1532616726','UF_CRM_1532616763'/*,'UF_CRM_1532616810'*/ // Это поля "Доп Вопросы"
    );

    //запрос уже сохраненной инфы в карточке
    $res = getDealDataByID($arFilter,$arSelect);

//    $file = $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/testLogDealNew.log';
//  file_put_contents($file, print_r($arFields,true), FILE_APPEND | LOCK_EX);
//  file_put_contents($file, print_r($res,true), FILE_APPEND | LOCK_EX);

    /*Стадия "Актуализация"*/

    if (
        (
            $arFields['STAGE_ID'] == 2 //'Ожидание'
            || $arFields['STAGE_ID'] == 'PREPARATION' //"Тендер"
            || $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
            || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
            || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
            || $arFields['STAGE_ID'] == 3 //"Слит"
        )
        &&
        (
            ((isset($arFields['UF_CRM_1535445683']) && $arFields['UF_CRM_1535445683'] == '') || ($res['UF_CRM_1535445683'] == '' && !isset($arFields['UF_CRM_1535445683'])))|| //"Наличие брокера по Мотор" NEW
            ((isset($arFields['UF_CRM_59DBEBA4374C2']) && $arFields['UF_CRM_59DBEBA4374C2'] == '') || ($res['UF_CRM_59DBEBA4374C2'] == '' && !isset($arFields['UF_CRM_59DBEBA4374C2'])))||
            ((isset($arFields['ASSIGNED_BY_ID']) && $arFields['ASSIGNED_BY_ID'] == '') || ($res['ASSIGNED_BY_ID'] == '' && !isset($arFields['ASSIGNED_BY_ID']))) ||
            ((isset($arFields['UF_CRM_1532613305575']) && $arFields['UF_CRM_1532613305575'] == '') || ($res['UF_CRM_1532613305575'] == '' && !isset($arFields['UF_CRM_1532613305575']))) ||
            ((isset($arFields['UF_CRM_1534156053']) && $arFields['UF_CRM_1534156053'] == '') || ($res['UF_CRM_1534156053'] == '' && !isset($arFields['UF_CRM_1534156053']))) || // Это Текущая СК по ОСГПО NEW
            ((isset($arFields['UF_CRM_1534156848']) && $arFields['UF_CRM_1534156848'] == '') || ($res['UF_CRM_1534156848'] == '' && !isset($arFields['UF_CRM_1534156848']))) || // Начало страхования ОСГПО NEW
            ((isset($arFields['UF_CRM_1533131953485']) && $arFields['UF_CRM_1533131953485'] == '') || ($res['UF_CRM_1533131953485'] == '' && !isset($arFields['UF_CRM_1533131953485'])))
           // ((isset($arFields['UF_CRM_1532614475']) && $arFields['UF_CRM_1532614475'] == '') || ($res['UF_CRM_1532614475'] == '' && !isset($arFields['UF_CRM_1532614475']))) ||
          // || ((isset($arFields['UF_CRM_1532614542']) && $arFields['UF_CRM_1532614542'] == '') || ($res['UF_CRM_1532614542'] == '' && !isset($arFields['UF_CRM_1532614542'])))
        )
    )
    {
        $arFields['STAGE_ID'] == 'NEW'; //"Актуализация"
        $arFields['RESULT_MESSAGE'] = 'На стадии "Актуализация" необходимо заполнить обязательные поля: Наличие брокера по МОТОР, Брокеры, Отрасль, Ответственный, ТС по МТСБУ, Текущая СК по ОСГПО, Начало страхования ОСГПО, Количество ТС подтвержденное по КАСКО, Целевой лид по мотор или не целевой, К лиду'; //, Руководство, ЛПР по мотор
        return false;
    }

    /*целевой/не целевой лид + К лиду*/
    if (
        (
            $arFields['STAGE_ID'] == 2 //'Ожидание'
            || $arFields['STAGE_ID'] == 'PREPARATION' //"Тендер"
            || $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
            || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
            || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
            || $arFields['STAGE_ID'] == 3 //"Слит"
        )
        &&
        (
            (isset($arFields['UF_CRM_1532614252']) && $arFields['UF_CRM_1532614252'] == 0) || ($res['UF_CRM_1532614252'] == 0 && !isset($arFields['UF_CRM_1532614252']))
        )
    )
    {
        if((isset($arFields['UF_CRM_1532614341']) && $arFields['UF_CRM_1532614341'] == '') || ($res['UF_CRM_1532614341'] == '' && !isset($arFields['UF_CRM_1532614341'])))
        {
            $arFields['STAGE_ID'] == 'NEW';
            $arFields['RESULT_MESSAGE'] = 'При невыбранном "Не целевом лиде" поле "К лиду" является обязательным';
            return false;
        }
      /*  else {
            return $arFields;
        }*/
    }
    /*Стадия "Актуализация"*/


    /*Этап ограничения выставления задних чисел дат в 7-ти полях */

    //дата "Дата ожидания"
    if($arFields['UF_CRM_1533040894'] != '' && (strtotime($arFields['UF_CRM_1533040894']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата ожидания не может быть прошедшей!';
        return false;
    }

    //дата "Дата отправления КП"
    if($arFields['UF_CRM_1532615151'] != '' && (strtotime($arFields['UF_CRM_1532615151']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата отправления КП не может быть прошедшей!';
        return false;
    }

    //дата "Дата отправления КП Доп вопросы"
    if($arFields['UF_CRM_1532616763'] != '' && (strtotime($arFields['UF_CRM_1532616763']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата отправления КП Доп вопросы не может быть прошедшей!';
        return false;
    }

    /*Этап ограничения выставления задних чисел дат в 7-ти полях */


    /*Стадия "Ожидание"*/

    /*Обяз. заполнение поля причины*/
    if (
        (
            $arFields['STAGE_ID'] == 2 //'Ожидание'
        )
        &&
        (
            (isset($arFields['UF_CRM_1533040894']) && $arFields['UF_CRM_1533040894'] == '') || ($res['UF_CRM_1533040894'] == '' && !isset($arFields['UF_CRM_1533040894']))
        )
    )
    {
        $arFields['STAGE_ID'] == 'NEW'; //"Ожидание"
        $arFields['RESULT_MESSAGE'] = 'До стадии "Ожидание" необходимо заполнить обязательное поле: Дата ожидания';
        return false;
    }
    /*Обяз. заполнение поля причины*/

    /*Создание задачи, если поле с датой ожидания != ''*/

   /*
    * 26.02.2019 Убрал создание задачи при переходе на стадию "Ожидание" по просьбе клиента
    *  if (
        (
            $arFields['STAGE_ID'] == 2 //'Ожидание'
        )
        &&
        (
            (isset($arFields['UF_CRM_1533040894']) && $arFields['UF_CRM_1533040894'] != '') || ($res['UF_CRM_1533040894'] != '' && !isset($arFields['UF_CRM_1533040894']))
        )
    )
    {
        $deadline = '';
        if(isset($arFields['UF_CRM_1533040894']) && $arFields['UF_CRM_1533040894'] != '') $deadline = $arFields['UF_CRM_1533040894'];
        if($res['UF_CRM_1533040894'] != '' && !isset($arFields['UF_CRM_1533040894'])) $deadline = $res['UF_CRM_1533040894'];

        //Если задачи не было создано раннее, то создаем новую - проверка по заполнению польз. поля в сделке
        if($res['UF_CRM_1536058832'] == '' && (!isset($arFields['UF_CRM_1536058832']) || $arFields['UF_CRM_1536058832'] == '')){

            //Создание задачи
            $taskFields = Array(
                "TITLE" => 'Необходимо связаться с клиентом по сделке "'.$res['TITLE'].'"',
                "DESCRIPTION" => 'Не забыть связаться с клиентом до '.$deadline.' включительно <a href="/crm/deal/details/'.$arFields['ID'].'/">по сделке № '.$arFields['ID'].'</a>', // Выполнил в виде ссылки
                "RESPONSIBLE_ID" => $res['ASSIGNED_BY_ID'],
                "CREATED_BY" => $res['ASSIGNED_BY_ID'],
                "DEADLINE" => $deadline,
                "PRIORITY" => 2, // 2 соответствует высокому приоритету
                "UF_CRM_TASK" => Array('D_'.$arFields['ID']) // привязка задачи к сделке по id
            );
            $taskID = createTask($taskFields); // ID новой задачи
                if($taskID > 0){
                    $noteFields = Array(
                        "MESSAGE_TYPE"  => "S",
                        "TO_USER_ID"    => intval($res["ASSIGNED_BY_ID"]), //Ответственный за сделку
                        "MESSAGE"       => 'Создана <a href="/company/personal/user/'.$res["ASSIGNED_BY_ID"].'/tasks/task/view/'.$taskID.'/">задача</a>, согласно которой необходимо связаться с клиентом до указанного срока по <a href="/crm/deal/details/'.$arFields['ID'].'/">сделке №'.$arFields['ID'].'</a>',
                        "NOTIFY_TYPE" => 4, // 1 - принять/отказаться; 2,3,5+ - нерабочие, 4 - обычное уведомление
                        "NOTIFY_TITLE" => 'Связаться с клиентом по сделке "'.$res['TITLE'].'" до '.$deadline,
                    );
                    $noteID = createNotification($noteFields);

                    $remindFields = Array(
                        "TASK_ID" => $taskID,
                        "USER_ID" => $res['ASSIGNED_BY_ID'],
                        "REMIND_DATE" => $deadline." 10:00:00",
                        "TYPE" => 'A',
                        "TRANSPORT" => 'J',
                        'RECEPIENT_TYPE' => 'R'
                    );
                    //Создание напоминания в задаче
                    $remindID = createRemaindForTask($remindFields);

                    //сохранение ID задачи в скрытом поле сделки UF_CRM_1536058832
                    $arFields['UF_CRM_1536058832'] = $taskID;
                }
            }

            //Создание задачи
        }


        //Если задача уже была создана, вносим
        if($res['STAGE_ID'] == 2 && !isset($arFields['STAGE_ID'])){

            if($res['UF_CRM_1536058832'] != '' && (isset($arFields['UF_CRM_1533040894']) && ($res['UF_CRM_1533040894'] != $arFields['UF_CRM_1533040894']))) {

                //Проверяем, что задача не закрыта
                $taskData = getTaskData($res['UF_CRM_1536058832']);
                if ($taskData['STATUS'] != 5){

                    $updTaskFields = Array(
                        "TITLE" => 'ОБНОВЛЕНО! Необходимо связаться с клиентом по сделке "'.$res['TITLE'].'"',
                        "DESCRIPTION" => 'Перенос! Не забыть связаться с клиентом до '.$arFields['UF_CRM_1533040894'].' включительно <a href="/crm/deal/details/'.$arFields['ID'].'/">по сделке № '.$arFields['ID'].'</a>', // Выполнил в виде ссылки
                        "DEADLINE" => $arFields['UF_CRM_1533040894'],
                       // "UF_CRM_TASK" => 'D_'.$arFields['ID'], // слетает при обновлении задачи
                    );

                    $updTask = updateTask($res['UF_CRM_1536058832'],$updTaskFields); //ID задачи + поля

                    $remindFields = Array(
                        "TASK_ID" => $res['UF_CRM_1536058832'],
                        "USER_ID" => $res['ASSIGNED_BY_ID'],
                        "REMIND_DATE" => $arFields['UF_CRM_1533040894']." 10:00:00",
                        "TYPE" => 'A',
                        "TRANSPORT" => 'J',
                        'RECEPIENT_TYPE' => 'R'
                    );
                    //Создание напоминания в задаче
                    $remindID = createRemaindForTask($remindFields);

                    //Создание уведомления
                    $noteFields = Array(
                        "MESSAGE_TYPE" => "S",
                        "TO_USER_ID" => intval($res["ASSIGNED_BY_ID"]), //Ответственный за сделку
                        "MESSAGE" => 'Обновлена <a href="/company/personal/user/' . $res["ASSIGNED_BY_ID"] . '/tasks/task/view/' . $res['UF_CRM_1536058832'] . '/">задача</a>, согласно которой необходимо связаться с клиентом до указанного срока по <a href="/crm/deal/details/' . $arFields['ID'] . '/">сделке №' . $arFields['ID'] . '</a>',
                        "NOTIFY_TYPE" => 4, // 1 - принять/отказаться; 2,3,5+ - нерабочие, 4 - обычное уведомление
                        "NOTIFY_TITLE" => 'Связаться с клиентом по сделке "' . $res['TITLE'] . '" до ' . $arFields['UF_CRM_1533040894'],
                    );
                    $noteID = createNotification($noteFields);


                }
            }
        }*/


   /*Создание задачи, если поле с датой ожидания != ''*/
    /*Стадия "Ожидание"*/



    /*Проба пера только для "СЛИТ"*/
    if (
        $arFields['STAGE_ID'] == 3 //"Слит"
        &&
        (
            ((isset($arFields['UF_CRM_1532615106']) && $arFields['UF_CRM_1532615106'] == '') || ($res['UF_CRM_1532615106'] == '' && !isset($arFields['UF_CRM_1532615106'])))||
            ((isset($arFields['UF_CRM_1532615151']) && $arFields['UF_CRM_1532615151'] == '') || ($res['UF_CRM_1532615151'] == '' && !isset($arFields['UF_CRM_1532615151'])))||
       // поле файла, заменено на множ.     ((isset($arFields['UF_CRM_1532615192']) && $arFields['UF_CRM_1532615192'] == '') || ($res['UF_CRM_1532615192'] == '' && !isset($arFields['UF_CRM_1532615192'])))||
            ((isset($arFields['UF_CRM_1554372334']) && $arFields['UF_CRM_1554372334'] == '') || ($res['UF_CRM_1554372334'] == '' && !isset($arFields['UF_CRM_1554372334'])))||
            ((isset($arFields['UF_CRM_1532615220']) && $arFields['UF_CRM_1532615220'] == '') || ($res['UF_CRM_1532615220'] == '' && !isset($arFields['UF_CRM_1532615220'])))||
            ((isset($arFields['UF_CRM_1534154476']) && $arFields['UF_CRM_1534154476'] == '') || ($res['UF_CRM_1534154476'] == '' && !isset($arFields['UF_CRM_1534154476']))) //Текущая страховая компания по КАСКО NEW!!!
        )
        &&
        ((isset($arFields['UF_CRM_1532614252']) && $arFields['UF_CRM_1532614252'] == 0) || ($res['UF_CRM_1532614252'] == 0 && !isset($arFields['UF_CRM_1532614252']))) //Целевой лид по мотор или не целевой
        &&
        ((isset($arFields['UF_CRM_1532614341']) && $arFields['UF_CRM_1532614341'] != '') || ($res['UF_CRM_1532614341'] != '' && !isset($arFields['UF_CRM_1532614341'])))
    )
    {
        return true;
    }
    /*Проба пера только для "СЛИТ"*/

    /*Стадия "Тендер"*/
    if (
            (
                $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
                || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
                || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
                || $arFields['STAGE_ID'] == 3 //"Слит"
            )
            &&
            (
                ((isset($arFields['UF_CRM_1532615106']) && $arFields['UF_CRM_1532615106'] == '') || ($res['UF_CRM_1532615106'] == '' && !isset($arFields['UF_CRM_1532615106'])))||
                ((isset($arFields['UF_CRM_1532615151']) && $arFields['UF_CRM_1532615151'] == '') || ($res['UF_CRM_1532615151'] == '' && !isset($arFields['UF_CRM_1532615151'])))||
                // поле файла, заменено на множ.  ID = UF_CRM_1554372334   ((isset($arFields['UF_CRM_1532615192']) && $arFields['UF_CRM_1532615192'] == '') || ($res['UF_CRM_1532615192'] == '' && !isset($arFields['UF_CRM_1532615192'])))||
                ((isset($arFields['UF_CRM_1554372334']) && $arFields['UF_CRM_1554372334'] == '') || ($res['UF_CRM_1554372334'] == '' && !isset($arFields['UF_CRM_1554372334'])))||
                ((isset($arFields['UF_CRM_1532615220']) && $arFields['UF_CRM_1532615220'] == '') || ($res['UF_CRM_1532615220'] == '' && !isset($arFields['UF_CRM_1532615220'])))||
                ((isset($arFields['UF_CRM_1534154476']) && $arFields['UF_CRM_1534154476'] == '') || ($res['UF_CRM_1534154476'] == '' && !isset($arFields['UF_CRM_1534154476']))) //Текущая страховая компания по КАСКО NEW!!!
            )
            /*&&
            ((isset($arFields['UF_CRM_1532614252']) && $arFields['UF_CRM_1532614252'] == 1) || ($res['UF_CRM_1532614252'] == 1 && !isset($arFields['UF_CRM_1532614252']))) //Целевой лид по мотор или не целевой*/
        )
        {
            $arFields['STAGE_ID'] == 'PREPARATION'; //"Тендер"
            $arFields['RESULT_MESSAGE'] = 'До стадии "Тендер" необходимо заполнить обязательные поля: Описание разговора с ЛПР, Дата отправления КП, Вложение тендерной документации, Наименьшая премия по тендеру, Текущая страховая компания по КАСКО';
            return false;
        }
    
    /*Стадия "Тендер"*/

    /*Стадия "Доп Вопросы"*/
    if (
            (
                $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
            )
            &&
            (
                ((isset($arFields['UF_CRM_1532616726']) && $arFields['UF_CRM_1532616726'] == '') || ($res['UF_CRM_1532616726'] == '' && !isset($arFields['UF_CRM_1532616726'])))||
                ((isset($arFields['UF_CRM_1532616763']) && $arFields['UF_CRM_1532616763'] == '') || ($res['UF_CRM_1532616763'] == '' && !isset($arFields['UF_CRM_1532616763'])))
            )
    )
    {
        $arFields['STAGE_ID'] == 'PREPARATION';//"Тендер"
        $arFields['RESULT_MESSAGE'] = 'Чтобы перейти на стадию "ДОПОЛНИТЕЛЬНЫЕ ВОПРОСЫ" обязательные поля к заполнению: Комментарий при какой причине выбрана данная стадия, Дата отправления КП';
        return false;
    }
    /*Стадия "Доп Вопросы"*/

    /*Запрет перехода с "Доп Вопросы" на "Тендер" - отменено клиентом*/
    /*if($arFields['STAGE_ID'] == 'PREPARATION' && $res['STAGE_ID'] == 'PREPAYMENT_INVOICE')
    {
        $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE';//"Доп. Вопросы"
        $arFields['RESULT_MESSAGE'] = 'Нельзя перейти обратно со стадии "Доп. вопросы" на стадию "Тендер"';
        return false;
    }*/
    /*Запрет перехода с "Доп Вопросы" на "Тендер" - отменено клиентом*/

        else {
            return $arFields;
        }

}
/*Делаем поля сделки обязательными на конкретных стадиях*/


function getDealDataByID($arFilter,$arSelect){

    $db_list = CCrmDeal::GetListEx(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array()); //получение пользовательских полей сделки по ID
    if($ar_result = $db_list->GetNext()) return $ar_result;
}


function reqFieldsBeforCreateDeal(&$arFields){

    /*Обязательные поля, которые слетели: Город, Отрасль, Источники new*/
    if(
        $arFields['UF_CRM_59EE4DC739368'] == 4810
        || $arFields['UF_CRM_59EE4DC739368'] == ''
        || $arFields['UF_CRM_5B8E54EF007FC'] == 5192 // "Отрасль"
        || $arFields['UF_CRM_5B8E54EF007FC'] == '' // "Отрасль"
        || $arFields['UF_CRM_5B75CA95E1791'] == 5
        || $arFields['UF_CRM_5B75CA95E1791'] == ''
    ){
        $arFields['RESULT_MESSAGE'] = 'Поля обязательны к заполнению: Город, Отрасль, Источники';
        return false;
    }


    /*Этап ограничения выставления задних чисел дат в 5-ти полях */

    //дата "Дата ожидания"
    if($arFields['UF_CRM_1533040894'] != '' && (strtotime($arFields['UF_CRM_1533040894']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата ожидания не может быть прошедшей!';
        return false;
    }

    //дата "Дата отправления КП"
    if($arFields['UF_CRM_1532615151'] != '' && (strtotime($arFields['UF_CRM_1532615151']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата отправления КП не может быть прошедшей!';
        return false;
    }

    //дата "Дата отправления КП Доп вопросы"
    if($arFields['UF_CRM_1532616763'] != '' && (strtotime($arFields['UF_CRM_1532616763']) <= strtotime('yesterday'))) {
        $arFields['RESULT_MESSAGE'] = 'Дата отправления КП Доп вопросы не может быть прошедшей!';
        return false;
    }

    /*Этап ограничения выставления задних чисел дат в 5-ти полях */

    else {

        if($arFields['COMPANY_ID'] > 0){
            $company = CCrmCompany::GetByID($arFields['COMPANY_ID'], $bCheckPerms = true);
            if($arFields['UF_CRM_1533826055']){ // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
                $arFields['TITLE'] = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3)).' - '.$company['TITLE'].' - М - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
            }
            else{
                $arFields['TITLE'] = date('m - Y - ').$company['TITLE'].' - М';
            }

        }
        else{
            if($arFields['UF_CRM_1533826055']){ // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
                $arFields['TITLE'] = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3)).' - М - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
            }
            else {
                $arFields['TITLE'] = date('m - Y - ') . 'Мотор';
            }
        }
        //Доработка № 1 - Присваиваем при СОЗДАНИИ СДЕЛКИ название по шаблону мм-гггг-Мотор

        //Исправление косяка отн. источников NEW Лидов при конвертации из лида в сделку - из кастомного поля "Источник информации о компании" кидаем в SOURCE_ID
        $arFields['TYPE_ID'] = $arFields['UF_CRM_5B75CA95E1791'];
        switch ($arFields['UF_CRM_5B75CA95E1791']){
            case 5:
                $arFields['TYPE_ID'] = 'SALE'; //Значение не задано
                break;
            case 'FACE_TRACKER':
                $arFields['TYPE_ID'] = 'COMPLEX'; //Face-трекер
                break;
            case 'CALL':
                $arFields['TYPE_ID'] = 'GOODS'; //База
                break;
            case 'EMAIL':
                $arFields['TYPE_ID'] = 'SERVICES'; //Выставка/Конференция
                break;
            case 'WEB':
                $arFields['TYPE_ID'] = 1; //Лизинг
                break;
            case 'ADVERTISING':
                $arFields['TYPE_ID'] = 2; //Брокеры
                break;
            case 'PARTNER':
                $arFields['TYPE_ID'] = 3; //Регресс
                break;
            case 'RECOMMENDATION':
                $arFields['TYPE_ID'] = 4; //Кросс
                break;
            case 'TRADE_SHOW':
                $arFields['TYPE_ID'] = 5; //Лизингополучатель
                break;
            case 'WEBFORM':
                $arFields['TYPE_ID'] = 6; //Банк
                break;
            case 'CALLBACK':
                $arFields['TYPE_ID'] = 7; //ДМС
                break;
            case 'OTHER':
                $arFields['TYPE_ID'] = 8; //Запрос
                break;
            case 1:
                $arFields['TYPE_ID'] = 9; //входящие и исходящие звонки
                break;
            case 2:
                $arFields['TYPE_ID'] = 10; //CRM-формы
                break;
            case 3:
                $arFields['TYPE_ID'] = 11; //e-mail
                break;
            case 6:
            $arFields['TYPE_ID'] = 13; //Физическое лицо
            break;
            default:
                $arFields['TYPE_ID'] = 'SALE'; //Значение не задано
                break;
        }


        return $arFields;
    }

}

//Обязательные поля при создании лида
function reqFieldsBeforCreateLead(&$arFields){

    if (
          /*   $arFields['NAME'] == '' // Имя
             || $arFields['LAST_NAME'] == '' // Фамилия
             || $arFields['POST'] == '' // Должность
             ||*/ $arFields['COMPANY_TITLE'] == '' // Название компании
             || $arFields['FM']['PHONE']['n0']['VALUE'] == '' // Телефон
        || $arFields['UF_CRM_1508326827'] == 4804
        || $arFields['UF_CRM_1508326827'] == ''
        || $arFields['UF_CRM_1533213299'] == 5
        || $arFields['UF_CRM_1533213299'] == ''
        || $arFields['UF_CRM_1533628062'] == 4805
        || $arFields['UF_CRM_1533628062'] == ''

     )
     {
         $arFields['RESULT_MESSAGE'] = 'При создании лида обязательно необходимо заполнить поля: Название компании, Телефон, Город, источник информации о компании, Отрасль';//Имя, Фамилия, Должность,
         return false;
     }
     $arFields['SOURCE_ID'] = $arFields['UF_CRM_1533213299']; // Доработка отн. источников - из кастомного поля источника кидаем в SOURCE_ID
}


//Счетчики - кол-во дней на стадии
function stageCounters(&$arFields){


   /* $file = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/StageCoutersLog.log';
    file_put_contents($file, print_r($arFields,true), FILE_APPEND | LOCK_EX);*/


    $arFilter = Array('ID' => $arFields['ID']);
    $arSelect = Array(
        'ID','STAGE_ID','BEGINDATE',
        'UF_CRM_1532617063', //счетчик "Актуализация" в днях
        'UF_CRM_1533559778', //счетчик "Актуализация" в Unix
        'UF_CRM_1532617099', //счетчик "Ожидание" в днях
        'UF_CRM_1533559811', //счетчик "Ожидание" в Unix
        'UF_CRM_1532617079', //счетчик "Тендер" в Днях
        'UF_CRM_1533559839', //счетчик "Тендер" в Unix
        'UF_CRM_1533557380681', //счетчик "Доп. Вопросы" в Днях
        'UF_CRM_1533559867', //счетчик "Доп. Вопросы" в Unix
    );

    //запрос уже сохраненной инфы в карточке
    $beforUpdateDealData = getDealDataByID($arFilter,$arSelect);

   // file_put_contents($file, print_r($beforUpdateDealData,true), FILE_APPEND | LOCK_EX);

    //счетчик для стадии "Актуализация"
    if($beforUpdateDealData['STAGE_ID'] == 'NEW' && (
            $arFields['STAGE_ID'] == 2 //'Ожидание'
            || $arFields['STAGE_ID'] == 'PREPARATION' //"Тендер"
            || $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
            || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
            || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
            || $arFields['STAGE_ID'] == 3 //"Слит"
        )
    ){
        /*$arFields['UF_CRM_1532617063'] = round((strtotime('now') - strtotime($beforUpdateDealData['BEGINDATE']))/(60*60*24));
        $arFields['UF_CRM_1533559778'] = strtotime('now');*/

        $datetime1 = new DateTime("now");
        $datetime2 = new DateTime($beforUpdateDealData['BEGINDATE']);
        $interval = $datetime2->diff($datetime1);

        $arFields['UF_CRM_1532617063'] = $interval->format('%R%D');
        $arFields['UF_CRM_1533559778'] = strtotime('now');

    }

    //счетчик "Ожидание"
    if($beforUpdateDealData['STAGE_ID'] == 2 && (
            $arFields['STAGE_ID'] == 'PREPARATION' //"Тендер"
            || $arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
            || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
            || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
            || $arFields['STAGE_ID'] == 3 //"Слит"
        )
    ){
       /* $arFields['UF_CRM_1532617099'] = round((strtotime('now') - $beforUpdateDealData['UF_CRM_1533559778'])/(60*60*24));
        $arFields['UF_CRM_1533559811'] = strtotime('now');*/

        $datetime1 = new DateTime("now");
        $datetime2 = new DateTime(date('d.m.Y H:i:s',$beforUpdateDealData['UF_CRM_1533559778']));
        $interval = $datetime2->diff($datetime1);

        $arFields['UF_CRM_1532617099'] = $interval->format('%R%D');
        $arFields['UF_CRM_1533559811'] = strtotime('now');
    }

    //счетчик "Тендер"
        //если перешли со стадии "Актуализация"
    if($beforUpdateDealData['STAGE_ID'] == 'PREPARATION' && (
        ($arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
        || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
        || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
        || $arFields['STAGE_ID'] == 3 //"Слит"
        )
        && ($beforUpdateDealData['UF_CRM_1533559778'] != '' && $beforUpdateDealData['UF_CRM_1533559811'] == '')//
        )
    ){
       /* $arFields['UF_CRM_1532617079'] = round((strtotime('now') - $beforUpdateDealData['UF_CRM_1533559778'])/(60*60*24));
        $arFields['UF_CRM_1533559839'] = strtotime('now');*/

        $datetime1 = new DateTime("now");
        $datetime2 = new DateTime(date('d.m.Y H:i:s',$beforUpdateDealData['UF_CRM_1533559778']));
        $interval = $datetime2->diff($datetime1);

        $arFields['UF_CRM_1532617079'] = $interval->format('%R%D');
        $arFields['UF_CRM_1533559839'] = strtotime('now');
    }

        //если перешли со стадии "Ожидание"
    if($beforUpdateDealData['STAGE_ID'] == 'PREPARATION' && (
            ($arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' //"Доп. Вопросы"
                || $arFields['STAGE_ID'] == 'WON' //"Застрахован"
                || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
                || $arFields['STAGE_ID'] == 3 //"Слит"
            )
            && ($beforUpdateDealData['UF_CRM_1533559778'] != '' && $beforUpdateDealData['UF_CRM_1533559811'] != '')
        )
    )
    {
       /* $arFields['UF_CRM_1532617079'] = round((strtotime('now') - $beforUpdateDealData['UF_CRM_1533559811'])/(60*60*24));
        $arFields['UF_CRM_1533559839'] = strtotime('now');*/

        $datetime1 = new DateTime("now");
        $datetime2 = new DateTime(date('d.m.Y H:i:s',$beforUpdateDealData['UF_CRM_1533559811']));
        $interval = $datetime2->diff($datetime1);

        $arFields['UF_CRM_1532617079'] = $interval->format('%R%D');
        $arFields['UF_CRM_1533559839'] = strtotime('now');
    }


    //счетчик "Доп. Вопросы" только со стадии "Тендер"
    if($beforUpdateDealData['STAGE_ID'] == 'PREPAYMENT_INVOICE' && (
            $arFields['STAGE_ID'] == 'WON' //"Застрахован"
            || $arFields['STAGE_ID'] == 'LOSE' //"Проигран тендер"
            || $arFields['STAGE_ID'] == 3 //"Слит"
        )
    ){
       /* $arFields['UF_CRM_1533557380681'] = round((strtotime('now') - $beforUpdateDealData['UF_CRM_1533559839'])/(60*60*24));
        $arFields['UF_CRM_1533559867'] = strtotime('now');*/

        $datetime1 = new DateTime("now");
        $datetime2 = new DateTime(date('d.m.Y H:i:s',$beforUpdateDealData['UF_CRM_1533559839']));
        $interval = $datetime2->diff($datetime1);

        $arFields['UF_CRM_1533557380681'] = $interval->format('%R%D');
        $arFields['UF_CRM_1533559867'] = strtotime('now');
    }

}


function createTask($taskFields){
    $obTask = new CTasks;
    return $taskID = $obTask->Add($taskFields);
}

function updateTask($ID,$taskFields){
    $obTask = new CTasks;
    return $success = $obTask->Update($ID,$taskFields);
}

function delTask($ID){
    return CTasks::Delete($ID);
    //return $success = $obTask->Update($taskFields);
}

function createNotification($noteFields){
    return $mess = CIMMessenger::Add($noteFields);
}

function createRemaindForTask($fields){
    $obTaskReminders = new CTaskReminders;
    return $ID = $obTaskReminders->Add($fields);
}

function deleteTaskRemainds($taskId){
    return $delRem = CTaskReminders::DeleteByTaskID($taskId);
}

function getTaskData($ID){
    $rsTask = CTasks::GetByID($ID);
    return $arTask = $rsTask->GetNext();
}

/**TESTS with Tasks**/
//AddEventHandler("tasks", "OnTaskUpdate", "checkTaskIfCompleted");
function checkTaskIfCompleted($ID, &$arFields, &$arTaskCopy)
{
    $file = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/testTasksNew.log';
    file_put_contents($file, print_r($arFields,true), FILE_APPEND | LOCK_EX);
}



/*Подключение js-файла сос счетчиками*/

$arJsConfig = array(
    'fillDealsCounters' => array(
        'js' => '/local/lib/js/itlogic/counters.js', //<ba0e993a-3c26-4efb-bed0-03f95aaca5cf>\home\bitrix\www\local\lib\itlogic\counters.js
    )
);

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}

//Вызов библиотеки
CUtil::InitJSCore(array('fillDealsCounters'));

/*Подключение js-файла сос счетчиками*/