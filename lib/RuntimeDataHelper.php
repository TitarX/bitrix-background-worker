<?php

namespace Dev\PerfCode\BgWorker;

use DomDocument;
use DOMXPath;

class RuntimeDataHelper
{
    private static $pieceQueryString = '//data/piece';
    private static $recallTimestampQueryString = '//data/recalltimestamp';
    private static $productIblockIdQueryString = '//data/productiblockid';
    private static $brandIblockIdQueryString = '//data/brandiblockid';
    private static $lastProductIdQueryString = '//data/lastproductid';
    private static $totalProductsCountQueryString = '//data/totalproductscount';
    private static $doneProductCountQueryString = '//data/doneproductcount';
    private static $productIblockNameQueryString = '//data/productiblockname';
    private static $brandIblockNameQueryString = '//data/brandiblockname';

    public static function getPieceQueryString()
    {
        return self::$pieceQueryString;
    }

    public static function getRecallTimestampQueryString()
    {
        return self::$recallTimestampQueryString;
    }

    public static function getProductIblockIdQueryString()
    {
        return self::$productIblockIdQueryString;
    }

    public static function getBrandIblockIdQueryString()
    {
        return self::$brandIblockIdQueryString;
    }

    public static function getLastProductIdQueryString()
    {
        return self::$lastProductIdQueryString;
    }

    public static function getTotalProductsCountQueryString()
    {
        return self::$totalProductsCountQueryString;
    }

    public static function getDoneProductCountQueryString()
    {
        return self::$doneProductCountQueryString;
    }

    public static function getProductIblockNameQueryString()
    {
        return self::$productIblockNameQueryString;
    }

    public static function getBrandIblockNameQueryString()
    {
        return self::$brandIblockNameQueryString;
    }

    private static function getDataFilePath(): string
    {
        return MiscHelper::getDataDirPath() . '/RuntimeData.xml';
    }

    public static function createDataFile(): ?int
    {
        if (self::isDataFileExists()) {
            return 0;
        }

        $xmlDocument = new DomDocument();
        $xmlDocument->version = '1.0';
        $xmlDocument->encoding = 'UTF-8';
        $xmlDocument->standalone = true;
        $xmlDocument->formatOutput = true;

        $dataElement = $xmlDocument->createElement('data');
        $xmlDocument->appendChild($dataElement);

        $pieceElement = $xmlDocument->createElement('piece', 0);
        $dataElement->appendChild($pieceElement);
        $runtimestampElement = $xmlDocument->createElement('recalltimestamp', time());
        $dataElement->appendChild($runtimestampElement);
        $productiblockidElement = $xmlDocument->createElement('productiblockid', 0);
        $dataElement->appendChild($productiblockidElement);
        $brandiblockidElement = $xmlDocument->createElement('brandiblockid', 0);
        $dataElement->appendChild($brandiblockidElement);
        $lastproductidElement = $xmlDocument->createElement('lastproductid', 0);
        $dataElement->appendChild($lastproductidElement);
        $totalproductscountElement = $xmlDocument->createElement('totalproductscount', 0);
        $dataElement->appendChild($totalproductscountElement);
        $doneproductcountElement = $xmlDocument->createElement('doneproductcount', 0);
        $dataElement->appendChild($doneproductcountElement);
        $productiblocknameElement = $xmlDocument->createElement('productiblockname', 'Name');
        $dataElement->appendChild($productiblocknameElement);
        $brandiblocknameElement = $xmlDocument->createElement('brandiblockname', 'Name');
        $dataElement->appendChild($brandiblocknameElement);

        $dataFilePath = RuntimeDataHelper::getDataFilePath();
        return $xmlDocument->save($dataFilePath);
    }

    public static function updateSingleValue($query, $value): ?int
    {
        $xmlDocument = new DomDocument();
        $xmlDocument->load(self::getDataFilePath());
        $xPath = new DOMXPath($xmlDocument);
        $result = $xPath->query($query);
        if (!empty($result[0])) {
            $result[0]->nodeValue = $value;
            return $xmlDocument->save(self::getDataFilePath());
        } else {
            return null;
        }
    }

    public static function readSingleValue($query): ?string
    {
        $xmlDocument = new DomDocument();
        $xmlDocument->load(self::getDataFilePath());
        $xPath = new DOMXPath($xmlDocument);
        $result = $xPath->query($query);
        if (!empty($result[0])) {
            return $result[0]->nodeValue;
        } else {
            return null;
        }
    }

    public static function deleteDataFile(): bool
    {
        if (self::isDataFileExists()) {
            $dataFilePath = RuntimeDataHelper::getDataFilePath();
            return unlink($dataFilePath);
        } else {
            return true;
        }
    }

    public static function isDataFileExists(): bool
    {
        $dataFilePath = RuntimeDataHelper::getDataFilePath();
        return file_exists($dataFilePath);
    }

    public static function isTimeExeeded(): ?bool
    {
        $recallTimestamp = self::readSingleValue(self::getRecallTimestampQueryString());
        if (empty($recallTimestamp)) {
            self::deleteDataFile();
            return null;
        }

        $timeout = Settings::getTimeout();
        $recallTimestamp = intval($recallTimestamp);
        $timeLimit = $recallTimestamp + $timeout;
        if ($timeLimit < time()) {
            return true;
        } else {
            return false;
        }
    }
}
