<?
use Bitrix\Highloadblock as HLLOOK;
use Bitrix\Highloadblock\HighloadBlockTable as HLBTLOOK;

if (!function_exists('VD')) {
    function VD($var, $die = false, $all = false){
        global $USER;
        if($USER->IsAuthorized() or $all == true) {
            echo "<pre>";
            var_dump($var);
            echo "</pre>";
        }
        if($die) {
            die;
        }
    }
}

if (!function_exists('GetPropertyID')) {
    function GetPropertyID($IBLOCK_ID, $PROPERTY_CODE){
        CModule::IncludeModule("iblock");
        $PROP = CIBlock::GetProperties($IBLOCK_ID, Array(), Array("CODE" => $PROPERTY_CODE));
        if ($arrPROP = $PROP->Fetch()) {
            return $arrPROP["ID"];
        } else {
            return false;
        }
    }
}

if (!function_exists('GetIBlockID')) {
    function GetIBlockID($type, $iblock_code){
        if (CModule::IncludeModule("iblock")) {
            $res = CIBlock::GetList(
                Array(),
                Array(
                    'TYPE' => $type,
                    'ACTIVE' => 'Y',
                    "CODE" => $iblock_code
                ), true
            );

            while ($ar_res = $res->Fetch()) {
                return $ar_res["ID"];
            }
        }
        return false;
    }
}

if (!function_exists('GetSectionID')) {
    function GetSectionID($section_id, $section_code, $arFilter){
        CModule::IncludeModule("iblock");
        $section_id = intval($section_id);
        if($section_id > 0)
        {
            return $section_id;
        }
        elseif(strlen($section_code) > 0)
        {
            $arFilter["=CODE"] = $section_code;

            $rsSection = CIBlockSection::GetList(array(), $arFilter);
            while($arSection = $rsSection->Fetch())
                $arS[] = $arSection["ID"];
            foreach ($arS as $key => $value) {

                $res = CIBlockSection::GetByID($value);
                if($ar_res = $res->GetNext()){

                    $ress = CIBlock::GetByID($ar_res["IBLOCK_ID"]);
                    while ($rs = $ress->GetNext()) {

                        if($rs["LID"] == SITE_ID){
                            return $value;
                        }

                    }

                }
            }
            return $arS;
        }
        return 0;
    }
}

if (!function_exists('GetGroupID')) {
    function GetGroupID($GROUPCODE){
        $rsGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ("STRING_ID" => $GROUPCODE));
        if($group = $rsGroups->Fetch()){
            return $group["ID"];
        }else{
            return false;
        }
    }
}

if (!function_exists('GetIBElementProperties')) {
    function GetIBElementProperties($IBLOCK_TYPE, $IBLOCK_ID, $ELEMENT_ID){
        CModule::IncludeModule('iblock');
        $dbEl = CIBlockElement::GetList(Array(), Array("IBLOCK_TYPE" => $IBLOCK_TYPE, "IBLOCK_ID" => $IBLOCK_ID));
        while ($obEl = $dbEl->GetNextElement()) {
            $field = $obEl->GetFields();
            $props = $obEl->GetProperties();
            if($field["ID"] == $ELEMENT_ID){
                return $props;
            }
        }
        return false;
    }
}

if (!function_exists('GetIBElementProperty')) {
    function GetIBElementProperty($IBLOCK_TYPE, $IBLOCK_ID, $ELEMENT_ID, $PROPERTY_CODE){
        CModule::IncludeModule('iblock');
        $dbEl = CIBlockElement::GetList(Array(), Array("IBLOCK_TYPE" => $IBLOCK_TYPE, "IBLOCK_ID" => $IBLOCK_ID));
        while ($obEl = $dbEl->GetNextElement()) {
            $field = $obEl->GetFields();
            $props = $obEl->GetProperties();
            if($field["ID"] == $ELEMENT_ID){
                if($props[$PROPERTY_CODE]){
                    return $props[$PROPERTY_CODE];
                }else{
                    return false;
                }
            }
        }
        return false;
    }
}

if (!function_exists('GetIBlockPropertyList')) {
    function GetIBlockPropertyList($IBLOCK_ID, $PROPERTY_CODE){
        CModule::IncludeModule('iblock');
        $db_enum_list = CIBlockProperty::GetPropertyEnum($PROPERTY_CODE, Array(), Array("IBLOCK_ID"=>$IBLOCK_ID));
        while($ar_enum_list = $db_enum_list->GetNext())
        {
            $arr[] = $ar_enum_list;
        }

        if(is_array($arr)){
            return $arr;
        }else{
            return false;
        }
    }
}

