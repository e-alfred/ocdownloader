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

use \OCP\Config;

class Settings
{
    private $Key = null;
    private $DbType = 0;
    private $Table = null;
    private $UID = null;
    
    public function __construct($Table = 'admin')
    {
        if (strcmp(Config::getSystemValue('dbtype'), 'pgsql') == 0) {
              $this->DbType = 1;
        }
        
        $this->Table = $Table;
    }
    
    public function setKey($Key)
    {
        $this->Key = $Key;
    }
    
    public function setUID($UID)
    {
        $this->UID = $UID;
    }
    
    public function setTable($Table)
    {
        $this->Table = $Table;
    }
    
    public function checkIfKeyExists()
    {
        if (is_null($this->Key)) {
            return false;
        }
        
        $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_'.$this->Table.'settings` WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '') . ' LIMIT 1';
        if ($this->DbType == 1) {
            $SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_'.$this->Table.'settings WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '').' LIMIT 1';
        }
        $Query = \OCP\DB::prepare($SQL);
        if (!is_null($this->UID)) {
            $Query->execute(array($this->Key, $this->UID));
        } else {
            $Query->execute(array($this->Key));
        }
        
        if ($Query->rowCount() == 1) {
            return true;
        }
        return false;
    }
    
    public function getValue()
    {
        $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_'.$this->Table.'settings` WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '').' LIMIT 1';
        if ($this->DbType == 1) {
            $SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_'.$this->Table.'settings WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '').' LIMIT 1';
        }
        $Query = \OCP\DB::prepare($SQL);
        
        if (!is_null($this->UID)) {
            $Result = $Query->execute(array($this->Key, $this->UID));
        } else {
            $Result = $Query->execute(array($this->Key));
        }
        
        if ($Query->rowCount() == 1) {
            return $Result->fetchOne();
        }
        return null;
    }
    
    public function getAllValues()
    {
        $SQL = 'SELECT `KEY`, `VAL` FROM `*PREFIX*ocdownloader_'.$this->Table.'settings`'
            .(!is_null($this->UID) ? ' WHERE `UID` = ?' : '');
        if ($this->DbType == 1) {
            $SQL = 'SELECT "KEY", "VAL" FROM *PREFIX*ocdownloader_'.$this->Table.'settings'
                .(!is_null($this->UID) ? ' WHERE "UID" = ?' : '');
        }
        $Query = \OCP\DB::prepare($SQL);
        
        if (!is_null($this->UID)) {
            return $Query->execute(array($this->UID));
        } else {
            return $Query->execute();
        }
    }
    
    public function updateValue($Value)
    {
        $SQL = 'UPDATE `*PREFIX*ocdownloader_' . $this->Table . 'settings` SET `VAL` = ? WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '');
        if ($this->DbType == 1) {
            $SQL = 'UPDATE *PREFIX*ocdownloader_' . $this->Table . 'settings SET "VAL" = ? WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '');
        }
        $Query = \OCP\DB::prepare($SQL);
        
        if (!is_null($this->UID)) {
            $Query->execute(array($Value, $this->Key, $this->UID));
        } else {
            $Query->execute(array($Value, $this->Key));
        }
    }
    
    public function insertValue($Value)
    {
        $SQL = 'INSERT INTO `*PREFIX*ocdownloader_'.$this->Table.'settings`(`KEY`, `VAL`'
            .(!is_null($this->UID) ? ', `UID`' : '') . ') VALUES(?, ?' .(!is_null($this->UID) ? ', ?' : '').')';
        if ($this->DbType == 1) {
            $SQL = 'INSERT INTO *PREFIX*ocdownloader_'.$this->Table.'settings("KEY", "VAL"'
                .(!is_null($this->UID) ? ', "UID"' : '') . ') VALUES(?, ?' .(!is_null($this->UID) ? ', ?' : '').')';
        }
        $Query = \OCP\DB::prepare($SQL);
        
        if (!is_null($this->UID)) {
            $Query->execute(array($this->Key, $Value, $this->UID));
        } else {
            $Query->execute(array($this->Key, $Value));
        }
    }
}
