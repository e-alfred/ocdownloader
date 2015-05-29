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
            if (isset ($_POST['URL']) && strlen ($_POST['URL']) > 0 && Tools::CheckURL ($_POST['URL']) && isset ($_POST['OPTIONS']))
            {
                  try
                  {
                        $YouTube = new YouTube ($this->YTDLBinary, $_POST['URL']);
                        
                        $URIArr = Array ();
                        
                        // Extract Audio YES
                        if (isset ($_POST['OPTIONS']['YTExtractAudio']) && strcmp ($_POST['OPTIONS']['YTExtractAudio'], 'true') == 0)
                        {
                              $VideoData = $YouTube->GetVideoData (true);
                              if (!isset ($VideoData['AUDIO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    // die video data not found
                              }
                              $TmpArr = Array ('URL' => $VideoData['AUDIO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'Audio');
                              
                              $Formats = Array ('best', 'aac', 'vorbis', 'mp3', 'm4a', 'opus', 'wav');
                              $Qualities = Array ('0', '5', '9');
                              if (isset ($_POST['OPTIONS']['YTEAFormat']) && isset ($_POST['OPTIONS']['YTEAQuality']) && in_array ($_POST['OPTIONS']['YTEAFormat'], $Formats) && in_array ($_POST['OPTIONS']['YTEAQuality'], $Qualities))
                              {
                                    // Extract audio with post-processing (FFMPEG is installed)
                                    $TmpArr['FILENAME'] = Tools::YouTubeDLExtractAudioReplaceExtension ($TmpArr['FILENAME'], $_POST['OPTIONS']['YTEAFormat']);
                              }
                              
                              $URIArr[] = $TmpArr;
                        }
                        else // No audio extract
                        {
                              $VideoData = $YouTube->GetVideoData ();
                              if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    // die video data not found
                              }
                              $URIArr[] = Array ('URL' => $VideoData['VIDEO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'Video');
                              
                              if (isset ($VideoData['AUDIO']))
                              {
                                    // FFMPEG is installed, so also download audio
                                    $URIArr[] = Array ('URL' => $VideoData['AUDIO'], 'FILENAME' => Tools::CleanString (Tools::YouTubeDLExtractAudioReplaceExtension ($VideoData['FULLNAME'], 'audio')), 'TYPE' => 'Audio');
                              }
                        }
                        
                        $Aria2 = new Aria2 ();
                        $Queue = Array ();
                        
                        for ($I = 0; $I < count ($URIArr); $I++)
                        {
                              // If target file exists, create a new one
                              if (\OC\Files\Filesystem::file_exists ($URIArr[$I]['FILENAME']))
                              {
                                    $URIArr[$I]['FILENAME'] = time () . '_' . $URIArr[$I]['FILENAME'];
                              }
                              // Create the target file
                              \OC\Files\Filesystem::touch ($URIArr[$I]['FILENAME']);
                              
                              $AddURI = $Aria2->addUri (Array ($URIArr[$I]['URL']), Array ('dir' => $this->TargetFolder, 'out' => $URIArr[$I]['FILENAME']));
                              
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
                                          $URIArr[$I]['FILENAME'],
                                          strtoupper(substr($_POST['URL'], 0, strpos($_POST['URL'], ':'))),
                                          1,
                                          time()
                                    ));
                                    
                                    $Queue[] = Array (
                                          'ERROR' => false, 
                                          'MESSAGE' => 'Download has been launched',
                                          'GID' => $AddURI['result'],
                                          'PROTO' => 'YT ' . $URIArr[$I]['TYPE'],
                                          'NAME' => (strlen ($URIArr[$I]['FILENAME']) > 40 ? substr ($URIArr[$I]['FILENAME'], 0, 40) . '...' : $URIArr[$I]['FILENAME']),
                                          'STATUS' => 'Active',
                                          'SPEED' => '...'
                                    );
                              }
                              else
                              {
                                    $Queue[] = Array (
                                          'ERROR' => true, 
                                          'MESSAGE' => 'Returned GID is null ! Is Aria2c running as a daemon ?'
                                    );
                              }
                        }
                        die (json_encode ($Queue));
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