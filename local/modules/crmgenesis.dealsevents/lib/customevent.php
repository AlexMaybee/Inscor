<?php

namespace Crmgenesis\Dealsevents;

//Подключение js D7
use \Bitrix\Main\Page\Asset;

class customevent{

    public function addCustomScripts(){
        //Штатная библиотека
        if(!\CJSCore::Init(["jquery2"]))
            \CJSCore::Init(["jquery2"]);

        Asset::getInstance()->addJs('/bitrix/js/'.Main::MODULE_ID.'/script.js',true);
    }

    public function reqFieldsBeforCreateLead(&$arFields){
        $errors = [];

        if($arFields['COMPANY_TITLE'] == '') $errors[] = 'Не заполнено название компании!';
        if($arFields['FM']['PHONE']['n0']['VALUE']) $errors[] = 'Не указан номер телефона!';
        if($arFields['UF_CRM_1508326827'] == 4804
            || $arFields['UF_CRM_1508326827'] == '') $errors[] = 'Не выбран город!';
        if($arFields['UF_CRM_1533213299'] == 5
            || $arFields['UF_CRM_1533213299'] == '') $errors[] = 'Не выбран источник информации!';
        if($arFields['UF_CRM_1533628062'] == 4805
            || $arFields['UF_CRM_1533628062'] == '') $errors[] = 'Не выбрана отрасль!';

        $arFields['SOURCE_ID'] = $arFields['UF_CRM_1533213299']; // Доработка отн. источников - из кастомного поля источника кидаем в SOURCE_ID

        if($errors){
            $err = '';
            foreach ($errors as $error)
                $err .= $error."\n";
            $arFields['RESULT_MESSAGE'] = $err;
            return false;
        }
        else return true;
    }


    public function reqFieldsBeforCreateDeal(&$arFields){
        $errors = [];

        //Обязательные поля, которые слетели: Город, Отрасль, Источники new
       if($arFields['UF_CRM_59EE4DC739368'] == 4810
           || $arFields['UF_CRM_59EE4DC739368'] == '')
           $errors[] = 'Поле Город обязательно к заполнению!';

       if($arFields['UF_CRM_5B8E54EF007FC'] == 5192
           || $arFields['UF_CRM_5B8E54EF007FC'] == '')
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
        $customNewDealTitle = self::changeDealTitleByPattern($arFields);
        if($customNewDealTitle) $arFields['TITLE'] = $customNewDealTitle;

        //Конвертация сделки из лида, перепривязка источников
        $arFields['TYPE_ID'] = self::correctSourceOnLeadToDelaConvertation($arFields['UF_CRM_5B75CA95E1791']);


        if($errors){
            $err = '';
            foreach ($errors as $error){
                $err .= $error."\n";
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
        $bitrixfunctionsObj = new bitrixfunctions;

        $bitrixfunctionsObj->logData($arFields);

    }



    //Фукнции-сателлиты


    //1. Этап ограничения выставления задних чисел дат в 3-ти полях (было в 5-ти)
    public function checkIfNeededDealDatesNotPast($arFields){
        $errors = [];

        //дата "Дата ожидания"
        if($arFields['UF_CRM_1533040894'] != '' && (strtotime($arFields['UF_CRM_1533040894']) <= strtotime('yesterday')))
            $errors[] = 'Дата ожидания не может быть прошедшей!';

        //дата "Дата отправления КП"
        if($arFields['UF_CRM_1532615151'] != '' && (strtotime($arFields['UF_CRM_1532615151']) <= strtotime('yesterday')))
            $errors[] = 'Дата отправления КП не может быть прошедшей!';

        //дата "Дата отправления КП Доп вопросы"
        if($arFields['UF_CRM_1532616763'] != '' && (strtotime($arFields['UF_CRM_1532616763']) <= strtotime('yesterday')))
            $errors[] = 'Дата отправления КП Доп вопросы не может быть прошедшей!';

        return $errors;
    }

    //2. Присваиваем при СОЗДАНИИ СДЕЛКИ название по шаблону мм-гггг-Мотор
    public function changeDealTitleByPattern($arFields){
        $bitrixfunctionsObj = new bitrixfunctions;

        $title = false;

        //Первое направление
        if($arFields['CATEGORY_ID'] == 0){
            if($arFields['COMPANY_ID'] > 0){
                $companyArr = $bitrixfunctionsObj->getCompanysListByFilter(['ID' => $arFields['COMPANY_ID']],['TITLE','ID']);
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

        //Здесь будет второе (новое) направление

    return $title;
    }

    //3. Корректировака источника при конвертации сделки из лида
    public function correctSourceOnLeadToDelaConvertation($incomeSource){
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

}