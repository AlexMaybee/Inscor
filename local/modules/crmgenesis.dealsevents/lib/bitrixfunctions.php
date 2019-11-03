<?php

namespace Crmgenesis\Dealsevents;

class bitrixfunctions{

    public function logData($data){
        $file = $_SERVER["DOCUMENT_ROOT"].'/zzz.log';
        file_put_contents($file, print_r([date('d.m.Y H:i:s'),$data],true), FILE_APPEND | LOCK_EX);
    }

    public function getCompanysListByFilter($arFilter,$arSelect){
        \CModule::IncludeModule("crm"); //Какого хера??
        $result = [];
        $db_list = \CCrmCompany::GetListEx(["ID" => "DESC"], $arFilter, false, false, $arSelect, []);
        while($ar_result = $db_list->GetNext()) $result[] = $ar_result;
        return $result;
    }

}