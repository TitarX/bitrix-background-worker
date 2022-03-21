<?php

namespace Dev\PerfCode\BgWorker;

class WorkerHelper
{
    public static function getWorkerPath(): string
    {
        return MiscHelper::getAppDirPath() . '/worker.php';
    }

    public static function isWorkerRunning(): ?bool
    {
        $phpProcesses = array();
        $checkWorkerResult = exec('ps -C php -f', $phpProcesses);
        if ($checkWorkerResult !== false) {
            $isWorkerRunning = false;
            foreach ($phpProcesses as $phpProcess) {
                if (strpos($phpProcess, self::getWorkerPath()) !== false) {
                    $isWorkerRunning = true;
                    break;
                }
            }

            return $isWorkerRunning;
        } else {
            return null;
        }
    }

    public static function killWorker(): bool
    {
        $killWorkerCommand = 'pkill -f ' . self::getWorkerPath();
        $killWorkerResult = exec($killWorkerCommand);
        if ($killWorkerResult === false) {
            return false;
        } else {
            return true;
        }
    }
}
