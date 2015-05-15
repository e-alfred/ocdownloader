<?php
namespace OCA\ocDownloader\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;

use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Tools;

class DownloaderQueueController extends Controller
{
      public function __construct ($AppName, IRequest $Request)
      {
            parent::__construct($AppName, $Request);
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
                        $Aria2 = new Aria2();
                        $Tools = new Tools();
                        foreach ($_POST['GIDS'] as $GID)
                        {
                              $Status = $Aria2->tellStatus ($GID);
                              $Queue[] = Array (
                                    'GID' => $GID,
                                    'PROGRESS' => $Tools->GetProgressString($Status['result']['completedLength'], $Status['result']['totalLength']),
                                    'STATUS' => isset($Status['result']['status']) ? ucfirst($Status['result']['status']) : 'N/A'
                              );
                              
                              if (strcmp (strtolower ($Status['result']['status']), 'complete') == 0)
                              {
                                    $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET IS_ACTIVE = ? WHERE GID = ? AND IS_ACTIVE = ?';
                                    $Query = \OCP\DB::prepare ($SQL);
                                    $Result = $Query->execute (Array (
                                          0,
                                          $AddURI["result"],
                                          1
                                    ));
                              }
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
                        $Aria2 = new Aria2();
                        $Status = $Aria2->tellStatus ($_POST['GID']);
                        
                        $Remove['result'] = $_POST['GID'];
                        if (strcmp (strtolower ($Status['result']['status']), 'complete') != 0 && !isset ($Status['error']['code']))
                        {
                              $Remove = $Aria2->remove ($_POST['GID']);
                        }
                        
                        if (strcmp ($Remove['result'], $_POST['GID']) == 0)
                        {
                              $Reset = $Aria2->removeDownloadResult ($_POST['GID']);
                              if (strcmp ($Reset['result'], 'OK') == 0)
                              {
                                    $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET IS_DELETED = ? WHERE GID = ?';
                                    $Query = \OCP\DB::prepare ($SQL);
                                    $Result = $Query->execute (Array (
                                          1,
                                          $_POST['GID']
                                    ));
                                    
                                    die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'The download has been removed')));
                              }
                              
                              $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET IS_DELETED = ? WHERE GID = ?';
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    1,
                                    $_POST['GID']
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'The download has been removed but an error occured while removing results')));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'An error occured while removing the download')));
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
}
?>