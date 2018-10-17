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



class Settings
{
    private $Key = null;
    private $Table = null;
    private $UID = null;

    public function __construct($Table = 'admin')
    {
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

        if (!is_null($this->UID)) {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->select('VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->andwhere($query->expr()->eq('UID', $query->createNamedParameter($this->UID)))
          ->setMaxResults('1');
          $Request = $qb->execute();

        } else {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->select('VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->setMaxResults('1');
          $Request = $qb->execute();
        }

        if ($Request->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getValue()
    {
        if (!is_null($this->UID)) {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->select('VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->andwhere($query->expr()->eq('UID', $query->createNamedParameter($this->UID)))
          ->setMaxResults('1');
          return $Request = $qb->execute();

        } else {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();

          $qb->select('VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->setMaxResults('1');
          return $Request = $qb->execute();
        }

    }

    public function getAllValues()
    {
        if (!is_null($this->UID)) {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->select('KEY', 'VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->andwhere($query->expr()->eq('UID', $query->createNamedParameter($this->UID)))
          ->setMaxResults('1');
          return $Request = $qb->execute();

        } else {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->select('KEY', 'VAL')->from('ocdownloader_'.$this->Table.'settings')
          ->where($query->expr()->eq('KEY', $query->createNamedParameter($this->Key)))
          ->setMaxResults('1');
          return $Request = $qb->execute();
        }

    }

    public function updateValue($Value)
    {
        if (!is_null($this->UID)) {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->update('ocdownloader_'.$this->Table.'settings')
              ->set('VAL', $qb->createNamedParameter($Value))
              ->where($qb->expr()->eq('KEY', $qb->createNamedParameter($this->Key)))
              ->andwhere($qb->expr()->eq('UID', $qb->createNamedParameter($this->UID)));
          $qb->execute();

        } else {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
          $qb->update('ocdownloader_'.$this->Table.'settings')
              ->set('VAL', $qb->createNamedParameter($Value))
              ->where($qb->expr()->eq('KEY', $qb->createNamedParameter($this->Key)));
          $qb->execute();
        }
    }

    public function insertValue($Value)
    {
        if (!is_null($this->UID)) {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
            $qb->insert('ocdownloader_'.$this->Table.'settings')
                ->values([
                    'KEY' => $qb->createNamedParameter($this->Key),
                    'VAL' => $qb->createNamedParameter($Value),
                    'UID' => $qb->createNamedParameter($this->UID),
                    ]);
            $qb->execute();

        } else {
          $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
            $qb->insert('ocdownloader_'.$this->Table.'settings')
                ->values([
                  'KEY' => $qb->createNamedParameter($this->Key),
                  'VAL' => $qb->createNamedParameter($Value),
                    ]);
            $qb->execute();
        }
    }
}
