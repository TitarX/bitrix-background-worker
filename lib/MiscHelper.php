<?php

namespace Dev\PerfCode\BgWorker;

class MiscHelper
{
    public static function getSiteDirPath(): string
    {
        return realpath(__DIR__ . '/../..');
    }

    public static function getAppDirPath(): string
    {
        return realpath(__DIR__ . '/..');
    }

    public static function getAppDirRelativePath(): string
    {
        $result = str_replace(self::getSiteDirPath(), '', __DIR__);
        $result = str_replace('/lib', '', $result);
        return $result;
    }

    public static function getDataDirPath(): string
    {
        return realpath(__DIR__ . '/../data');
    }

    public static function checkPossibilityWorking(): array
    {
        $resultMessages = array();

        if (!function_exists('exec')) {
            $resultMessages[] = 'Недоступна функция "exec".';
        }

        return $resultMessages;
    }
}
