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

use \OCA\ocDownloader\Controller\Lib\YouTube;
use \OCA\ocDownloader\Controller\Lib\Tools;
use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Settings;

class YTDownloaderController extends Controller
{
      private $AbsoluteDownloadsFolder = null;
      private $DownloadsFolder = null;
      private $DbType = 0;
      private $YTDLBinary = null;
      private $ProxyAddress = null;
      private $ProxyPort = 0;
      private $ProxyUser = null;
      private $ProxyPasswd = null;
      private $CurrentUID = null;
      
      public function __construct ($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
      {
            parent::__construct ($AppName, $Request);
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $this->CurrentUID = $CurrentUID;
            
            $Settings = new Settings ();
            $Settings->SetKey ('YTDLBinary');
            $YTDLBinary = $Settings->GetValue ();
            
            $this->YTDLBinary = '/usr/local/bin/youtube-dl'; // default path
            if (!is_null ($YTDLBinary))
            {
                  $this->YTDLBinary = $YTDLBinary;
            }
            
            $Settings->SetKey ('ProxyAddress');
            $this->ProxyAddress = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPort');
            $this->ProxyPort = intval ($Settings->GetValue ());
            $Settings->SetKey ('ProxyUser');
            $this->ProxyUser = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPasswd');
            $this->ProxyPasswd = $Settings->GetValue ();
            
            $Settings->SetTable ('personal');
            $Settings->SetUID ($this->CurrentUID);
            $Settings->SetKey ('DownloadsFolder');
            $this->DownloadsFolder = $Settings->GetValue ();
            
            $this->DownloadsFolder = '/' . (is_null ($this->DownloadsFolder) ? 'Downloads' : $this->DownloadsFolder);
            $this->AbsoluteDownloadsFolder = \OC\Files\Filesystem::getLocalFolder ($this->DownloadsFolder);
            
            $this->L10N = $L10N;
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            if (isset ($_POST['FILE']) && strlen ($_POST['FILE']) > 0 && Tools::CheckURL ($_POST['FILE']) && isset ($_POST['OPTIONS']))
            {
                  try
                  {
                        $YouTube = new YouTube ($this->YTDLBinary, $_POST['FILE']);
                        
                        if (!is_null ($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536)
                        {
                              $YouTube->SetProxy ($this->ProxyAddress, $this->ProxyPort);
                        }
                        
                        if (isset ($_POST['OPTIONS']['YTForceIPv4']) && strcmp ($_POST['OPTIONS']['YTForceIPv4'], 'false') == 0)
                        {
                              $YouTube->SetForceIPv4 (false);
                        }
                        
                        // Extract Audio YES
                        if (isset ($_POST['OPTIONS']['YTExtractAudio']) && strcmp ($_POST['OPTIONS']['YTExtractAudio'], 'true') == 0)
                        {
                              $VideoData = $YouTube->GetVideoData (true);
                              if (!isset ($VideoData['AUDIO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    die (json_encode (Array (
                                          'ERROR' => true, 
                                          'MESSAGE' => (string)$this->L10N->t ('Unable to retrieve true YouTube audio URL')
                                    )));
                              }
                              $DL = Array ('URL' => $VideoData['AUDIO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => (string)$this->L10N->t ('Audio'));
                        }
                        else // No audio extract
                        {
                              $VideoData = $YouTube->GetVideoData ();
                              if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    die (json_encode (Array (
                                          'ERROR' => true, 
                                          'MESSAGE' => (string)$this->L10N->t ('Unable to retrieve true YouTube video URL')
                                    )));
                              }
                              $DL = Array ('URL' => $VideoData['VIDEO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => (string)$this->L10N->t ('Video'));
                        }
                        
                        $Aria2 = new Aria2 ();
                        
                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::file_exists ($this->DownloadsFolder . '/' . $DL['FILENAME']))
                        {
                              $DL['FILENAME'] = time () . '_' . $DL['FILENAME'];
                        }
                        // Create the target file
                        \OC\Files\Filesystem::touch ($this->DownloadsFolder . '/' . $DL['FILENAME']);
                        
                        $OPTIONS = Array ('dir' => $this->AbsoluteDownloadsFolder, 'out' => $DL['FILENAME']);
                        if (!is_null ($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536)
                        {
                              $OPTIONS['all-proxy'] = $this->ProxyAddress . ':' . $this->ProxyPort;
                              if (!is_null ($this->ProxyUser) && !is_null ($this->ProxyPasswd))
                              {
                                    $OPTIONS['all-proxy-user'] = $this->ProxyUser;
                                    $OPTIONS['all-proxy-passwd'] = $this->ProxyPasswd;
                              }
                        }
                        
                        $AddURI = $Aria2->addUri (Array ($DL['URL']), $OPTIONS);
                        
                        if (isset ($AddURI['result']) && !is_null ($AddURI['result']))
                        {
                              $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue` (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `STATUS`, `TIMESTAMP`) VALUES (?, ?, ?, ?, ?, ?)';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue ("UID", "GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES (?, ?, ?, ?, ?, ?)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $this->CurrentUID,
                                    $AddURI['result'],
                                    $DL['FILENAME'],
                                    'YT ' . $DL['TYPE'],
                                    1,
                                    time()
                              ));
                              
                              sleep (1);
                              $Status = $Aria2->tellStatus ($AddURI['result']);
                              $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                              die (json_encode (Array (
                                    'ERROR' => false, 
                                    'MESSAGE' => (string)$this->L10N->t ('Download started'), 
                                    'GID' => $AddURI['result'],
                                    'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                                    'PROGRESS' => Tools::GetProgressString ($Status['result']['completedLength'], $Status['result']['totalLength'], $Progress),
                                    'STATUS' => isset ($Status['result']['status']) ? (string)$this->L10N->t (ucfirst ($Status['result']['status'])) : (string)$this->L10N->t ('N/A'),
                                    'STATUSID' => Tools::GetDownloadStatusID ($Status['result']['status']),
                                    'SPEED' => isset ($Status['result']['downloadSpeed']) ? Tools::FormatSizeUnits ($Status['result']['downloadSpeed']) . '/s' : (string)$this->L10N->t ('N/A'),
                                    'FILENAME' => (strlen ($DL['FILENAME']) > 40 ? substr ($DL['FILENAME'], 0, 40) . '...' : $DL['FILENAME']),
                                    'PROTO' => 'YT ' . $DL['TYPE'],
                                    'ISTORRENT' => false
                              )));
                        }
                        else
                        {
                              die (json_encode (Array (
                                    'ERROR' => true, 
                                    'MESSAGE' => (string)$this->L10N->t ('Returned GID is null ! Is Aria2c running as a daemon ?')
                              )));
                        }
                  }
                  catch (Exception $E)
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
                  }
            }
            else
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Please check the URL you\'ve just provided'))));
            }
      }
}