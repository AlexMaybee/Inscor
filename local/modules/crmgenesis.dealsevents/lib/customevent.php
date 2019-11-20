<?php

namespace Crmgenesis\Dealsevents;

//Подключение js D7
use \Bitrix\Main\Page\Asset,
    \Crmgenesis\Dealsevents\Bitrixfunctions;

class Customevent{

    const CATEGORY_ID_MOTOR = 0;
    const CATEGORY_ID_INDIVID = 1;

    public function addCustomScripts(){
        //Штатная библиотека
        if(!\CJSCore::Init(["jquery2"]))
            \CJSCore::Init(["jquery2"]);

        Asset::getInstance()->addJs('/bitrix/js/'.Main::MODULE_ID.'/script.js',true);
    }

    public function reqFieldsBeforCreateLead(&$arFields){
        $errors = [];

//        if(empty(trim($arFields['COMPANY_TITLE']))) $errors[] = 'Не заполнено название компании!';
//        if($arFields['FM']['PHONE']['n0']['VALUE']) $errors[] = 'Не указан номер телефона!';
        if($arFields['UF_CRM_1508326827'] == 4804
            || $arFields['UF_CRM_1508326827'] == '') $errors[] = 'Не выбран город!';
        if($arFields['UF_CRM_1533213299'] == 5
            || $arFields['UF_CRM_1533213299'] == '') $errors[] = 'Не выбран источник информации!';
        if($arFields['UF_CRM_1533628062'] == 4805
            || $arFields['UF_CRM_1533628062'] == '') $errors[] = 'Не выбрана отрасль!';

        $arFields['SOURCE_ID'] = $arFields['UF_CRM_1533213299']; // Доработка отн. источников - из кастомного поля источника кидаем в SOURCE_ID

//        Bitrixfunctions::logData([$errors,$arFields]);

        if($errors){
            $err = '';
            foreach ($errors as $error)
                $err .= $error."<br>";
            $arFields['RESULT_MESSAGE'] = $err;
            return false;
        }
        else return true;
    }


    public function reqFieldsBeforCreateDeal(&$arFields){
        $errors = [];


//        Bitrixfunctions::logData($arFields);


        //Обязательные поля, которые слетели: Город, Отрасль, Источники new
       if($arFields['UF_CRM_59EE4DC739368'] == 4810
           || $arFields['UF_CRM_59EE4DC739368'] == '')
           $errors[] = 'Поле Город обязательно к заполнению!';

       if($arFields['CATEGORY_ID'] == 0 &&
           ($arFields['UF_CRM_5B8E54EF007FC'] == 5192
               || $arFields['UF_CRM_5B8E54EF007FC'] == '')
          )
           $errors[] = 'Поле Отрасль обязательно к заполнению!';

       if($arFields['UF_CRM_5B75CA95E1791'] == 5
           || $arFields['UF_CRM_5B75CA95E1791'] == '')
           $errors[] = 'Поле Источники обязательно к заполнению!';


       //Проверка полей с датами, чтобы не были прошедшими
        $datesErrors = self::checkIfNeededDealDatesNotPast($arFields);
        if($datesErrors){
            foreach ($datesErrors as $error) $errors[] = $error;
        }

        //Присвоение нового названия сделке в зависимости от направдения и пары моментов
        $customNewDealTitle = self::changeDealTitleByPatternBefore($arFields);
        if($customNewDealTitle) $arFields['TITLE'] = $customNewDealTitle;

        //Конвертация сделки из лида, перепривязка источников
        $arFields['TYPE_ID'] = self::correctSourceOnLeadToDelaConvertation($arFields['UF_CRM_5B75CA95E1791']);


        if($errors){
            $err = '';
            foreach ($errors as $error){
//                $err .= $error."\n";
                $err .= $error.'<br>';
            }
            $arFields['RESULT_MESSAGE'] = $err;
            return false;
        }
        else{

            return true;

            //Здесь продолжение всего или нет
        }

    }


