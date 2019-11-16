<?php

namespace Crmgenesis\Dealsevents;

class Bitrixfunctions{

    public function logData($data){
        $file = $_SERVER["DOCUMENT_ROOT"].'/zzz.log';
        file_put_contents($file, print_r([date('d.m.Y H:i:s'),$data],true), FILE_APPEND | LOCK_EX);
    }

//    public function getCompanysListByFilter($arFilter,$arSelect){
//        \CModule::IncludeModule("crm"); //Какого хера??
//        $result = [];
//        $db_list = \CCrmCompany::GetListEx(["ID" => "DESC"], $arFilter, false, false, $arSelect, []);
//        while($ar_result = $db_list->GetNext()) $result[] = $ar_result;
//        return $result;
//    }

    //получение Сделок по фильтру
    public function getDealsByFilter($filter,$select){
        return $record = \Bitrix\Crm\DealTable::getList([
            'select' => $select,
            'filter' => $filter,
        ])->fetchAll();
    }

    //получение Компаний по фильтру
    public function getCompaniesByFilter($filter,$select){
        return $record = \Bitrix\Crm\CompanyTable::getList([
            'select' => $select,
            'filter' => $filter,
        ])->fetchAll();
    }

    //получение Контактов по фильтру
    public function getContactsByFilter($filter,$select){
        return $record = \Bitrix\Crm\ContactTable::getList([
            'select' => $select,
            'filter' => $filter,
        ])->fetchAll();
    }

    //D7 - получение всех контактов из сделки
    public function getAllDealContacts($dealID){
        return \Bitrix\Crm\Binding\DealContactTable::getDealContactIDs($dealID);
    }

}