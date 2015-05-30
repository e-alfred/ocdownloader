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
use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Settings;

class YTDownloaderController extends Controller
{
      private $TargetFolder = null;
      private $DbType = 0;
      private $YTDLBinary = null;
      private $ProxyAddress = null;
      private $ProxyPort = 0;
      private $ProxyUser = null;
      private $ProxyPasswd = null;
      
      public function __construct ($AppName, IRequest $Request, $UserStorage)
      {
            parent::__construct ($AppName, $Request);
            $this->TargetFolder = Config::getSystemValue ('datadirectory') . $UserStorage->getPath ();
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
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
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            if (isset ($_POST['URL']) && strlen ($_POST['URL']) > 0 && Tools::CheckURL ($_POST['URL']) && isset ($_POST['OPTIONS']))
            {
                  try
                  {
                        $YouTube = new YouTube ($this->YTDLBinary, $_POST['URL']);
                        
                        // Extract Audio YES
                        if (isset ($_POST['OPTIONS']['YTExtractAudio']) && strcmp ($_POST['OPTIONS']['YTExtractAudio'], 'true') == 0)
                        {
                              $VideoData = $YouTube->GetVideoData (true);
                              if (!isset ($VideoData['AUDIO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    die (json_encode (Array (
                                          'ERROR' => true, 
                                          'MESSAGE' => 'Unable to retrieve true YouTube video URL'
                                    )));
                              }
                              $DL = Array ('URL' => $VideoData['AUDIO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'Audio');
                        }
                        else // No audio extract
                        {
                              $VideoData = $YouTube->GetVideoData ();
                              if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    die (json_encode (Array (
                                          'ERROR' => true, 
                                          'MESSAGE' => 'Unable to retrieve true YouTube video URL'
                                    )));
                              }
                              $DL = Array ('URL' => $VideoData['VIDEO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'Video');
                        }
                        
                        $Aria2 = new Aria2 ();
                        
                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::file_exists ($DL['FILENAME']))
                        {
                              $DL['FILENAME'] = time () . '_' . $DL['FILENAME'];
                        }
                        // Create the target file
                        \OC\Files\Filesystem::touch ($DL['FILENAME']);
                        
                        $OPTIONS = Array ('dir' => $this->TargetFolder, 'out' => $DL['FILENAME']);
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
                              $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue` (GID, FILENAME, PROTOCOL, STATUS, TIMESTAMP) VALUES (?, ?, ?, ?, ?)';
                              if ($this->DbType == 1)
                              {
                                    $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue ("GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES (?, ?, ?, ?, ?)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    $AddURI['result'],
                                    $DL['FILENAME'],
                                    'YT ' . $DL['TYPE'],
                                    1,
                                    time()
                              ));
                              
                              die (json_encode (Array (
                                    'ERROR' => false, 
                                    'MESSAGE' => 'Download has been launched',
                                    'GID' => $AddURI['result'],
                                    'PROTO' => 'YT ' . $DL['TYPE'],
                                    'NAME' => (strlen ($DL['FILENAME']) > 40 ? substr ($DL['FILENAME'], 0, 40) . '...' : $DL['FILENAME']),
                                    'STATUS' => 'Active',
                                    'SPEED' => '...'
                              )));
                        }
                        else
                        {
                              die (json_encode (Array (
                                    'ERROR' => true, 
                                    'MESSAGE' => 'Returned GID is null ! Is Aria2c running as a daemon ?'
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
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Please check the URL you\'ve just provided')));
            }
      }
}