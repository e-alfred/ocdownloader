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
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\Config;
use \OCP\IL10N;

use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Tools;

class QueueController extends Controller
{
      private $UserStorage;
      private $DbType;
      private $CurrentUID;
      
      public function __construct ($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
      {
            parent::__construct($AppName, $Request);
            
            $this->DbType = 0;
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $this->CurrentUID = $CurrentUID;
            
            $this->L10N = $L10N;
      }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function get ()
      {
            try
            {
                  if (isset ($_POST['VIEW']) && strlen (trim ($_POST['VIEW'])) > 0)
                  {
                        $Params = Array ($this->CurrentUID);
                        switch ($_POST['VIEW'])
                        {
                              case 'completes':
                                    $StatusReq = '(?)';
                                    $Params[] = 0;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              case 'removed':
                                    $StatusReq = '(?)';
                                    $Params[] = 4;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              case 'actives':
                                    $StatusReq = '(?)';
                                    $Params[] = 1;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              case 'stopped':
                                    $StatusReq = '(?)';
                                    $Params[] = 3;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              case 'waitings':
                                    $StatusReq = '(?)';
                                    $Params[] = 2;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              case 'all':
                                    $StatusReq = '(?, ?, ?, ?, ?)';
                                    $Params[] = 0; $Params[] = 1; $Params[] = 2; $Params[] = 3; $Params[] = 4;
                                    $IsCleanedReq = '(?, ?)';
                                    $Params[] = 0; $Params[] = 1;
                              break;
                              default: // add view 
                                    $StatusReq = '(?, ?, ?, ?)';
                                    $Params[] = 0; $Params[] = 1; $Params[] = 2; $Params[] = 3; // STATUS
                                    $IsCleanedReq = '(?)';
                                    $Params[] = 0; // IS_CLEANED
                              break;
                        }
                        
                        $SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue` WHERE `UID` = ? AND `STATUS` IN ' . $StatusReq . ' AND `IS_CLEANED` IN ' . $IsCleanedReq . ' ORDER BY `TIMESTAMP` ASC';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'SELECT * FROM *PREFIX*ocdownloader_queue WHERE "UID" = ? AND "STATUS" IN ' . $StatusReq . ' AND "IS_CLEANED" IN ' . $IsCleanedReq . ' ORDER BY "TIMESTAMP" ASC';
                        }
                        $Query = \OCP\DB::prepare ($SQL);
                        $Request = $Query->execute ($Params);
                        
                        $Queue = [];
                        
                        while ($Row = $Request->fetchRow ())
                        {
                              $Aria2 = new Aria2();
                              $Status = $Aria2->tellStatus ($Row['GID']);
                              $DLStatus = 5; // Error
                              
                              if (!is_null ($Status))
                              {
                                    if (!isset ($Status['error']))
                                    {
                                          $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                                          
                                          $DLStatus = Tools::GetDownloadStatusID ($Status['result']['status']);
                                          
                                          $Queue[] = Array (
                                                'GID' => $Row['GID'],
                                                'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                                                'PROGRESS' => Tools::GetProgressString ($Status['result']['completedLength'], $Status['result']['totalLength'], $Progress) . (isset ($Status['result']['bittorrent']) && $Progress < 1 ? ' - <strong>' . $this->L10N->t ('Seeders') . '</strong>: ' . $Status['result']['numSeeders'] : (isset ($Status['result']['bittorrent']) && $Progress == 1 ? ' - <strong>' . $this->L10N->t ('Uploaded') . '</strong>: ' . Tools::FormatSizeUnits ($Status['result']['uploadLength']) . ' - <strong>' . $this->L10N->t ('Ratio') . '</strong>: ' . round (($Status['result']['uploadLength'] / $Status['result']['completedLength']), 2) : '')),
                                                'STATUS' => isset ($Status['result']['status']) ? $this->L10N->t ($Row['STATUS'] == 4 ? 'Removed' : ucfirst ($Status['result']['status'])) . (isset ($Status['result']['bittorrent']) && $Progress == 1 && $DLStatus != 3 ? ' - ' . $this->L10N->t ('Seeding') : '') : (string)$this->L10N->t ('N/A'),
                                                'STATUSID' => $Row['STATUS'] == 4 ? 4 : $DLStatus,
                                                'SPEED' => isset ($Status['result']['downloadSpeed']) ? ($Status['result']['downloadSpeed'] == 0 ? (isset ($Status['result']['bittorrent']) && $Progress == 1 ? ($Status['result']['uploadSpeed'] == 0 ? '--' : Tools::FormatSizeUnits ($Status['result']['uploadSpeed']) . '/s') : '--') : Tools::FormatSizeUnits ($Status['result']['downloadSpeed']) . '/s') : (string)$this->L10N->t ('N/A'),
                                                'FILENAME' => (strlen ($Row['FILENAME']) > 40 ? substr ($Row['FILENAME'], 0, 40) . '...' : $Row['FILENAME']),
                                                'PROTO' => $Row['PROTOCOL'],
                                                'ISTORRENT' => isset ($Status['result']['bittorrent'])
                                          );
                                          
                                          if ($Row['STATUS'] != $DLStatus)
                                          {
                                                $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ? WHERE `UID` = ? AND `GID` = ? AND `STATUS` != ?';
                                                if ($this->DbType == 1)
                                                {
                                                      $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "UID" = ? AND "GID" = ? AND "STATUS" != ?';
                                                }
                                                
                                                $Query = \OCP\DB::prepare ($SQL);
                                                $Result = $Query->execute (Array (
                                                      $DLStatus,
                                                      $this->CurrentUID,
                                                      $Row['GID'],
                                                      4
                                                ));
                                          }
                                    }
                                    else
                                    {
                                          $Queue[] = Array (
                                                'GID' => $Row['GID'],
                                                'PROGRESSVAL' => 0,
                                                'PROGRESS' => (string)$this->L10N->t ('Error, GID not found !'),
                                                'STATUS' => (string)$this->L10N->t ('N/A'),
                                                'STATUSID' => $DLStatus,
                                                'SPEED' => (string)$this->L10N->t ('N/A'),
                                                'FILENAME' => (strlen ($Row['FILENAME']) > 40 ? substr ($Row['FILENAME'], 0, 40) . '...' : $Row['FILENAME']),
                                                'PROTO' => $Row['PROTOCOL'],
                                                'ISTORRENT' => isset ($Status['result']['bittorrent'])
                                          );
                                    }
                              }
                              else
                              {
                                    $Queue[] = Array (
                                          'GID' => $Row['GID'],
                                          'PROGRESSVAL' => 0,
                                          'PROGRESS' => (string)$this->L10N->t ('Returned status is null ! Is Aria2c running as a daemon ?'),
                                          'STATUS' => (string)$this->L10N->t ('N/A'),
                                          'STATUSID' => $DLStatus,
                                          'SPEED' => (string)$this->L10N->t ('N/A'),
                                          'FILENAME' => (strlen ($Row['FILENAME']) > 40 ? substr ($Row['FILENAME'], 0, 40) . '...' : $Row['FILENAME']),
                                          'PROTO' => $Row['PROTOCOL'],
                                          'ISTORRENT' => isset ($Status['result']['bittorrent'])
                                    );
                              }
                        }
                        die (json_encode (Array ('ERROR' => false, 'QUEUE' => $Queue, 'COUNTER' => Tools::GetCounters ($this->DbType))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function count ()
      {
            try
            {
                  die (json_encode (Array ('ERROR' => false, 'COUNTER' => Tools::GetCounters ($this->DbType))));
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function pause ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        $Aria2 = new Aria2();
                        $Status = $Aria2->tellStatus ($_POST['GID']);
                        
                        $Pause['result'] = $_POST['GID'];
                        if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'error') != 0 && strcmp ($Status['result']['status'], 'complete') != 0  && strcmp ($Status['result']['status'], 'active') == 0)
                        {
                              $Pause = $Aria2->pause ($_POST['GID']);
                        }
                        
                        if (strcmp ($Pause['result'], $_POST['GID']) == 0)
                        {
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ? WHERE `UID` = ? AND `GID` = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "UID" = ? AND "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    3,
                                    $this->CurrentUID,
                                    $_POST['GID']
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been paused'))));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('An error occured while pausing the download'))));
                        }
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function unpause ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        $Aria2 = new Aria2();
                        $Status = $Aria2->tellStatus ($_POST['GID']);
                        
                        $UnPause['result'] = $_POST['GID'];
                        if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'error') != 0 && strcmp ($Status['result']['status'], 'complete') != 0  && strcmp ($Status['result']['status'], 'paused') == 0)
                        {
                              $UnPause = $Aria2->unpause ($_POST['GID']);
                        }
                        
                        if (strcmp ($UnPause['result'], $_POST['GID']) == 0)
                        {
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ? WHERE `UID` = ? AND `GID` = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "UID" = ? AND "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    1,
                                    $this->CurrentUID,
                                    $_POST['GID']
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been unpaused'))));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('An error occured while unpausing the download'))));
                        }
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function hide ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `IS_CLEANED` = ? WHERE `UID` = ? AND `GID` = ?';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "IS_CLEANED" = ? WHERE "UID" = ? AND "GID" = ?';
                        }
      
                        $Query = \OCP\DB::prepare ($SQL);
                        $Result = $Query->execute (Array (
                              1,
                              $this->CurrentUID,
                              $_POST['GID']
                        ));
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been cleaned'))));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function hideall ()
      {
            try
            {
                  if (isset ($_POST['GIDS']) && count ($_POST['GIDS']) > 0)
                  {
                        $Queue = Array ();
                        
                        foreach ($_POST['GIDS'] as $GID)
                        {
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `IS_CLEANED` = ? WHERE `UID` = ? AND `GID` = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "IS_CLEANED" = ? WHERE "UID" = ? AND "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    1,
                                    $this->CurrentUID,
                                    $GID
                              ));
                              
                              $Queue[] = Array (
                                    'GID' => $GID
                              );
                        }
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('All downloads have been cleaned'), 'QUEUE' => $Queue)));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('No GIDS in the download queue'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function remove ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        $Aria2 = new Aria2();
                        $Status = $Aria2->tellStatus ($_POST['GID']);
                        
                        $Remove['result'] = $_POST['GID'];
                        if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'error') != 0 && strcmp ($Status['result']['status'], 'complete') != 0)
                        {
                              $Remove = $Aria2->remove ($_POST['GID']);
                        }
                        
                        if (strcmp ($Remove['result'], $_POST['GID']) == 0)
                        {
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ?, `IS_CLEANED` = ? WHERE `UID` = ? AND `GID` = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ?, "IS_CLEANED" = ? WHERE "UID" = ? AND "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    4, 1,
                                    $this->CurrentUID,
                                    $_POST['GID']
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been removed'))));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('An error occured while removing the download'))));
                        }
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function removeall ()
      {
            try
            {
                  if (isset ($_POST['GIDS']) && count ($_POST['GIDS']) > 0)
                  {
                        $GIDS = Array ();
                        
                        foreach ($_POST['GIDS'] as $GID)
                        {
                              $Aria2 = new Aria2();
                              $Status = $Aria2->tellStatus ($GID);
                              
                              $Remove['result'] = $GID;
                              if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'error') != 0 && strcmp ($Status['result']['status'], 'complete') != 0)
                              {
                                    $Remove = $Aria2->remove ($GID);
                              }
                              
                              if (strcmp ($Remove['result'], $GID) == 0)
                              {
                                    $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ?, `IS_CLEANED` = ? WHERE `UID` = ? AND `GID` = ?';
                                    if ($this->DbType == 1)
                                    {
                                          $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ?, "IS_CLEANED" = ? WHERE "UID" = ? AND "GID" = ?';
                                    }
                  
                                    $Query = \OCP\DB::prepare ($SQL);
                                    $Result = $Query->execute (Array (
                                          4, 1,
                                          $this->CurrentUID,
                                          $GID
                                    ));
                              }
                              
                              $GIDS[] = $GID;
                        }
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('All downloads have been removed'), 'GIDS' => $GIDS)));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('No GIDS in the download queue'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function completelyremove ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        $Aria2 = new Aria2();
                        $Status = $Aria2->tellStatus ($_POST['GID']);
                        
                        if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'removed') == 0)
                        {
                              $Remove = $Aria2->removeDownloadResult ($_POST['GID']);
                        }
                        
                        $SQL = 'DELETE FROM `*PREFIX*ocdownloader_queue` WHERE `UID` = ? AND `GID` = ?';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'DELETE FROM *PREFIX*ocdownloader_queue WHERE "UID" = ? AND "GID" = ?';
                        }
      
                        $Query = \OCP\DB::prepare ($SQL);
                        $Result = $Query->execute (Array (
                              $this->CurrentUID,
                              $_POST['GID']
                        ));
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been totally removed'))));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function completelyremoveall ()
      {
            try
            {
                  if (isset ($_POST['GIDS']) && count ($_POST['GIDS']) > 0)
                  {
                        $GIDS = Array ();
                        
                        foreach ($_POST['GIDS'] as $GID)
                        {
                              $Aria2 = new Aria2();
                              $Status = $Aria2->tellStatus ($GID);
                              
                              if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'removed') == 0)
                              {
                                    $Remove = $Aria2->removeDownloadResult ($GID);
                              }
                              
                              $SQL = 'DELETE FROM `*PREFIX*ocdownloader_queue` WHERE `UID` = ? AND `GID` = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'DELETE FROM *PREFIX*ocdownloader_queue WHERE "UID" = ? AND "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $this->CurrentUID,
                                    $GID
                              ));
                              
                              $GIDS[] = $GID;
                        }
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => (string)$this->L10N->t ('The download has been totally removed'), 'GIDS' => $GIDS)));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Bad GID'))));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
}
?>