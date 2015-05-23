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

use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Tools;

class DownloaderQueueController extends Controller
{
      private $UserStorage;
      private $DbType;
      
      public function __construct ($AppName, IRequest $Request)
      {
            parent::__construct($AppName, $Request);
            
            $this->DbType = 0;
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
      }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function get ()
      {
            try
            {
                  if (isset ($_POST['GIDS']) && count ($_POST['GIDS']) > 0)
                  {
                        $Queue = [];
                        
                        foreach ($_POST['GIDS'] as $GID)
                        {
                              if (strpos ($GID, 'YT_') !== false)
                              {
                                    $LogFile = '/tmp/' . $GID . '.log';
                                    
                                    $Percent = 0;
                                    $Progress = 0;
                                    $Speed = 'N/A';
                                    $Status = 'N/A';
                                    $DbStatus = 5; // Error
                                    if (file_exists ($LogFile) && !is_null ($Line = Tools::ReadLastLineOfFile ($LogFile)))
                                    {
                                          preg_match ('/^\[download\][\s]*([0-9]*\.[0-9]{1}%)([\s]of[\s])([0-9]*\.[0-9]{2}[a-zA-Z]*).*[\s]([0-9]*\.[0-9]{2}[a-zA-Z]*).*$/', $Line, $Matches);
                                          if (count ($Matches) == 5)
                                          {
                                                $Percent = $Matches[1];
                                                $Progress = $Matches[1] . $Matches[2] . str_replace ('i', '', $Matches[3]);
                                                $Speed = str_replace ('i', '', $Matches[4]) . '/s';
                                                $Status = 'Active';
                                                $DbStatus = 1;
                                          }
                                          else
                                          {
                                                preg_match ('/^\[download\][\s]*(100%[\s]of[\s][0-9]*\.[0-9]{2}[a-zA-Z]*).*[\s]([0-9]{2}:[0-9]{2})$/', $Line, $Matches);
                                                if (count ($Matches) == 3)
                                                {
                                                      $Percent = '100%';
                                                      $Progress = str_replace ('i', '', $Matches[1]);
                                                      $Speed = '--';
                                                      $Status = 'Complete';
                                                      $DbStatus = 0;
                                                }
                                          }
                                    }
                                    
                                    $Queue[] = Array (
                                          'GID' => $GID,
                                          'PROGRESSVAL' => $Percent,
                                          'PROGRESS' => $Progress,
                                          'STATUS' => $Status,
                                          'SPEED' => $Speed
                                    );
                              }
                              else
                              {
                                    $Aria2 = new Aria2();
                                    $Status = $Aria2->tellStatus ($GID);
                                    
                                    $Queue[] = Array (
                                          'GID' => $GID,
                                          'PROGRESSVAL' => round((($Status['result']['completedLength'] / $Status['result']['totalLength']) * 100), 2) . '%',
                                          'PROGRESS' => Tools::GetProgressString ($Status['result']['completedLength'], $Status['result']['totalLength']),
                                          'STATUS' => isset ($Status['result']['status']) ? ucfirst ($Status['result']['status']) : 'N/A',
                                          'SPEED' => isset ($Status['result']['downloadSpeed']) ? ($Status['result']['downloadSpeed'] == 0 ? '--' : Tools::FormatSizeUnits ($Status['result']['downloadSpeed']) . '/s') : 'N/A'
                                    );
                                    
                                    $DbStatus = 5; // Error
                                    switch (strtolower ($Status['result']['status']))
                                    {
                                          case 'complete':
                                                $DbStatus = 0;
                                                break;
                                          case 'active':
                                                $DbStatus = 1;
                                                break;
                                          case 'waiting':
                                                $DbStatus = 2;
                                                break;
                                          case 'paused':
                                                $DbStatus = 3;
                                                break;
                                          case 'removed':
                                                $DbStatus = 4;
                                                break;
                                    }
                              }
                              
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET STATUS = ? WHERE GID = ? AND (STATUS != ? OR STATUS IS NULL)';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "GID" = ? AND ("STATUS" != ? OR "STATUS" IS NULL)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $DbStatus,
                                    $GID,
                                    4
                              ));
                        }
                        die (json_encode (Array ('ERROR' => false, 'QUEUE' => $Queue)));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'No GIDS in the queue')));
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
                        if (strpos ($_POST['GID'], 'YT_') !== false)
                        {
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET STATUS = ? WHERE GID = ?';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "GID" = ?';
                              }
            
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    4,
                                    $_POST['GID']
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'The download has been removed')));
                        }
                        else
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
                                    $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET STATUS = ? WHERE GID = ?';
                                    if ($this->DbType == 1)
                                    {
                                          $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "GID" = ?';
                                    }
                  
                                    $Query = \OCP\DB::prepare ($SQL);
                                    $Result = $Query->execute (Array (
                                          4,
                                          $_POST['GID']
                                    ));
                                    
                                    die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'The download has been removed')));
                              }
                              else
                              {
                                    die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'An error occured while removing the download')));
                              }
                        }
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Bad GID')));
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
      public function totalremove ()
      {
            try
            {
                  if (isset ($_POST['GID']) && strlen (trim ($_POST['GID'])) > 0)
                  {
                        if (strpos ($_POST['GID'], 'YT_') === 0)
                        {
                              $LogFile = '/tmp/' . $_POST['GID'] . '.log';
                              
                              if (file_exists ($LogFile) && unlink ($LogFile) === false)
                              {
                                    die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Error while removing the download log file')));
                              }
                        }
                        else
                        {
                              $Aria2 = new Aria2();
                              $Status = $Aria2->tellStatus ($_POST['GID']);
                              
                              if (!isset ($Status['error']) && strcmp ($Status['result']['status'], 'removed') == 0)
                              {
                                    $Remove = $Aria2->removeDownloadResult ($_POST['GID']);
                              }
                        }
                        
                        $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET IS_DELETED = ? WHERE GID = ?';
                        if ($this->DbType == 1)
                        {
                              $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "IS_DELETED" = ? WHERE "GID" = ?';
                        }
      
                        $Query = \OCP\DB::prepare ($SQL);
                        $Result = $Query->execute (Array (
                              1,
                              $_POST['GID']
                        ));
                        
                        die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'The download has been totally removed')));
                  }
                  else
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Bad GID')));
                  }
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
}
?>