if (!function_exists('GetIBElementPropertyFilePath')) {
    function GetIBElementPropertyFilePath($IBLOCK_TYPE, $IBLOCK_CODE, $ELEMENT_ID, $PROPERTY_CODE){
        $PROP = GetIBElementProperty($IBLOCK_TYPE, GetIBlockID($IBLOCK_TYPE, $IBLOCK_CODE), $ELEMENT_ID, $PROPERTY_CODE);
        if($PROP){
            if($PROP["PROPERTY_TYPE"] == "F"){
                if($PROP["MULTIPLE"] == "N"){
                    if(CFile::GetPath($PROP["VALUE"])) {
                        return CFile::GetPath($PROP["VALUE"]);
                    }else{
                        return false;
                    }
                }else{
                    foreach ($PROP["VALUE"] as $file){
                        $arrFile[] = CFile::GetPath($file);
                    }
                    if(is_array($arrFile)){
                        return $arrFile;
                    }else{
                        return false;
                    }
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}

if (!function_exists('GetHLBlockID')) {
    function GetHLBlockID($HLBLOCKNAME){
        CModule::IncludeModule('highloadblock');
        if($arrhlblock = HLLOOK\HighloadBlockTable::getList(['filter' => ['=NAME' => $HLBLOCKNAME]])->fetch()){
            return $arrhlblock["ID"];
        }else{
            return false;
        }
    }
}

if (!function_exists('GetHLBlockList')) {

    function GetHLBlockList($HLBLOCKNAME){
        CModule::IncludeModule('highloadblock');
        $HBLOCKID = GetHLBlockID($HLBLOCKNAME);

        if($HBLOCKID){
            $entity_data_class = GetEntityDataClass($HBLOCKID);
            $rsData = $entity_data_class::getList(array(
                'select' => array('*')
            ));
            $arrEl = [];
            while($el = $rsData->fetch()){
                $arrEl[] = $el;
            }

            return $arrEl;
        }else{
            return false;
        }
    }
}

if (!function_exists('GetHLBlockFields')) {

    function GetHLBlockFields($HLBLOCKNAME){
        CModule::IncludeModule('highloadblock');
        $HBLOCKID = GetHLBlockID($HLBLOCKNAME);
        if($HBLOCKID) {
            $hlblock = HLBTLOOK::getById($HBLOCKID)->fetch();
            $entity = HLBTLOOK::compileEntity($hlblock);
            $arrProp = $entity->getFields();
            foreach ($arrProp as $key => $prop){
                $arrName[] = $key;
            }
            return $arrName;
        }else{
            return false;
        }
    }
}

if (!function_exists('AddHLBlockElement')) {

    function AddHLBlockElement($HLBLOCKNAME, $arFields){
        CModule::IncludeModule('highloadblock');
        $HBLOCKID = GetHLBlockID($HLBLOCKNAME);
        if($HBLOCKID) {
            $entity_data_class = GetEntityDataClass($HBLOCKID);
            try{
                $result = $entity_data_class::add($arFields);
                return ($result->getId());
            }catch (Exception $e){
                return $e->getMessage();
            }
        }else{
            return false;
        }
    }
}

if (!function_exists('UpdateHLBlockElement')) {

    function UpdateHLBlockElement($HLBLOCKNAME, $ID ,$arFields){
        CModule::IncludeModule('highloadblock');
        $HBLOCKID = GetHLBlockID($HLBLOCKNAME);
        if($HBLOCKID) {
            $entity_data_class = GetEntityDataClass($HBLOCKID);
            try{
                $result = $entity_data_class::update($ID,$arFields);
                return true;
            }catch (Exception $e){
                return $e->getMessage();
            }
        }else{
            return false;
        }
    }
}

if (!function_exists('DeleteHLBlockElement')) {

    function DeleteHLBlockElement($HLBLOCKNAME, $ID){
        CModule::IncludeModule('highloadblock');
        $HBLOCKID = GetHLBlockID($HLBLOCKNAME);
        if($HBLOCKID) {
            $entity_data_class = GetEntityDataClass($HBLOCKID);
            try{
                $result = $entity_data_class::delete($ID);
                return true;
            }catch (Exception $e){
                return $e->getMessage();
            }
        }else{
            return false;
        }
    }
}


/*Недокументированные функции 1С-Битрикс*/
/*
______________________________________________________________________________________________________________________________________________________________________________
Функция удаляет из массива $arr все элементы с пустыми значениями. Если установлен флаг $trim_value, для непустых значений будет применена функция trim().
function TrimArr(&$arr, $trim_value=false);
______________________________________________________________________________________________________________________________________________________________________________
function randString($pass_len=10, $pass_chars=false)
Возвращает строку указанной длины $pass_len, состоящую из символов набора a-zA-Z0-9, выбранных случайным образом. В параметре $pass_chars можно передавать:
1. строку символов, которая будет являться базовым набором;
2. массив строк. Результат будет формироваться следующим образом:
- элементы массива перемешиваются
- в цикле из каждой строки (элемента массива) выбирается случайный символ до получения нужного количества.
______________________________________________________________________________________________________________________________________________________________________________
function TrimExAll($str,$symbol)
Удаляет все крайние символы $symbol в строке $str.
______________________________________________________________________________________________________________________________________________________________________________
function GetFileExtension($path)
По заданному пути к файлу $path возвращает расширение файла. По сути, функция возвращает символы после последней точки в строке.
______________________________________________________________________________________________________________________________________________________________________________
function GetFileType($path)
По заданному пути к файлу $path возвращает его тип:
IMAGE для jpg, jpeg, gif, bmp, png
FLASH для swf
SOURCE для html, htm, asp, aspx, phtml, php, php3, php4, php5, php6, shtml, sql, txt, inc, js, vbs, tpl, css, shtm
UNKNOWN для остальных
______________________________________________________________________________________________________________________________________________________________________________
*/
?>