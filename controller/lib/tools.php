<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

namespace OCA\ocDownloader\Controller\Lib;

class Tools
{
	public static function CheckURL ($URL)
	{
		$URLPattern = '%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';
		
		preg_match ($URLPattern, $URL, $Matches);
		if (count ($Matches) == 1)
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
		$CompletedStr = self::FormatSizeUnits($Completed);
		$TotalStr = self::FormatSizeUnits($Total);
		
		if ($Progress < 1)
		{
			return $CompletedStr . ' / ' . $TotalStr . ' (' . round ((($Completed / $Total) * 100), 2) . '%)';
		}
		else
		{
			return $TotalStr . ' (' . round ((($Completed / $Total) * 100), 2) . '%)';
		}
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
	
	public static function GetCounters ($DbType)
	{
		$SQL = 'SELECT (SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue`) as `ALL`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `COMPLETES`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `ACTIVES`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `WAITINGS`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `STOPPED`,' .
					  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `REMOVED`';
		if ($DbType == 1)
		{
			$SQL = 'SELECT (SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue`) as `ALL`,' .
						  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `COMPLETES`,' .
						  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `ACTIVES`,' .
						  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `WAITINGS`,' .
						  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `STOPPED`,' .
						  '(SELECT COUNT(*) FROM `*PREFIX*ocdownloader_queue` WHERE `STATUS` = ?) as `REMOVED`';
		}
		$Query = \OCP\DB::prepare ($SQL);
		$Request = $Query->execute (Array (0, 1, 2, 3, 4));
		
		return $Request->fetchRow ();
	}
}
?>