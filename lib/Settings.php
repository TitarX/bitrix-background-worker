<?php

namespace Dev\PerfCode\BgWorker;

class Settings
{
    private static $timeout = 300;
    private static $timezone = 'Europe/Moscow';
    private static $pieceCount = 300;

    public static function getTimeout(): int
    {
        return self::$timeout;
    }

    public static function getTimezone(): string
    {
        return self::$timezone;
    }

    public static function getPieceCount(): int
    {
        return self::$pieceCount;
    }
}
