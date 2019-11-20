<?php

//подключение файла с какими-то данными модуля и проверкой на D7
include_once(dirname(__DIR__).'/lib/main.php');

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;

//Это подключение файла с классом тек. модуля
use \Crmgenesis\Dealsevents\Main;

//Lang-файлы
Loc::loadMessages(__FILE__);

class crmgenesis_dealsevents extends CModule{

    public $MODULE_ID = 'crmgenesis.dealsevents';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct(){
        $arModuleVersion = [];
        include(__DIR__."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_PARTNER_URI");
    }

    public function InstallEvents(){
//        EventManager::getInstance()->registerEventHandler('main','OnBeforeProlog',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','addCustomScripts');

        //перед созданием лида
        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmLeadAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','reqFieldsBeforCreateLead');
        //перед созданием сделки
        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmDealAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','reqFieldsBeforCreateDeal');

        //после создания сделки - изменение названия по шаблону для Физ.
        EventManager::getInstance()->registerEventHandler('crm','OnAfterCrmDealAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','changeDealTitleByPatternAfter');

        //перед обновлением сделки - ДОПИСАТЬ!!!
        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmDealUpdate',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','checkFieldsBeforeDealUpdate');

        //после обновления сделки - для отлавливания переходов по стадиям и полей
        EventManager::getInstance()->registerEventHandler('crm','OnAfterCrmDealUpdate',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','checkFieldsAfterDealUpdate');


        return true;
    }

    public function UnInstallEvents(){
//        EventManager::getInstance()->unRegisterEventHandler('main','OnBeforeProlog',$this->MODULE_ID,'\Crmgenesis\Dealsevents\customevent','addCustomScripts');

        //перед созданием лида
        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmLeadAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','reqFieldsBeforCreateLead');

        //перед созданием сделки
        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmDealAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','reqFieldsBeforCreateDeal');

        //после создания сделки - изменение названия по шаблону для Физ.
        EventManager::getInstance()->unRegisterEventHandler('crm','OnAfterCrmDealAdd',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','changeDealTitleByPatternAfter');

        //перед обновлением сделки
        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmDealUpdate',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','checkFieldsBeforeDealUpdate');

        //после обновления сделки - для отлавливания переходов по стадиям и полей
        EventManager::getInstance()->unRegisterEventHandler('crm','OnAfterCrmDealUpdate',$this->MODULE_ID,'\Crmgenesis\Dealsevents\Customevent','checkFieldsAfterDealUpdate');

        return true;
    }

    public function InstallFiles($arParams = [])
    {
//        CopyDirFiles(Main::GetPatch()."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
//        CopyDirFiles(Main::GetPatch()."/install/css/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/", true, true);

        return true;
    }

    public function UnInstallFiles()
    {

//        DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
//        DeleteDirFilesEx("/bitrix/css/crmgenesis/workPanelControl");

        //удаление папки itlogic из компонентов, если в ней пусто после удаления своего компонента
//        if(!glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/crmgenesis/*')) DeleteDirFilesEx("/bitrix/js/crmgenesis");
//        if(!glob($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/crmgenesis/*')) DeleteDirFilesEx("/bitrix/css/crmgenesis");

        return true;
    }

    public function DoInstall(){
        global $APPLICATION;
        if(Main::isVersionD7())
        {
            $this->InstallFiles();
            $this->InstallEvents();
            ModuleManager::registerModule($this->MODULE_ID);
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_INSTALL_ERROR_VERSION"));
        }

//        $APPLICATION->IncludeAdminFile(Loc::getMessage("CRM_GENESIS_DEAL_EVENTS_INSTALL_TITLE"), Main::GetPatch()."/install/step.php");
    }

    public function DoUninstall(){
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }

}