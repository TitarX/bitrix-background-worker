<?php

namespace Dev\PerfCode\BgWorker;

use CIBlockElement;

class IblockHelper
{
    public static function getElementsCount($iblockId): string
    {
        $arFilter = array('IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y');
        return CIBlockElement::GetList(array(), $arFilter, array(), false, array());
    }
}