    //создание дочерних сделок, если выигрыш или проигрыш
    public function checkFieldsBeforeDealUpdate(&$arFields){
        $errors = [];

        if($arFields['ID'] > 0){
            $dealsRes = Bitrixfunctions::getDealsByFilter(['ID' => $arFields['ID']],['ID','STAGE_ID','TITLE','ASSIGNED_BY_ID','UF_*']);

            if($dealsRes){
                if($dealsRes[0]['CATEGORY_ID'] == self::CATEGORY_ID_MOTOR){
                    $errorRes = self::checkMotorCategory($arFields,$dealsRes);
                    if($errorRes)
                        foreach ($errorRes as $error) $errors[] = $error;
                }
            }
        }


        //Проверка полей с датами, чтобы не были прошедшими
        $datesErrors = self::checkIfNeededDealDatesNotPast($arFields);
        if($datesErrors){
            foreach ($datesErrors as $error) $errors[] = $error;
        }

        if($errors){
            $err = '';
            foreach ($errors as $error){
                $err .= $error.'<br>';
            }
            $arFields['RESULT_MESSAGE'] = $err;
            return false;
        }
        else{

            return true;

            //Здесь продолжение всего или нет
        }

//        Bitrixfunctions::logData([$arFields,$dealsRes]);
    }



    //Фукнции-сателлиты


    //1. Этап ограничения выставления задних чисел дат в 3-ти полях (было в 5-ти)
    private function checkIfNeededDealDatesNotPast($arFields){
        $errors = [];

        //дата "Дата ожидания"
        if(!self::checkDateFieldIfPast($arFields['UF_CRM_1533040894']))
            $errors[] = 'Дата ожидания не может быть прошедшей!';

        //дата "Дата отправления КП"
        if(!self::checkDateFieldIfPast($arFields['UF_CRM_1532615151']))
            $errors[] = 'Дата отправления КП не может быть прошедшей!';

        //дата "Дата отправления КП Доп вопросы"
        if(!self::checkDateFieldIfPast($arFields['UF_CRM_1532616763']))
            $errors[] = 'Дата отправления КП Доп вопросы не может быть прошедшей!';

        return $errors;
    }

    //проверка полей с датами, чтобы не были прошедшими: если выбрана прошедшая дата, то ответ = false
    private function checkDateFieldIfPast($date){
        return (!empty(trim($date)) && (strtotime($date) <= strtotime('yesterday')))
            ? $result = false
            : $result = true;
    }


    //2.1. Присваиваем при СОЗДАНИИ СДЕЛКИ название по шаблону мм-гггг-Мотор
    //на контакт (новое направление Физиков) не срабатывает, т.к. ID контакта он не присваивает, пока не создаст сделку
    //поэтому шаблон с мм.гг. - имя + фамилия - Ф нужно ставить на AfterDealAdd
    private function changeDealTitleByPatternBefore($arFields){
        $title = false;

        //Первое направление
        if($arFields['CATEGORY_ID'] == 0){
            if($arFields['COMPANY_ID'] > 0){
//                $companyArr = Bitrixfunctions::getCompanysListByFilter(['ID' => $arFields['COMPANY_ID']],['TITLE','ID']);
                $companyArr = Bitrixfunctions::getCompaniesByFilter(['ID' => $arFields['COMPANY_ID']],['TITLE','ID']);
                if($companyArr){
                    // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
                    if($arFields['UF_CRM_1533826055'])
                        $title = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3))
                            .' - '.$companyArr[0]['TITLE'].' - М - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
                    else
                        $title = date('m - Y - ').$companyArr[0]['TITLE'].' - М';
                }

            }
            else{
                // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
                if($arFields['UF_CRM_1533826055'])
                    $title = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3))
                        .' - М - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
                else $title = date('m - Y - ') . 'Мотор';
            }
        }

        //Здесь будет второе (новое) направление - его нужно переносить на After, т.к. ID сделки и привязка контакта
        //будет только после присовения сделке ID
        if($arFields['CATEGORY_ID'] == 1){
            // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
            if($arFields['UF_CRM_1533826055'])
                $title = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3))
                    .' - Ф - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
            else $title = date('m - Y - ') . 'Физ';

