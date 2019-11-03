<?php

//подключение файла с какими-то данными модуля и проверкой на D7
include_once(dirname(__DIR__).'/lib/main.php');

//подключение файла с базовыми функциями
include_once(dirname(__DIR__).'/lib/bitrixfunctions.php');
include_once(dirname(__DIR__).'/lib/customevent.php');

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;

//Это подключение файла с классом тек. модуля
use \Crmgenesis\Dealsevents\Main;

//подключение файла с базовыми функциями здесь, чтобы вызывать в нужном классе его функции
use \Crmgenesis\Dealsevents\bitrixfunctions;
use \Crmgenesis\Dealsevents\customevent;

//Lang-файлы
Loc::loadMessages(__FILE__);

class crmgenesis_dealsevents extends CModule{


    public function __construct(){
        $arModuleVersion = [];
        include(__DIR__."/version.php");
        $this->MODULE_ID = Main::MODULE_ID;
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("CRM_GENESIS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("CRM_GENESIS_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("CRM_GENESIS_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("CRM_GENESIS_PARTNER_URI");
    }

    public function InstallEvents(){
        EventManager::getInstance()->registerEventHandler('main','OnBeforeProlog',Main::MODULE_ID,'\Crmgenesis\Dealsevents\customevent','addCustomScripts');

        //перед созданием лида
//        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmLeadAdd',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','reqFieldsBeforCreateLead');
        //перед созданием сделки
//        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmDealAdd',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','reqFieldsBeforCreateDeal');

        //перед обновлением сделки - ДОПИСАТЬ!!!
//        EventManager::getInstance()->registerEventHandler('crm','OnBeforeCrmDealUpdate',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','checkFieldsBeforeDealUpdate');


        return true;
    }

    public function UnInstallEvents(){
        EventManager::getInstance()->unRegisterEventHandler('main','OnBeforeProlog',Main::MODULE_ID,'\Crmgenesis\Dealsevents\customevent','addCustomScripts');

        //перед созданием лида
//        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmLeadAdd',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','reqFieldsBeforCreateLead');
        //перед созданием сделки
//        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmDealAdd',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','reqFieldsBeforCreateDeal');

        //перед обновлением сделки
//        EventManager::getInstance()->unRegisterEventHandler('crm','OnBeforeCrmDealUpdate',Main::MODULE_ID,'\Crmgenesis\Newworktimecontrol\customevent','checkFieldsBeforeDealUpdate');

        return true;
    }

    public function InstallFiles($arParams = [])
    {
        CopyDirFiles(Main::GetPatch()."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
//        CopyDirFiles(Main::GetPatch()."/install/css/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/", true, true);

        return true;
    }

    public function UnInstallFiles()
    {

        DeleteDirFilesEx("/bitrix/js/".Main::MODULE_ID);
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
            ModuleManager::registerModule(Main::MODULE_ID);
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("CRM_GENESIS_INSTALL_ERROR_VERSION"));
        }

//        $APPLICATION->IncludeAdminFile(Loc::getMessage("CRM_GENESIS_INSTALL_TITLE"), Main::GetPatch()."/install/step.php");
    }

    public function DoUninstall(){
        ModuleManager::unRegisterModule(Main::MODULE_ID);
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }

}