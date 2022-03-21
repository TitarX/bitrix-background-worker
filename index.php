<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Background worker");

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

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Dev\PerfCode\BgWorker\Settings;
use Dev\PerfCode\BgWorker\MiscHelper;
use Dev\PerfCode\BgWorker\RuntimeDataHelper;
use Dev\PerfCode\BgWorker\WorkerHelper;

Asset::getInstance()->addCss(MiscHelper::getAppDirRelativePath() . '/css/bgworker.css');
Asset::getInstance()->addJs(MiscHelper::getAppDirRelativePath() . '/js/bgworker.js');
?>

<?php
global $USER;
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

date_default_timezone_set(Settings::getTimezone());

$workUnvailabilityMessages = MiscHelper::checkPossibilityWorking();

$request = Application::getInstance()->getContext()->getRequest();
if ($request->isPost()) {
    $postAction = $request->get('post-action');
    if ($postAction === 'start') { // Запуск воркера
        if (!WorkerHelper::isWorkerRunning() && empty($workUnvailabilityMessages)) {
            $productIblockId = $request->get('product-iblock-id');
            $productIblockId = trim($productIblockId);
            $brandIblockId = $request->get('brand-iblock-id');
            $brandIblockId = trim($brandIblockId);

            if (is_numeric($productIblockId) && is_numeric($brandIblockId)) {
                RuntimeDataHelper::deleteDataFile();

                $productIblockId = intval($productIblockId);
                $brandIblockId = intval($brandIblockId);
                RuntimeDataHelper::createDataFile();
                RuntimeDataHelper::updateSingleValue(RuntimeDataHelper::getProductIblockIdQueryString(), $productIblockId);
                RuntimeDataHelper::updateSingleValue(RuntimeDataHelper::getBrandIblockIdQueryString(), $brandIblockId);

                $phpPath = exec('which php');
                $phpPath = trim($phpPath);
                $phpParameters = '-d short_open_tag=1';
                $workerPath = WorkerHelper::getWorkerPath();
                $commandAddition = '> /dev/null &';
                exec("$phpPath $phpParameters $workerPath $commandAddition");
            }
        }
    } else if ($postAction === 'stop') { // Остановка воркера
        WorkerHelper::killWorker();
        print '<script type="text/javascript">setTimeout(function () {window.location.href = "' . $request->getRequestUri() . '";}, 10);</script>';
    }
}
?>

<div id="bgworker-container">
    <fieldset>
        <legend>Обходчик товаров, привязка бренда</legend>
        <?php if (WorkerHelper::isWorkerRunning()): ?>
            <div>
                <h3 class="info">Выполняется ...</h3>
                <form action="" method="post" name="bgworker-stop-form">
                    <input type="hidden" name="post-action" value="stop">
                    <input type="button" id="stop-button" name="stop-button" value="Прервать" class="btn btn-default btn-block button-bgworker button-bgworker-stop">
                </form>
                <script type="text/javascript">
                    setTimeout(function () {
                        window.location.href = '<?= $request->getRequestUri() ?>';
                    }, 15000);
                </script>
            </div>
        <?php else: ?>
            <?php if (empty($workUnvailabilityMessages)): ?>
                <form action="" method="post" name="bgworker-start-form">
                    <input type="hidden" name="post-action" value="start">
                    <input type="number" min="1" max="1000" name="product-iblock-id" id="product-iblock-id" placeholder="Идентификатор инфоблока товаров">
                    <input type="number" min="1" max="1000" name="brand-iblock-id" id="brand-iblock-id" placeholder="Идентификатор инфоблока брендов">
                    <input type="submit" name="start-button" value="Пуск" class="btn btn-default btn-block button-bgworker button-bgworker-start">
                </form>
            <?php else: ?>
                <div>
                    <h3 class="error">Выполнение недоступно!</h3>
                    <?php foreach ($workUnvailabilityMessages as $workUnvailabilityMessage): ?>
                        <div class="error"><?= $workUnvailabilityMessage ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (RuntimeDataHelper::isDataFileExists()): ?>
            <?php
            $pieceValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getPieceQueryString());
            $totalProductsCountValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getTotalProductsCountQueryString());
            ?>
            <?php if (!empty($pieceValue) && !empty($totalProductsCountValue)): ?>
                <?php
                $productIblockIdValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getProductIblockIdQueryString());
                $productIblockNameValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getProductIblockNameQueryString());
                $brandIblockIdValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getBrandIblockIdQueryString());
                $brandIblockNameValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getBrandIblockNameQueryString());
                $recallTimestampValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getRecallTimestampQueryString());
                $doneProductCountValue = RuntimeDataHelper::readSingleValue(RuntimeDataHelper::getDoneProductCountQueryString());

                $statusResultHeader = 'текущего';
                $cssClassResultInfo = 'info';
                if (!WorkerHelper::isWorkerRunning()) {
                    $statusResultHeader = 'последнего';
                    if ($doneProductCountValue !== $totalProductsCountValue) {
                        $cssClassResultInfo = 'warning';
                    }
                }
                ?>
                <div class="<?= $cssClassResultInfo ?>">
                    <h3 class="<?= $cssClassResultInfo ?>">Результаты <?= $statusResultHeader ?> выполнения:</h3>
                    <div>
                        <span>Инфоблок товаров (<?= $productIblockIdValue ?>): </span><span><?= $productIblockNameValue ?></span>
                    </div>
                    <div>
                        <span>Инфоблок брендов (<?= $brandIblockIdValue ?>): </span><span><?= $brandIblockNameValue ?></span>
                    </div>
                    <?php if (WorkerHelper::isWorkerRunning()): ?>
                        <div><span>Шаг: </span><span><?= $pieceValue ?></span></div>
                    <?php endif; ?>
                    <div>
                        <span>Время запуска <?= $statusResultHeader ?> шага: </span><span><?= date('d F Y - H:i:s', $recallTimestampValue) ?> (<?= date_default_timezone_get() ?>)</span>
                    </div>
                    <div><span>Всего товаров: </span><span><?= $totalProductsCountValue ?></span></div>
                    <div><span>Обработано товаров: </span><span><?= $doneProductCountValue ?></span></div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </fieldset>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
