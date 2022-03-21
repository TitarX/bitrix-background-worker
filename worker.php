<?php
if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REQUEST_METHOD'])) {
    exit;
}

spl_autoload_register(
    function ($className) {
        $classPath = __DIR__ . '/lib/';

        $className = preg_replace('/^\\\\/', '', $className);
        $className = preg_replace('/^Dev\\\\PerfCode\\\\BgWorker\\\\/', '', $className);

        $arClassPath = explode('\\', $className);
        $classPath .= implode(DIRECTORY_SEPARATOR, $arClassPath);
        $classPath .= '.php';

        if (file_exists($classPath)) {
            include_once $classPath;
        }
    }
);

use Dev\PerfCode\BgWorker\Settings;
use Dev\PerfCode\BgWorker\MiscHelper;
use Dev\PerfCode\BgWorker\RuntimeDataHelper;
use Dev\PerfCode\BgWorker\Worker;

$_SERVER['DOCUMENT_ROOT'] = MiscHelper::getSiteDirPath();

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

date_default_timezone_set(Settings::getTimezone());
set_time_limit(0);
sleep(5);

if (CModule::IncludeModule('iblock')) {
    $productIblockId = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getProductIblockIdQueryString());
    $brandIblockId = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getBrandIblockIdQueryString());
    Worker::start($productIblockId, $brandIblockId);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
