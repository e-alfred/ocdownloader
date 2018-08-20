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

use OCP\IDBConnection;

class Settings
{
    private $Key = null;
    private $DbType = 0;
    private $Table = null;
    private $UID = null;
    private $dbconnection = null;

    public function __construct($Table = 'admin')
    {
        if (strcmp(\OC::$server->getConfig()->getSystemValue('dbtype'), 'pgsql') == 0) {
              $this->DbType = 1;
        }

        $this->Table = $Table;

        $this->dbconnection = \OC::$server->getDatabaseConnection();
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

        $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdl_'.$this->Table.'settings` WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '') . ' LIMIT 1';
        if ($this->DbType == 1) {
            $SQL = 'SELECT "VAL" FROM *PREFIX*ocdl_'.$this->Table.'settings WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '').' LIMIT 1';
        }
        if (!is_null($this->UID)) {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key, $this->UID));
        } else {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key));
        }

        if ($Query->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getValue()
    {
        $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdl_'.$this->Table.'settings` WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '').' LIMIT 1';
        if ($this->DbType == 1) {
            $SQL = 'SELECT "VAL" FROM *PREFIX*ocdl_'.$this->Table.'settings WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '').' LIMIT 1';
        }

        if (!is_null($this->UID)) {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key, $this->UID));
        } else {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key));
        }

        if ($Query->rowCount() == 1) {
            return $Query->fetchOne();
        }
        return null;
    }

    public function getAllValues()
    {
        $SQL = 'SELECT `KEY`, `VAL` FROM `*PREFIX*ocdl_'.$this->Table.'settings`'
            .(!is_null($this->UID) ? ' WHERE `UID` = ?' : '');
        if ($this->DbType == 1) {
            $SQL = 'SELECT "KEY", "VAL" FROM *PREFIX*ocdl_'.$this->Table.'settings'
                .(!is_null($this->UID) ? ' WHERE "UID" = ?' : '');
        }

        if (!is_null($this->UID)) {
            return $Query = $this->dbconnection->executequery($SQL, array($this->UID));
        } else {
            return $Query = $this->dbconnection->executequery($SQL);
        }
    }

    public function updateValue($Value)
    {
        $SQL = 'UPDATE `*PREFIX*ocdl_' . $this->Table . 'settings` SET `VAL` = ? WHERE `KEY` = ?'
            .(!is_null($this->UID) ? ' AND `UID` = ?' : '');
        if ($this->DbType == 1) {
            $SQL = 'UPDATE *PREFIX*ocdl_' . $this->Table . 'settings SET "VAL" = ? WHERE "KEY" = ?'
                .(!is_null($this->UID) ? ' AND "UID" = ?' : '');
        }

        if (!is_null($this->UID)) {
            $Query = $this->dbconnection->executequery($SQL, array($Value, $this->Key, $this->UID));
        } else {
            $Query = $this->dbconnection->executequery($SQL, array($Value, $this->Key));
        }
    }

    public function insertValue($Value)
    {
        $SQL = 'INSERT INTO `*PREFIX*ocdl_'.$this->Table.'settings`(`KEY`, `VAL`'
            .(!is_null($this->UID) ? ', `UID`' : '') . ') VALUES(?, ?' .(!is_null($this->UID) ? ', ?' : '').')';
        if ($this->DbType == 1) {
            $SQL = 'INSERT INTO *PREFIX*ocdl_'.$this->Table.'settings("KEY", "VAL"'
                .(!is_null($this->UID) ? ', "UID"' : '') . ') VALUES(?, ?' .(!is_null($this->UID) ? ', ?' : '').')';
        }

        if (!is_null($this->UID)) {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key, $Value, $this->UID));
        } else {
            $Query = $this->dbconnection->executequery($SQL, array($this->Key, $Value));
        }
    }
}
