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
      
namespace OCA\ocDownloader\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\Config;

class AdminSettingsController extends Controller
{
      private $DbType = 0;
      
      public function __construct ($AppName, IRequest $Request)
      {
            parent::__construct($AppName, $Request);
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
      }
      
      /**
       * @AdminRequired
       * @NoCSRFRequired
       */
      public function save ()
      {
            // Save YTBinary path
            if (isset ($_POST['YTBinary']) && strlen (trim ($_POST['YTBinary'])) > 0)
            {
                  $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_adminsettings` WHERE `KEY` = ? LIMIT 1';
                  if ($this->DbType == 1)
                  {
                        $SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_adminsettings WHERE "KEY" = ? LIMIT 1';
                  }
                  $Query = \OCP\DB::prepare ($SQL);
                  $Result = $Query->execute (Array ('YTDLBinary'));
                  
                  if ($Query->rowCount () == 1)
                  {
                        $SQL = 'UPDATE `*PREFIX*ocdownloader_adminsettings` SET `VAL` = ? WHERE `KEY` = ?';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'UPDATE *PREFIX*ocdownloader_adminsettings SET "VAL" = ? WHERE "KEY" = ?';
                        }
                        
                        $Query = \OCP\DB::prepare ($SQL);
                        $Result = $Query->execute (Array (
                              trim (str_replace (' ', '\ ', $_POST['YTBinary'])),
                              'YTDLBinary'
                        ));
                  }
                  else
                  {
                        $SQL = 'INSERT INTO `*PREFIX*ocdownloader_adminsettings` (`KEY`, `VAL`) VALUES (?, ?)';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'INSERT INTO *PREFIX*ocdownloader_adminsettings ("KEY", "VAL") VALUES (?, ?)';
                        }
                        
                        $Query = \OCP\DB::prepare ($SQL);
                        $Result = $Query->execute (Array (
                              'YTDLBinary',
                              trim (str_replace (' ', '\ ', $_POST['YTBinary']))
                        ));
                  }
            }
      }
}