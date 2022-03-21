<?php

namespace Dev\PerfCode\BgWorker;

class Worker
{
    private static $piece = 0;
    private static $productIblockId = 0;
    private static $brandIblockId = 0;
    private static $lastProductId = 0;
    private static $doneProductCount = 0;
    private static $totalProductsCount = 0;

    public static function start($productIblockId, $brandIblockId): void
    {
        self::$piece = 0;
        self::$productIblockId = $productIblockId;
        self::$brandIblockId = $brandIblockId;
        self::$lastProductId = 0;
        self::$doneProductCount = 0;
        self::$totalProductsCount = IblockHelper::getElementsCount($productIblockId);

        RuntimeDataHelper::updateSingleValue(
            RuntimeDataHelper::getTotalProductsCountQueryString(),
            self::$totalProductsCount
        );

        //
    }
}
