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
                                    'STATUS' => ucfirst($Status['result']['status'])
                              );
                              
                              if (strcmp (strtolower ($Status['result']['status']), 'complete') == 0)
                              {
                                    $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET IS_ACTIVE = ? WHERE GID = ?';
                                    $Query = \OCP\DB::prepare ($SQL);
                                    $Result = $Query->execute (Array (
                                          0,
                                          $AddURI["result"]
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
}
?>