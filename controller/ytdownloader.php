<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

namespace OCA\ocDownloader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Config;
use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\YouTube;
use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Aria2;
use OCA\ocDownloader\Controller\Lib\CURL;
use OCA\ocDownloader\Controller\Lib\Settings;

class YTDownloader extends Controller
{
      private $AbsoluteDownloadsFolder = null;
      private $DownloadsFolder = null;
      private $DbType = 0;
      private $YTDLBinary = null;
      private $ProxyAddress = null;
      private $ProxyPort = 0;
      private $ProxyUser = null;
      private $ProxyPasswd = null;
      private $ProxyOnlyWithYTDL = null;
      private $WhichDownloader = 0;
      private $CurrentUID = null;
      private $L10N = null;
      private $AllowProtocolYT = null;
      private $MaxDownloadSpeed = null;

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
            $Settings->SetKey ('WhichDownloader');
            $this->WhichDownloader = $Settings->GetValue ();
            $this->WhichDownloader = is_null ($this->WhichDownloader) ? 0 : (strcmp ($this->WhichDownloader, 'ARIA2') == 0 ? 0 : 1); // 0 means ARIA2, 1 means CURL
            $Settings->SetKey ('MaxDownloadSpeed');
            $this->MaxDownloadSpeed = $Settings->GetValue ();
            $Settings->SetKey ('AllowProtocolYT');
            $this->AllowProtocolYT = $Settings->GetValue ();
            $this->AllowProtocolYT = is_null ($this->AllowProtocolYT) ? true : strcmp ($this->AllowProtocolYT, 'Y') == 0;

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
      public function Add ()
      {
            \OCP\JSON::setContentTypeHeader ('application/json');

            if (isset ($_POST['FILE']) && strlen ($_POST['FILE']) > 0 && Tools::CheckURL ($_POST['FILE']) && isset ($_POST['OPTIONS']))
            {
                  try
                  {
                        if (!$this->AllowProtocolYT && !\OC_User::isAdminUser ($this->CurrentUID))
                        {
                              throw new \Exception ((string)$this->L10N->t ('You are not allowed to use the YouTube protocol'));
                        }

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
                                    return new JSONResponse (Array (
                                          'ERROR' => true,
                                          'MESSAGE' => (string)$this->L10N->t ('Unable to retrieve true YouTube audio URL')
                                    ));
                              }
                              $DL = Array ('URL' => $VideoData['AUDIO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'YT Audio');
                        }
                        else // No audio extract
                        {
                              $VideoData = $YouTube->GetVideoData ();
                              if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    return new JSONResponse (Array (
                                          'ERROR' => true,
                                          'MESSAGE' => (string)$this->L10N->t ('Unable to retrieve true YouTube video URL')
                                    ));
                              }
                              $DL = Array ('URL' => $VideoData['VIDEO'], 'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']), 'TYPE' => 'YT Video');
                        }

                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::file_exists ($this->DownloadsFolder . '/' . $DL['FILENAME']))
                        {
                              $DL['FILENAME'] = time () . '_' . $DL['FILENAME'];
                        }

                        // Create the target file if the downloader is ARIA2
                        if ($this->WhichDownloader == 0)
                        {
                              \OC\Files\Filesystem::touch ($this->DownloadsFolder . '/' . $DL['FILENAME']);
                        }
                        else
                        {
                              if (!\OC\Files\Filesystem::is_dir ($this->DownloadsFolder))
                              {
                                    \OC\Files\Filesystem::mkdir ($this->DownloadsFolder);
                              }
                        }

                        $OPTIONS = Array ('dir' => $this->AbsoluteDownloadsFolder, 'out' => $DL['FILENAME']);
                        if (!is_null ($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536)
                        {
                              $OPTIONS['all-proxy'] = rtrim ($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
                              if (!is_null ($this->ProxyUser) && !is_null ($this->ProxyPasswd))
                              {
                                    $OPTIONS['all-proxy-user'] = $this->ProxyUser;
                                    $OPTIONS['all-proxy-passwd'] = $this->ProxyPasswd;
                              }
                        }
                        if (!is_null ($this->MaxDownloadSpeed) && $this->MaxDownloadSpeed > 0)
                        {
                              $OPTIONS['max-download-limit'] = $this->MaxDownloadSpeed . 'K';
                        }

                        $AddURI = ($this->WhichDownloader == 0 ? Aria2::AddUri (Array ($DL['URL']), Array ('Params' => $OPTIONS)) : CURL::AddUri ($DL['URL'], $OPTIONS));

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
                                    $DL['TYPE'],
                                    1,
                                    time()
                              ));

                              sleep (1);
                              $Status = Aria2::TellStatus ($AddURI['result']);

                              $Progress = 0;
                              if ($Status['result']['totalLength'] > 0)
                              {
                                    $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                              }

                              $ProgressString = Tools::GetProgressString ($Status['result']['completedLength'], $Status['result']['totalLength'], $Progress);

                              return new JSONResponse (Array (
                                    'ERROR' => false,
                                    'MESSAGE' => (string)$this->L10N->t ('Download started'),
                                    'GID' => $AddURI['result'],
                                    'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                                    'PROGRESS' => is_null ($ProgressString) ? (string)$this->L10N->t ('N/A') : $ProgressString,
                                    'STATUS' => isset ($Status['result']['status']) ? (string)$this->L10N->t (ucfirst ($Status['result']['status'])) : (string)$this->L10N->t ('N/A'),
                                    'STATUSID' => Tools::GetDownloadStatusID ($Status['result']['status']),
                                    'SPEED' => isset ($Status['result']['downloadSpeed']) ? Tools::FormatSizeUnits ($Status['result']['downloadSpeed']) . '/s' : (string)$this->L10N->t ('N/A'),
                                    'FILENAME' => (mb_strlen ($DL['FILENAME'], "UTF-8") > 40 ? mb_substr ($DL['FILENAME'], 0, 40, "UTF-8") . '...' : $DL['FILENAME']),
                                    'FILENAME_SHORT' => Tools::getShortFilename($DL['FILENAME']),
                                    'PROTO' => $DL['TYPE'],
                                    'ISTORRENT' => false
                              ));
                        }
                        else
                        {
                              return new JSONResponse (Array (
                                    'ERROR' => true,
                                    'MESSAGE' => (string)$this->L10N->t ('Returned GID is null ! Is Aria2c running as a daemon ?')
                              ));
                        }
                  }
                  catch (Exception $E)
                  {
                        return new JSONResponse (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ()));
                  }
            }
            else
            {
                  return new JSONResponse (Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Please check the URL you\'ve just provided')));
            }
      }
}
