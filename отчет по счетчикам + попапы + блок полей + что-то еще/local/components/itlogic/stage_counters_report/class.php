<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule("crm");

class MyDealsStageCountersTest extends CBitrixComponent{

    public function mainFunct(){
        $dealCats = $this->getCategories();
        return $dealCats;
    }


    public function getCategories(){
        $catIds = \Bitrix\Crm\Category\DealCategory::getAllIDs();
        $massive = [];
        foreach ($catIds as $catId){
            $massive[] = [
                'ID' => $catId,
                'NAME' => $this->getCategoryNameById($catId),
                'STAGES' => $this->getCategoryStages($catId),
            ];
        }
        return $massive;
    }

    private function getCategoryNameById($category_id){
        return $name = \Bitrix\Crm\Category\DealCategory::getName($category_id);
    }

    private function getCategoryStages($category_id){
        $stages = \Bitrix\Crm\Category\DealCategory::getStageList($category_id);

        $stagesMassive = [];
        foreach ($stages as $key => $value){
            $stagesMassive[] = ['STAGE_ID' => $key, 'STAGE_NAME' => $value];
        }
        return $stagesMassive;
    }

    //получение истории сделок
    public function getDealHistoryByFilter1($arFilter,$arSelect,$dealCreateDate,$stagesMassive){
        $deal_history_list = CCrmEvent::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect, array());

        $result = [];
        while($res = $deal_history_list->GetNext()) {
            $result[] = $res;
        }

        return $result;
    }

}