//            Bitrixfunctions::logData([$arFields,$dealContactsIds]);
        }

    return $title;
    }

    //2.2 Шаблон создания сделки после создания сделки для Физиков
    public function changeDealTitleByPatternAfter(&$arFields){
        if($arFields['CATEGORY_ID'] == 1){
            //получаем все ID контактов, кот. привязаны к сделке
            $dealContactsIds = Bitrixfunctions::getAllDealContacts($arFields['ID']);
            if($dealContactsIds){
                $contacts = Bitrixfunctions::getContactsByFilter(
                    ['ID' => $dealContactsIds],
                    ['ID','NAME','LAST_NAME']
                );
                if($contacts){
                    $contactsName = '';
                    foreach ($contacts as $contact)
                       $contactsName .= $contact['LAST_NAME'].' '.$contact['NAME'].', ';

                    $contactsName = rtrim($contactsName, ', ');

                    // ЕСЛИ В МАССИВЕ ЕСТЬ ПОЛЕ С ИД Предыдущей сделки, название меняем на такое!
                    if($arFields['UF_CRM_1533826055'])
                        $updFields['TITLE'] = str_replace('.',' - ',substr($arFields['__BEGINDATE'], 3))
                            .' - '.$contactsName.' - Ф - Предыдущая сделка № '.$arFields['UF_CRM_1533826055'];
                    else
                        $updFields['TITLE'] = date('m - Y - ').$contactsName.' - Ф';

                    $updRes = Bitrixfunctions::updateDeal($arFields['ID'],$updFields);
                }
            }
        }
//        Bitrixfunctions::logData(['TEST_FIELD' => 'CHANGE_DEAL_TITLE_AFTER_УДАЛИТЬ!!!',$updRes]);
    }


    //3. Корректировака источника при конвертации сделки из лида
    private function correctSourceOnLeadToDelaConvertation($incomeSource){
        switch ($incomeSource){
            case 'FACE_TRACKER':
                $typeID = 'COMPLEX'; //Face-трекер
                break;
            case 'CALL':
                $typeID = 'GOODS'; //База
                break;
            case 'EMAIL':
                $typeID = 'SERVICES'; //Выставка/Конференция
                break;
            case 'WEB':
                $typeID = 1; //Лизинг
                break;
            case 'ADVERTISING':
                $typeID = 2; //Брокеры
                break;
            case 'PARTNER':
                $typeID = 3; //Регресс
                break;
            case 'RECOMMENDATION':
                $typeID = 4; //Кросс
                break;
            case 'TRADE_SHOW':
                $typeID = 5; //Лизингополучатель
                break;
            case 'WEBFORM':
                $typeID = 6; //Банк
                break;
            case 'CALLBACK':
                $typeID = 7; //ДМС
                break;
            case 'OTHER':
                $typeID = 8; //Запрос
                break;
            case 1:
                $typeID = 9; //входящие и исходящие звонки
                break;
            case 2:
                $typeID = 10; //CRM-формы
                break;
            case 3:
                $typeID = 11; //e-mail
                break;
            case 6:
                $typeID = 13; //Физическое лицо
                break;
            default:
                $typeID = 'SALE'; //Значение не задано
                break;
        }
        return $typeID;
    }


    //функция перехода по стадиям для МОТОР
    private function checkMotorCategory($arFields,$dealFields){
        $errors = [];

        //массив стадий для "актуализация", чтобы не дублировать ту стену
        $dealStagesActualisation = [2,'PREPARATION','PREPAYMENT_INVOICE','WON','LOSE',3];

        //массив стадий для "Тендер"
        $dealStagesTender = ['PREPAYMENT_INVOICE','WON','LOSE',3];


        //СТАДИЯ "Актуализация"
        //"Наличие брокера по Мотор" NEW
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            (
//                (isset($arFields['UF_CRM_1535445683']) && $arFields['UF_CRM_1535445683'] == '') ||
//                ($dealFields[0]['UF_CRM_1535445683'] == '' && !isset($arFields['UF_CRM_1535445683']))
//            )
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Наличие брокера по МОТОР"';
//
//
//        //"Брокеры"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['UF_CRM_59DBEBA4374C2']) && $arFields['UF_CRM_59DBEBA4374C2'] == '') ||
//                ($dealFields[0]['UF_CRM_59DBEBA4374C2'] == '' && !isset($arFields['UF_CRM_59DBEBA4374C2'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Брокеры"';
//
//        //"Ответственный"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['ASSIGNED_BY_ID']) && $arFields['ASSIGNED_BY_ID'] == '') ||
//                ($dealFields[0]['ASSIGNED_BY_ID'] == '' && !isset($arFields['ASSIGNED_BY_ID'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Ответственный"';
//
//        //"ТС по МТСБУ"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['UF_CRM_1532613305575']) && $arFields['UF_CRM_1532613305575'] == '') ||
//                ($dealFields[0]['UF_CRM_1532613305575'] == '' && !isset($arFields['UF_CRM_1532613305575'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "ТС по МТСБУ"';
//
//        //"Текущая СК по ОСГПО"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['UF_CRM_1534156053']) && $arFields['UF_CRM_1534156053'] == '') ||
//                ($dealFields[0]['UF_CRM_1534156053'] == '' && !isset($arFields['UF_CRM_1534156053'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Текущая СК по ОСГПО"';
//
//        //"Начало страхования ОСГПО"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['UF_CRM_1534156848']) && $arFields['UF_CRM_1534156848'] == '') ||
//                ($dealFields[0]['UF_CRM_1534156848'] == '' && !isset($arFields['UF_CRM_1534156848'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Начало страхования ОСГПО"';
//
//        //"Количество ТС подтвержденное по КАСКО"
//        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
//            ((isset($arFields['UF_CRM_1533131953485']) && $arFields['UF_CRM_1533131953485'] == '') ||
//                ($dealFields[0]['UF_CRM_1533131953485'] == '' && !isset($arFields['UF_CRM_1533131953485'])))
//        ) $errors[] = 'На стадии "Актуализация" необходимо заполнить поле "Количество ТС подтвержденное по КАСКО"';

        //остальное не делал, т.к. Эми должен сам настроить поля после обновления


        //"целевой/не целевой лид" + "К лиду"
        if(in_array($arFields['STAGE_ID'],$dealStagesActualisation) &&
            ((isset($arFields['UF_CRM_1532614252']) && $arFields['UF_CRM_1532614252'] == 0) ||
                ($dealFields[0]['UF_CRM_1532614252'] == 0 && !isset($arFields['UF_CRM_1532614252'])))
        ){
            if((isset($arFields['UF_CRM_1532614341']) && $arFields['UF_CRM_1532614341'] == '') ||
                ($dealFields[0]['UF_CRM_1532614341'] == '' && !isset($arFields['UF_CRM_1532614341'])))
                $errors[] = 'При невыбранном "Не целевом лиде" поле "К лиду" является обязательным';
        }

        //СТАДИЯ "Актуализация"


        //СТАДИЯ "Ожидание"
        //Обяз. заполнение поля причины
        if($arFields['STAGE_ID'] == 2 &&
            ((isset($arFields['UF_CRM_1533040894']) && $arFields['UF_CRM_1533040894'] == '') ||
                ($dealFields[0]['UF_CRM_1533040894'] == '' && !isset($arFields['UF_CRM_1533040894'])))
        ) $errors[] = 'До стадии "Ожидание" необходимо заполнить обязательное поле: Дата ожидания';
        //СТАДИЯ "Ожидание"


        //Исключение только для "СЛИТ"
//        if (
//            $arFields['STAGE_ID'] == 3 //"Слит"
//            &&
//            (
//                ((isset($arFields['UF_CRM_1532615106']) && $arFields['UF_CRM_1532615106'] == '') || ($dealFields[0]['UF_CRM_1532615106'] == '' && !isset($arFields['UF_CRM_1532615106'])))||
//                ((isset($arFields['UF_CRM_1532615151']) && $arFields['UF_CRM_1532615151'] == '') || ($dealFields[0]['UF_CRM_1532615151'] == '' && !isset($arFields['UF_CRM_1532615151'])))||
//                // поле файла, заменено на множ.     ((isset($arFields['UF_CRM_1532615192']) && $arFields['UF_CRM_1532615192'] == '') || ($dealFields[0]['UF_CRM_1532615192'] == '' && !isset($arFields['UF_CRM_1532615192'])))||
//                ((isset($arFields['UF_CRM_1554372334']) && $arFields['UF_CRM_1554372334'] == '') || ($dealFields[0]['UF_CRM_1554372334'] == '' && !isset($arFields['UF_CRM_1554372334'])))||
//                ((isset($arFields['UF_CRM_1532615220']) && $arFields['UF_CRM_1532615220'] == '') || ($dealFields[0]['UF_CRM_1532615220'] == '' && !isset($arFields['UF_CRM_1532615220'])))||
//                ((isset($arFields['UF_CRM_1534154476']) && $arFields['UF_CRM_1534154476'] == '') || ($dealFields[0]['UF_CRM_1534154476'] == '' && !isset($arFields['UF_CRM_1534154476']))) //Текущая страховая компания по КАСКО NEW!!!
//            )
//            &&
//            ((isset($arFields['UF_CRM_1532614252']) && $arFields['UF_CRM_1532614252'] == 0) || ($dealFields[0]['UF_CRM_1532614252'] == 0 && !isset($arFields['UF_CRM_1532614252']))) //Целевой лид по мотор или не целевой
//            &&
//            ((isset($arFields['UF_CRM_1532614341']) && $arFields['UF_CRM_1532614341'] != '') || ($dealFields[0]['UF_CRM_1532614341'] != '' && !isset($arFields['UF_CRM_1532614341'])))
//        )
//        {
//            return true;
//        }
        //Исключвение только для "СЛИТ"


        //стадия "Тендер"

        //"Описание разговора с ЛПР"
//       if(in_array($arFields['STAGE_ID'],$dealStagesTender) &&
//           ((isset($arFields['UF_CRM_1532615106']) && $arFields['UF_CRM_1532615106'] == '') ||
//               ($dealFields[0]['UF_CRM_1532615106'] == '' && !isset($arFields['UF_CRM_1532615106'])))
//       ) $errors[] = 'На стадии "Тендер" необходимо заполнить поле "Описание разговора с ЛПР"';
//
//        //"Дата отправления КП"
//        if(in_array($arFields['STAGE_ID'],$dealStagesTender) &&
//            ((isset($arFields['UF_CRM_1532615151']) && $arFields['UF_CRM_1532615151'] == '') ||
//                ($dealFields[0]['UF_CRM_1532615151'] == '' && !isset($arFields['UF_CRM_1532615151'])))
//        ) $errors[] = 'На стадии "Тендер" необходимо заполнить поле "Дата отправления КП"';
//
//        //"Вложение тендерной документации"
//        if(in_array($arFields['STAGE_ID'],$dealStagesTender) &&
//            ((isset($arFields['UF_CRM_1554372334']) && $arFields['UF_CRM_1554372334'] == '') ||
//                ($dealFields[0]['UF_CRM_1554372334'] == '' && !isset($arFields['UF_CRM_1554372334'])))
//        ) $errors[] = 'На стадии "Тендер" необходимо заполнить поле "Вложение тендерной документации"';
//
//        //"Наименьшая премия по тендеру"
//        if(in_array($arFields['STAGE_ID'],$dealStagesTender) &&
//            ((isset($arFields['UF_CRM_1532615220']) && $arFields['UF_CRM_1532615220'] == '') ||
//                ($dealFields[0]['UF_CRM_1532615220'] == '' && !isset($arFields['UF_CRM_1532615220'])))
//        ) $errors[] = 'На стадии "Тендер" необходимо заполнить поле "Наименьшая премия по тендеру"';
//
//        //"Текущая страховая компания по КАСКО"
//        if(in_array($arFields['STAGE_ID'],$dealStagesTender) &&
//            ((isset($arFields['UF_CRM_1534154476']) && $arFields['UF_CRM_1534154476'] == '') ||
//                ($dealFields[0]['UF_CRM_1534154476'] == '' && !isset($arFields['UF_CRM_1534154476'])))
//        ) $errors[] = 'На стадии "Тендер" необходимо заполнить поле "Текущая страховая компания по КАСКО"';

        //стадия "Тендер"

        //стадия "Доп Вопросы"

        //"Комментарий при какой причине выбрана данная стадия"
        if($arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' &&
            ((isset($arFields['UF_CRM_1532616726']) && $arFields['UF_CRM_1532616726'] == '') ||
                ($dealFields[0]['UF_CRM_1532616726'] == '' && !isset($arFields['UF_CRM_1532616726'])))
        ) $errors[] = 'На стадии "Доп. вопросы" необходимо заполнить поле "Комментарий при какой причине выбрана данная стадия"';

        //"Дата отправления КП Доп. вопросы"
        if($arFields['STAGE_ID'] == 'PREPAYMENT_INVOICE' &&
            ((isset($arFields['UF_CRM_1532616763']) && $arFields['UF_CRM_1532616763'] == '') ||
                ($dealFields[0]['UF_CRM_1532616763'] == '' && !isset($arFields['UF_CRM_1532616763'])))
        ) $errors[] = 'На стадии "Доп. вопросы" необходимо заполнить поле "Дата отправления КП Доп. вопросы"';

        //стадия "Доп Вопросы"

        return $errors;
    }

    //4. Функция после обновления сделки - для отлавливания переходов по стадиям и полей
    public function checkFieldsAfterDealUpdate(&$arFields){
        $arFields['TEST_FIELD'] = 'GET_FIELDS_AFTER_DEAL_UPDATE';

        if(['ID'] > 0){

            if(in_array($arFields['STAGE_ID'],['WON','LOSE','C1:WON','C1:LOSE'])){

                $dealSelect = ['ID','STAGE_ID','CATEGORY_ID','TITLE','ASSIGNED_BY_ID','CONTACT_ID','COMPANY_ID','UF_*'];
                $dealsRes = Bitrixfunctions::getDealsByFilter(['ID' => $arFields['ID']],$dealSelect);
                if($dealsRes){

                    //проверка, чтобы дочерней сделки c привязкой к текущей сделке НЕ БЫЛО!!! UF_CRM_1533826055
                    $childDealSelect = ['ID','STAGE_ID','CATEGORY_ID','TITLE'];
                    $childDealRes = Bitrixfunctions::getDealsByFilter(['UF_CRM_1533826055' => $dealsRes[0]['ID']],$dealSelect);

                    if(!$childDealRes){

                        if($dealsRes[0]['CATEGORY_ID'] == 0)
                            $newDealFields = [
                                'BEGINDATE' => date('d.m.Y', strtotime($dealsRes[0]['UF_CRM_1533826216']->toString(new \Bitrix\Main\Context\Culture(["FORMAT_DATETIME" => 'd.m.Y'])))),
                                'CATEGORY_ID' => $dealsRes[0]['CATEGORY_ID'],
                                'TITLE' => $dealsRes[0]['TITLE'].' следующий период (+1 год)',
                                'CONTACT_ID' => $dealsRes[0]['CONTACT_ID'],
                                'COMPANY_ID' => $dealsRes[0]['COMPANY_ID'],
                                'ASSIGNED_BY_ID' => $dealsRes[0]['ASSIGNED_BY_ID'],
                                'UF_CRM_59EE4DC739368' => $dealsRes[0]['UF_CRM_59EE4DC739368'],
                                'UF_CRM_5BB1DE9BCB83A' => $dealsRes[0]['UF_CRM_5BB1DE9BCB83A'],
                                'UF_CRM_5B8E54EF007FC' => $dealsRes[0]['UF_CRM_5B8E54EF007FC'],
                                'UF_CRM_5BB1DE9C49D2C' => $dealsRes[0]['UF_CRM_5BB1DE9C49D2C'],
                                'UF_CRM_5B75CA95BD958' => $dealsRes[0]['UF_CRM_5B75CA95BD958'],
                                'UF_CRM_5B75CA95D62D9' => $dealsRes[0]['UF_CRM_5B75CA95D62D9'],
                                'UF_CRM_5B75CA95E1791' => $dealsRes[0]['UF_CRM_5B75CA95E1791'],
                                'UF_CRM_1535445683' => $dealsRes[0]['UF_CRM_1535445683'],
                                'UF_CRM_59DBEBA4374C2' => $dealsRes[0]['UF_CRM_59DBEBA4374C2'],
                                'UF_CRM_1532613305575' => $dealsRes[0]['UF_CRM_1532613305575'],
                                'UF_CRM_1534156053' => $dealsRes[0]['UF_CRM_1534156053'],//Текущая СК по ОСГПО NEW
                                'UF_CRM_1534156848' => $dealsRes[0]['UF_CRM_1534156848'],//Начало страхования ОСГПО NEW
                                'UF_CRM_1533131953485' => $dealsRes[0]['UF_CRM_1533131953485'],
                                'UF_CRM_1532614252' => $dealsRes[0]['UF_CRM_1532614252'],
                                'UF_CRM_1532614341' => $dealsRes[0]['UF_CRM_1532614341'],
                                'UF_CRM_1532614542' => $dealsRes[0]['UF_CRM_1532614542'],
                                'UF_CRM_1533715533' => $dealsRes[0]['UF_CRM_1533715533'],
                                'UF_CRM_1533826055' => $dealsRes[0]['ID'],
                            ];

                        if($dealsRes[0]['CATEGORY_ID'] == 1)
                            $newDealFields = [
                                'BEGINDATE' => date('d.m.Y', strtotime($dealsRes[0]['UF_CRM_1533826216']->toString(new \Bitrix\Main\Context\Culture(["FORMAT_DATETIME" => 'd.m.Y'])))),
                                'CATEGORY_ID' => $dealsRes[0]['CATEGORY_ID'],
                                'TITLE' => $dealsRes[0]['TITLE'].' следующий период (+1 год)',
                                'CONTACT_ID' => $dealsRes[0]['CONTACT_ID'],
                                'COMPANY_ID' => $dealsRes[0]['COMPANY_ID'],
                                'ASSIGNED_BY_ID' => $dealsRes[0]['ASSIGNED_BY_ID'],
                                'UF_CRM_59EE4DC739368' => $dealsRes[0]['UF_CRM_59EE4DC739368'],
                                'UF_CRM_5BB1DE9BCB83A' => $dealsRes[0]['UF_CRM_5BB1DE9BCB83A'],
                                'UF_CRM_5B8E54EF007FC' => $dealsRes[0]['UF_CRM_5B8E54EF007FC'],
                                'UF_CRM_5BB1DE9C49D2C' => $dealsRes[0]['UF_CRM_5BB1DE9C49D2C'],
                                'UF_CRM_5B75CA95BD958' => $dealsRes[0]['UF_CRM_5B75CA95BD958'],
                                'UF_CRM_5B75CA95D62D9' => $dealsRes[0]['UF_CRM_5B75CA95D62D9'],
                                'UF_CRM_5B75CA95E1791' => $dealsRes[0]['UF_CRM_5B75CA95E1791'],
                                'UF_CRM_1535445683' => $dealsRes[0]['UF_CRM_1535445683'],
                                'UF_CRM_59DBEBA4374C2' => $dealsRes[0]['UF_CRM_59DBEBA4374C2'],
                                'UF_CRM_1532613305575' => $dealsRes[0]['UF_CRM_1532613305575'],
                                'UF_CRM_1534156053' => $dealsRes[0]['UF_CRM_1534156053'],//Текущая СК по ОСГПО NEW
                                'UF_CRM_1534156848' => $dealsRes[0]['UF_CRM_1534156848'],//Начало страхования ОСГПО NEW
                                'UF_CRM_1533131953485' => $dealsRes[0]['UF_CRM_1533131953485'],
                                'UF_CRM_1532614252' => $dealsRes[0]['UF_CRM_1532614252'],
                                'UF_CRM_1532614341' => $dealsRes[0]['UF_CRM_1532614341'],
                                'UF_CRM_1533715533' => $dealsRes[0]['UF_CRM_1533715533'],
                                'UF_CRM_1533826055' => $dealsRes[0]['ID'],
                            ];

                    $newDealAddRes = Bitrixfunctions::addDeal($newDealFields);
                    if($newDealAddRes['result'])
                        $writeNewDealIdIntoPriviousDealRes = Bitrixfunctions::updateDeal(
                            $dealsRes[0]['ID'],
                            ['UF_CRM_1533826141' => $newDealAddRes['result']]);

                    }
                }
            }
        }


//        Bitrixfunctions::logData([/*$newDealAddRes,*/$arFields,$newDealFields]);
    }


}