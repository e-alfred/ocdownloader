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

use \OCP\Config;

class Settings
{
	private $Key = null;
	private $DbType = 0;
	
	public function __construct ()
	{
		if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
        {
              $this->DbType = 1;
        }
	}
	
	public function SetKey ($Key)
	{
		$this->Key = $Key;
	}
	
	public function CheckIfKeyExists ()
	{
		if (is_null ($this->Key))
		{
			return false;
		}
		
		$SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_adminsettings` WHERE `KEY` = ? LIMIT 1';
		if ($this->DbType == 1)
		{
		    $SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_adminsettings WHERE "KEY" = ? LIMIT 1';
		}
		$Query = \OCP\DB::prepare ($SQL);
		$Result = $Query->execute (Array ($this->Key));
		
		if ($Query->rowCount () == 1)
        {
			return true;
		}
		return false;
	}
	
	public function GetValue ()
	{
		$SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_adminsettings` WHERE `KEY` = ? LIMIT 1';
        if ($this->DbType == 1)
        {
			$SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_adminsettings WHERE "KEY" = ? LIMIT 1';
        }
        $Query = \OCP\DB::prepare ($SQL);
        $Result = $Query->execute (Array ($this->Key));
		
		if ($Query->rowCount () == 1)
		{
			return $Result->fetchOne ();
		}
		return null;
	}
	
	public function GetAllValues ()
	{
		$SQL = 'SELECT `KEY`, `VAL` FROM `*PREFIX*ocdownloader_adminsettings`';
		if ($this->DbType == 1)
		{
		    $SQL = 'SELECT "KEY", "VAL" FROM *PREFIX*ocdownloader_adminsettings';
		}
		$Query = \OCP\DB::prepare ($SQL);
		return $Query->execute ();
	}
	
	public function UpdateValue ($Value)
	{
		$SQL = 'UPDATE `*PREFIX*ocdownloader_adminsettings` SET `VAL` = ? WHERE `KEY` = ?';
		if ($this->DbType == 1)
		{
			$SQL = 'UPDATE *PREFIX*ocdownloader_adminsettings SET "VAL" = ? WHERE "KEY" = ?';
		}
		
		$Query = \OCP\DB::prepare ($SQL);
		$Result = $Query->execute (Array (
			$Value,
			$this->Key
		));
	}
	
	public function InsertValue ($Value)
	{
		$SQL = 'INSERT INTO `*PREFIX*ocdownloader_adminsettings` (`KEY`, `VAL`) VALUES (?, ?)';
		if ($this->DbType == 1)
		{
			$SQL = 'INSERT INTO *PREFIX*ocdownloader_adminsettings ("KEY", "VAL") VALUES (?, ?)';
		}
		
		$Query = \OCP\DB::prepare ($SQL);
		$Result = $Query->execute (Array (
			$this->Key,
			$Value
		));
	}
}