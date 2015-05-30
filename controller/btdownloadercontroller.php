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
use \OCA\ocDownloader\Controller\Lib\Settings;

class BTDownloaderController extends Controller
{
      private $CurrentUID = null;
      private $TargetFolder = null;
      private $TorrentsFolder = null;
      private $AbsoluteTargetFolder = null;
      private $AbsoluteTorrentsFolder = null;
      private $DbType = 0;
      private $ProxyAddress = null;
      private $ProxyPort = 0;
      private $ProxyUser = null;
      private $ProxyPasswd = null;
      private $Settings = null;
	  
      public function __construct ($AppName, IRequest $Request, $UserStorage, $CurrentUID)
      {
            parent::__construct ($AppName, $Request);
            $this->CurrentUID = $CurrentUID;
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $this->Settings = new Settings ();
            
            $this->Settings->SetKey ('ProxyAddress');
            $this->ProxyAddress = $this->Settings->GetValue ();
            $this->Settings->SetKey ('ProxyPort');
            $this->ProxyPort = intval ($this->Settings->GetValue ());
            $this->Settings->SetKey ('ProxyUser');
            $this->ProxyUser = $this->Settings->GetValue ();
            $this->Settings->SetKey ('ProxyPasswd');
            $this->ProxyPasswd = $this->Settings->GetValue ();
            
            $this->Settings->SetTable ('personal');
            $this->Settings->SetUID ($CurrentUID);
            $this->Settings->SetKey ('DownloadsFolder');
            $this->DownloadsFolder = $this->Settings->GetValue ();
            $this->Settings->SetKey ('TorrentsFolder');
            $this->TorrentsFolder = $this->Settings->GetValue ();
            
            $this->DownloadsFolder = '/' . (is_null ($this->DownloadsFolder) ? 'Downloads' : $this->DownloadsFolder);
            $this->TorrentsFolder = '/' . (is_null ($this->TorrentsFolder) ? 'Downloads/Files/Torrents' : $this->TorrentsFolder);
            
            $this->AbsoluteTargetFolder = Config::getSystemValue ('datadirectory') . $UserStorage->getPath () . $this->DownloadsFolder;
            $this->AbsoluteTorrentsFolder = Config::getSystemValue ('datadirectory') . $UserStorage->getPath () . $this->TorrentsFolder;
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            if (isset ($_POST['PATH']) && strlen (trim ($_POST['PATH'])) > 0 && (Tools::CheckURL ($_POST['PATH']) || Tools::CheckFilepath ($this->TorrentsFolder . '/' . $_POST['PATH']))/* && isset ($_POST['OPTIONS'])*/)
            {
                  try
                  {
                        $Target = str_replace ('.torrent', '', Tools::CleanString ($_POST['PATH']));
                        
                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::is_dir ($this->DownloadsFolder . '/' . $Target))
                        {
                              $Target = time () . '_' . $Target;
                        }
                        
                        // Create the target file
                        \OC\Files\Filesystem::mkdir ($this->DownloadsFolder . '/' . $Target);
                        
                        $OPTIONS = Array ('dir' => $this->AbsoluteTargetFolder . '/' . $Target);
                        
                        $Aria2 = new Aria2 ();
                        $AddTorrent = $Aria2->addTorrent (base64_encode (file_get_contents ($this->AbsoluteTorrentsFolder . '/' . $_POST['PATH'])), Array (), $OPTIONS);
                        
                        if (isset ($AddTorrent['result']) && !is_null ($AddTorrent['result']))
                        {
                              $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue` (GID, FILENAME, PROTOCOL, STATUS, TIMESTAMP) VALUES (?, ?, ?, ?, ?)';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue ("GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES (?, ?, ?, ?, ?)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $AddTorrent['result'],
                                    $Target,
                                    'BitTorrent',
                                    1,
                                    time()
                              ));
                              
                              die (json_encode (Array (
                                    'ERROR' => false, 
                                    'MESSAGE' => 'Download has been launched', 
                                    'NAME' => (strlen ($Target) > 40 ? substr ($Target, 0, 40) . '...' : $Target), 
                                    'GID' => $AddTorrent['result'], 
                                    'PROTO' => 'BitTorrent', 
                                    'SPEED' => '...'
                              )));
                        }
                        else
                        {
                              die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Returned GID is null ! Is Aria2c running as a daemon ?')));
                        }
                  }
                  catch (Exception $E)
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
                  }
            }
            else
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Please check the URL or filepath you\'ve just provided')));
            }
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function listtorrentfiles ()
      {
            try
            {
                  if (!\OC\Files\Filesystem::is_dir ($this->TorrentsFolder))
                  {
                        \OC\Files\Filesystem::mkdir ($this->TorrentsFolder);
                  }
                  
                  $this->TorrentsFolder = \OC\Files\Filesystem::normalizePath($this->TorrentsFolder);
                  
                  $Files = \OCA\Files\Helper::getFiles ($this->TorrentsFolder, 'name', 'desc');
                  $Files = \OCA\Files\Helper::formatFileInfos ($Files);
                  
                  die (json_encode (Array ('ERROR' => false, 'FILES' => $Files)));
            }
            catch (Exception $E)
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
            }
      }
}