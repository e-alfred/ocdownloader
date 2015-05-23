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

use \OCA\ocDownloader\Controller\Lib\YouTube;
use \OCA\ocDownloader\Controller\Lib\Tools;

class YTDownloaderController extends Controller
{
      private $TargetFolder = null;
      private $DbType = 0;
      private $YTDLBinary = null;
      
      public function __construct ($AppName, IRequest $Request, $UserStorage)
      {
            parent::__construct ($AppName, $Request);
            $this->TargetFolder = Config::getSystemValue ('datadirectory') . $UserStorage->getPath ();
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            // Get YouTube-DL binary custom path
            $SQL = 'SELECT `VAL` FROM `*PREFIX*ocdownloader_adminsettings` WHERE `KEY` = ? LIMIT 1';
            if ($this->DbType == 1)
            {
                  $SQL = 'SELECT "VAL" FROM *PREFIX*ocdownloader_adminsettings WHERE "KEY" = ? LIMIT 1';
            }
            $Query = \OCP\DB::prepare ($SQL);
            $Result = $Query->execute (Array ('YTDLBinary'));
            
            $this->YTDLBinary = '/usr/local/bin/youtube-dl'; // default path
            if ($Query->rowCount () == 1)
            {
                  $this->YTDLBinary = $Result->fetchOne (); // custom path
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            if (isset ($_POST['URL']) && strlen ($_POST['URL']) > 0 && Tools::CheckURL ($_POST['URL'])/* && isset ($_POST['OPTIONS'])*/)
            {
                  try
                  {
                        $YouTube = new YouTube ($this->YTDLBinary, $_POST['URL']);
                        
                        $Target = Tools::CleanString ($YouTube->GetFileName ());
                        
                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::file_exists ($Target))
                        {
                              $Target = $Target . '.' . time ();
                        }
                        
                        $GID = 'YT_' . str_replace (' ', '', microtime ());
                        if ($YouTube->Download ($this->TargetFolder . '/' . $Target, $GID) !== false)
                        {
                              $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue` (GID, FILENAME, PROTOCOL, STATUS, TIMESTAMP) VALUES (?, ?, ?, ?, ?)';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue ("GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES (?, ?, ?, ?, ?)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $GID,
                                    $Target,
                                    'YOUTUBE',
                                    1,
                                    time()
                              ));
                              
                              die (json_encode (Array ('ERROR' => false, 'MESSAGE' => 'Download has been launched', 'NAME' => (strlen ($Target) > 40 ? substr ($Target, 0, 40) . '...' : $Target), 'GID' => $GID, 'PROTO' => 'YOUTUBE', 'SPEED' => '...')));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Unable to launch the download', 'NAME' => (strlen ($Target) > 40 ? substr ($Target, 0, 40) . '...' : $Target), 'GID' => $GID, 'PROTO' => 'YOUTUBE', 'SPEED' => '...')));
                        }
                  }
                  catch (Exception $E)
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
                  }
            }
            else
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Please check the URL you\'ve just provided')));
            }
      }
}