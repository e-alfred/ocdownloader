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
	public static function CheckURL ($URL)
	{
		$URLPattern = '%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';

		preg_match ($URLPattern, $URL, $Matches);
		if (count ($Matches) === 1)
		{
			return true;
		}
		return false;
	}

	public static function CheckFilepath ($FP)
	{
		if (\OC\Files\Filesystem::file_exists ($FP))
        {
              return true;
        }
		return false;
	}

	public static function GetProgressString ($Completed, $Total, $Progress)
	{
		$CompletedStr = self::FormatSizeUnits ($Completed);
		$TotalStr = self::FormatSizeUnits ($Total);

		if ($Progress < 1 && $Progress > 0)
		{
			return $CompletedStr . ' / ' . $TotalStr . ' (' . round ((($Completed / $Total) * 100), 2) . '%)';
		}
		elseif ($Progress >= 1)
		{
			return $TotalStr . ' (' . round ((($Completed / $Total) * 100), 2) . '%)';
		}
		return null;
	}

	public static function FormatSizeUnits ($Bytes)
    {
        if ($Bytes >= 1073741824)
        {
            $Bytes = number_format ($Bytes / 1073741824, 2) . ' GB';
        }
        elseif ($Bytes >= 1048576)
        {
            $Bytes = number_format ($Bytes / 1048576, 2) . ' MB';
        }
        elseif ($Bytes >= 1024)
        {
            $Bytes = number_format ($Bytes / 1024, 2) . ' KB';
        }
        else
        {
            $Bytes = $Bytes . ' B';
        }

        return $Bytes;
	}

	public static function CheckBinary ($Binary)
	{
		exec ('which ' . $Binary, $Output, $Return);

		if ($Return == 0)
		{
		    return true;
		}
		return false;
	}

	public static function CleanString ($Text)
	{
	    $UTF8 = Array
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
	        '/ /'           =>   '_', // nonbreaking space (equiv. to 0x160)
	    );
    	return preg_replace (array_keys ($UTF8), array_values ($UTF8), $Text);
	}

	public static function GetDownloadStatusID ($Status)
	{
		switch (strtolower ($Status))
		{
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

	public static function GetCounters ($DbType, $UID)
	{
		$SQL = 'SELECT (SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` < ? AND `UID` = ?) as `ALL`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `COMPLETES`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `ACTIVES`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `WAITINGS`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `STOPPED`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ? AND `UID` = ?) as `REMOVED`';
		if ($DbType == 1)
		{
			$SQL = 'SELECT (SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" < ? AND "UID" = ?) as "ALL",' .
						  '(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "COMPLETES",' .
						  '(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "ACTIVES",' .
						  '(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "WAITINGS",' .
						  '(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "STOPPED",' .
						  '(SELECT COUNT(*) FROM *PREFIX*ocdownloader_queue WHERE "STATUS" = ? AND "UID" = ?) as "REMOVED"';
		}
		$Query = \OCP\DB::prepare ($SQL);
		$Request = $Query->execute (Array (5, $UID, 0, $UID, 1, $UID, 2, $UID, 3, $UID, 4, $UID));

		return $Request->fetchRow ();
	}

	public static function StartsWith ($Haystack, $Needle)
	{
	    return $Needle === "" || strrpos ($Haystack, $Needle, -strlen ($Haystack)) !== FALSE;
	}

	public static function EndsWith ($Haystack, $Needle)
	{
	    return $Needle === "" || (($Temp = strlen ($Haystack) - strlen ($Needle)) >= 0 && strpos ($Haystack, $Needle, $Temp) !== FALSE);
	}

	public static function GetLastVersionNumber ()
	{
		$CH = curl_init ('https://raw.githubusercontent.com/e-alfred/ocdownloader/master/appinfo/version');

		curl_setopt_array ($CH, Array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
	    	CURLOPT_CONNECTTIMEOUT => 10,
	    	CURLOPT_RETURNTRANSFER => true,
	    	CURLOPT_FOLLOWLOCATION => true,
	    	CURLOPT_MAXREDIRS => 10
		));

		$Data = curl_exec ($CH);
		curl_close ($CH);

		return $Data;
	}

	public static function CanCheckForUpdate ()
	{
		// Is the user in the admin group ?
        if (\OC_User::isAdminUser (\OC_User::getUser ()))
        {
			// Is the ocdownloader option to automatically check is enable ?
			$Settings = new Settings ();
			$Settings->SetKey ('CheckForUpdates');
			$CheckForUpdates = $Settings->GetValue ();
			if (strcmp ($CheckForUpdates, 'Y') == 0 || is_null ($CheckForUpdates))
			{
				return true;
			}
        }
		return false;
	}

	public static function ResetAria2 ($DbType)
	{
		$SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue`';
		if ($DbType == 1)
		{
			$SQL = 'SELECT * FROM *PREFIX*ocdownloader_queue';
		}
		$Query = \OCP\DB::prepare ($SQL);
		$Request = $Query->execute ();

		while ($Row = $Request->fetchRow ())
		{
			$Status = Aria2::TellStatus ($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!

			if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'error') != 0 && strcmp ($Status['result']['status'], 'complete') != 0)
			{
				Aria2::Remove ($Row['GID']); //$GID was wrong, but $Row['GID']? untested!!
			}
		}

		$Purge = Aria2::PurgeDownloadResult ();
		if (isset ($Purge['result']) && strcmp ($Purge['result'], 'OK') == 0)
		{
			$SQL = 'TRUNCATE TABLE `*PREFIX*ocdownloader_queue`';
			if ($DbType == 1)
			{
				$SQL = 'TRUNCATE TABLE *PREFIX*ocdownloader_queue';
			}
			$Query = \OCP\DB::prepare ($SQL);
			$Request = $Query->execute ();
		}
	}

	public static function GetMinutes ($Number, $UnitLetter)
	{
		if (strcmp ($UnitLetter, 'i') == 0)
		{
			return $Number;
		}

		$Units = array ('h' => 'hour', 'd' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year');

		$To = strtotime ('+' . $Number . ' ' . $Units[$UnitLetter] . ($Number > 1 ? 's' : ''));
		$From = strtotime ('now');
		return round (abs ($To - $From) / 60,2);
	}
	
	public static function getShortFilename($filename)
	{
		return mb_strlen ($filename, "UTF-8") > 40 ? mb_substr ($filename, 0, 40, "UTF-8") . '...' : $filename;
	}
}