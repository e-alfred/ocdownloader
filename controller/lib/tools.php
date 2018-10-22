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

use OCA\ocDownloader\Controller\Lib\Aria2;

class Tools
{
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

    public static function checkBinary($Binary)
    {
        exec('which ' . $Binary, $Output, $Return);

        if ($Return == 0) {
            return true;
        }
        return false;
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

    public static function getCounters($UID)
    {
      $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
      $qb->select('status')
      ->selectAlias($qb->func()->count('*'), 'counter')
      ->from('ocdownloader_queue')
      ->where($qb->expr()->eq('uid', $qb->createNamedParameter($UID)))
      ->groupBy('status');
      $result = $qb->execute();

      $downloads = [
            'ALL' => 0,
            'COMPLETES' => 0,
            'ACTIVES' => 0,
            'WAITINGS' => 0,
            'STOPPED' => 0,
            'REMOVED' => 0,
      ];

        while ($row = $result->fetch()) {
            if ($row['status'] == 0) {
                 $downloads['ALL'] += $row['counter'];
                 $downloads['COMPLETES'] = $row['counter'];
            } else if ($row['status'] == 1) {
                 $downloads['ALL'] += $row['counter'];
                 $downloads['ACTIVES'] = $row['counter'];
            } else if ($row['status'] == 2) {
                 $downloads['ALL'] += $row['counter'];
                 $downloads['WAITINGS'] = $row['counter'];
            } else if ($row['status'] == 3) {
                 $downloads['ALL'] += $row['counter'];
                 $downloads['STOPPED'] = $row['counter'];
            } else if ($row['status'] == 4) {
                 $downloads['ALL'] += $row['counter'];
                 $downloads['REMOVED'] = $row['counter'];
            }
        }
        $result->closeCursor();

        return $row;
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

    public static function getLastVersionNumber()
    {
        $CH = curl_init('https://raw.githubusercontent.com/e-alfred/ocdownloader/master/appinfo/version');

        curl_setopt_array($CH, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10
        ));

        $Data = curl_exec($CH);
        curl_close($CH);

        return $Data;
    }

    public static function canCheckForUpdate()
    {
        // Is the user in the admin group ?
        if (\OC_User::isAdminUser(\OC_User::getUser())) {
            // Is the ocdownloader option to automatically check is enable ?
            $Settings = new Settings();
            $Settings->setKey('CheckForUpdates');
            $CheckForUpdates = $Settings->getValue();
            if (strcmp($CheckForUpdates, 'Y') == 0 || is_null($CheckForUpdates)) {
                return true;
            }
        }
        return false;
    }

    public static function resetAria2()
    {
        $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
        $qb->select('*')->from('ocdownloader_queue');
        $Request = $qb->execute();

        while ($Row = $Request->fetch()) {
            $Status = Aria2::tellStatus($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!

            if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                && strcmp($Status['result']['status'], 'complete') != 0) {
                Aria2::remove($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!
            }
        }

        $Purge = Aria2::purgeDownloadResult();
        if (isset($Purge['result']) && strcmp($Purge['result'], 'OK') == 0) {
            $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
            $qb->delete('ocdownloader_queue');
            $Request = $qb->execute();
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
