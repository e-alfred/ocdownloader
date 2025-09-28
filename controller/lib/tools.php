<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

namespace OCA\ocDownloader\Controller\Lib;

class Tools
{
    public static function sanitiseFileName($filename)
    {
        return str_replace(['../', '..\\', "..$DIRECTORY_SEPARATOR"], '', $filename);
    }

    public static function checkURL($URL)
    {
        $URLPattern = '%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}'
            .']+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.'
            .'[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';

        preg_match($URLPattern, $URL, $Matches);
        if (count($Matches) === 1) {
            return true;
        }
        return false;
    }

    public static function isMagnet($URL)
    {
        $magnetPattern = '%magnet:\?xt=urn:[a-z0-9]+:[a-z0-9]{32}%i';
        preg_match($magnetPattern, $URL, $Matches);
        if (count($Matches) === 1) {
            return true;
        }
        return false;
    }

    public static function checkFilepath($FP)
    {
        if (\OC\Files\Filesystem::file_exists($FP)) {
            return true;
        }
        return false;
    }

    public static function getProgressString($Completed, $Total, $Progress)
    {
        $CompletedStr = self::formatSizeUnits($Completed);
        $TotalStr = self::formatSizeUnits($Total);

        if ($Progress < 1 && $Progress > 0) {
            return $CompletedStr . ' / ' . $TotalStr . '(' . round((($Completed / $Total) * 100), 2) . '%)';
        } elseif ($Progress >= 1) {
            return $TotalStr . '(' . round((($Completed / $Total) * 100), 2) . '%)';
        }
        return null;
    }

    public static function formatSizeUnits($Bytes)
    {
        if ($Bytes >= 1073741824) {
            $Bytes = number_format($Bytes / 1073741824, 2) . ' GB';
        } elseif ($Bytes >= 1048576) {
            $Bytes = number_format($Bytes / 1048576, 2) . ' MB';
        } elseif ($Bytes >= 1024) {
            $Bytes = number_format($Bytes / 1024, 2) . ' KB';
        } else {
            $Bytes = $Bytes . ' B';
        }

        return $Bytes;
    }

    public static function cleanString($Text)
    {
        $UTF8 = array
        (
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   '', // Literally a single quote
            '/[“”«»„]/u'    =>   '', // Double quote
            '/ /'           =>   '_', // nonbreaking space(equiv. to 0x160)
        );
        return preg_replace(array_keys($UTF8), array_values($UTF8), $Text);
    }

    public static function getDownloadStatusID($Status)
    {
        switch (strtolower($Status)) {
            case 'complete':
                $DLStatus = 0;
                break;
            case 'active':
                $DLStatus = 1;
                break;
            case 'waiting':
                $DLStatus = 2;
                break;
            case 'paused':
                $DLStatus = 3;
                break;
            case 'removed':
                $DLStatus = 4;
                break;
            default:
                $DLStatus = 5;
                break;
        }
        return $DLStatus;
    }

    public static function getCounters($DbType, $UID)
    {
        $SQL = 'SELECT(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` < ? AND `UID` = ?) as `ALL`,'
            .'(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `COMPLETES`,'
            .'(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `ACTIVES`,'
            .'(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `WAITINGS`,'
            .'(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `STOPPED`,'
            .'(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `REMOVED`';
        if ($DbType == 1) {
            $SQL = 'SELECT(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" < ? AND "UID" = ?) as "ALL",'
                .'(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "COMPLETES",'
                .'(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "ACTIVES",'
                .'(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "WAITINGS",'
                .'(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "STOPPED",'
                .'(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "REMOVED"';
        }
        $Query = \OC_DB::prepare($SQL);
        $Request = $Query->execute(array(5, $UID, 0, $UID, 1, $UID, 2, $UID, 3, $UID, 4, $UID));

        return $Request->fetchRow();
    }

    public static function startsWith($Haystack, $Needle)
    {
        return $Needle === "" || strrpos($Haystack, $Needle, -strlen($Haystack)) !== false;
    }

    public static function endsWith($Haystack, $Needle)
    {
        return $Needle === "" ||(($Temp = strlen($Haystack) - strlen($Needle)) >= 0
                && strpos($Haystack, $Needle, $Temp) !== false);
    }

    public static function resetAria2($DbType)
    {
        $SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue`';
        if ($DbType == 1) {
            $SQL = 'SELECT * FROM *PREFIX*ocdownloader_queue';
        }
        $Query = \OC_DB::prepare($SQL);
        $Request = $Query->execute();

        while ($Row = $Request->fetchRow()) {
            $Status = Aria2::tellStatus($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!

            if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                && strcmp($Status['result']['status'], 'complete') != 0) {
                Aria2::remove($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!
            }
        }

        $Purge = Aria2::purgeDownloadResult();
        if (isset($Purge['result']) && strcmp($Purge['result'], 'OK') == 0) {
            $SQL = 'TRUNCATE TABLE `*PREFIX*ocdownloader_queue`';
            if ($DbType == 1) {
                $SQL = 'TRUNCATE TABLE *PREFIX*ocdownloader_queue';
            }
            $Query = \OC_DB::prepare($SQL);
            $Request = $Query->execute();
        }
    }

    public static function getMinutes($Number, $UnitLetter)
    {
        if (strcmp($UnitLetter, 'i') == 0) {
            return $Number;
        }

        $Units = array('h' => 'hour', 'd' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year');

        $To = strtotime('+' . $Number . ' ' . $Units[$UnitLetter] .($Number > 1 ? 's' : ''));
        $From = strtotime('now');
        return round(abs($To - $From) / 60, 2);
    }

    public static function getShortFilename($filename)
    {
        return mb_strlen($filename, "UTF-8") > 40 ? mb_substr($filename, 0, 40, "UTF-8") . '...' : $filename;
    }

}
