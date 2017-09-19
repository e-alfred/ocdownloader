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

    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);

        if (strcmp(Config::getSystemValue('dbtype'), 'pgsql') == 0) {
            $this->DbType = 1;
        }

        $this->CurrentUID = $CurrentUID;

        $Settings = new Settings();
        $Settings->setKey('YTDLBinary');
        $YTDLBinary = $Settings->getValue();

        $this->YTDLBinary = '/usr/local/bin/youtube-dl'; // default path
        if (!is_null($YTDLBinary)) {
            $this->YTDLBinary = $YTDLBinary;
        }

        $Settings->setKey('ProxyAddress');
        $this->ProxyAddress = $Settings->getValue();
        $Settings->setKey('ProxyPort');
        $this->ProxyPort = intval($Settings->getValue());
        $Settings->setKey('ProxyUser');
        $this->ProxyUser = $Settings->getValue();
        $Settings->setKey('ProxyPasswd');
        $this->ProxyPasswd = $Settings->getValue();
        $Settings->setKey('WhichDownloader');
        $this->WhichDownloader = $Settings->getValue();
        $this->WhichDownloader = is_null($this->WhichDownloader) ? 0 :(strcmp($this->WhichDownloader, 'ARIA2') == 0 ? 0 : 1); // 0 means ARIA2, 1 means CURL
        $Settings->setKey('MaxDownloadSpeed');
        $this->MaxDownloadSpeed = $Settings->getValue();
        $Settings->setKey('AllowProtocolYT');
        $this->AllowProtocolYT = $Settings->getValue();
        $this->AllowProtocolYT = is_null($this->AllowProtocolYT) ? true : strcmp($this->AllowProtocolYT, 'Y') == 0;

        $Settings->setTable('personal');
        $Settings->setUID($this->CurrentUID);
        $Settings->setKey('DownloadsFolder');
        $this->DownloadsFolder = $Settings->getValue();

        $this->DownloadsFolder = '/' .(is_null($this->DownloadsFolder) ? 'Downloads' : $this->DownloadsFolder);
        $this->AbsoluteDownloadsFolder = \OC\Files\Filesystem::getLocalFolder($this->DownloadsFolder);

        $this->L10N = $L10N;
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function add()
    {
        \OCP\JSON::setContentTypeHeader('application/json');

        if (isset($_POST['FILE']) && strlen($_POST['FILE']) > 0
              && Tools::checkURL($_POST['FILE']) && isset($_POST['OPTIONS'])) {
            try {
                if (!$this->AllowProtocolYT && !\OC_User::isAdminUser($this->CurrentUID)) {
                    throw new \Exception((string)$this->L10N->t('You are not allowed to use the YouTube protocol'));
                }

                $YouTube = new YouTube($this->YTDLBinary, $_POST['FILE']);

                if (!is_null($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536) {
                    $YouTube->SetProxy($this->ProxyAddress, $this->ProxyPort);
                }

                if (isset($_POST['OPTIONS']['YTForceIPv4']) && strcmp($_POST['OPTIONS']['YTForceIPv4'], 'false') == 0) {
                    $YouTube->SetForceIPv4(false);
                }

                // Extract Audio YES
                if (isset($_POST['OPTIONS']['YTExtractAudio'])
                && strcmp($_POST['OPTIONS']['YTExtractAudio'], 'true') == 0) {
                    $VideoData = $YouTube->getVideoData(true);
                    if (!isset($VideoData['AUDIO']) || !isset($VideoData['FULLNAME'])) {
                        return new JSONResponse(array(
                              'ERROR' => true,
                              'MESSAGE' =>(string)$this->L10N->t('Unable to retrieve true YouTube audio URL')
                        ));
                    }
                    $DL = array(
                        'URL' => $VideoData['AUDIO'],
                        'FILENAME' => Tools::cleanString($VideoData['FULLNAME']),
                        'TYPE' => 'YT Audio'
                    );
                } else // No audio extract
                {
                    $VideoData = $YouTube->getVideoData();
                    if (!isset($VideoData['VIDEO']) || !isset($VideoData['FULLNAME'])) {
                        return new JSONResponse(array(
                              'ERROR' => true,
                              'MESSAGE' =>(string)$this->L10N->t('Unable to retrieve true YouTube video URL')
                        ));
                    }
                    $DL = array(
                        'URL' => $VideoData['VIDEO'],
                        'FILENAME' => Tools::cleanString($VideoData['FULLNAME']),
                        'TYPE' => 'YT Video'
                    );
                }

                // If target file exists, create a new one
                if (\OC\Files\Filesystem::file_exists($this->DownloadsFolder . '/' . $DL['FILENAME'])) {
                    $DL['FILENAME'] = time() . '_' . $DL['FILENAME'];
                }

                // Create the target file if the downloader is ARIA2
                if ($this->WhichDownloader == 0) {
                    \OC\Files\Filesystem::touch($this->DownloadsFolder . '/' . $DL['FILENAME']);
                } else {
                    if (!\OC\Files\Filesystem::is_dir($this->DownloadsFolder)) {
                        \OC\Files\Filesystem::mkdir($this->DownloadsFolder);
                    }
                }

                $OPTIONS = array('dir' => $this->AbsoluteDownloadsFolder, 'out' => $DL['FILENAME']);
                if (!is_null($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536) {
                    $OPTIONS['all-proxy'] = rtrim($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
                    if (!is_null($this->ProxyUser) && !is_null($this->ProxyPasswd)) {
                        $OPTIONS['all-proxy-user'] = $this->ProxyUser;
                        $OPTIONS['all-proxy-passwd'] = $this->ProxyPasswd;
                    }
                }
                if (!is_null($this->MaxDownloadSpeed) && $this->MaxDownloadSpeed > 0) {
                    $OPTIONS['max-download-limit'] = $this->MaxDownloadSpeed . 'K';
                }

                $AddURI =($this->WhichDownloader == 0
                ?Aria2::addUri(array($DL['URL']), array('Params' => $OPTIONS))
                :CURL::addUri($DL['URL'], $OPTIONS));

                if (isset($AddURI['result']) && !is_null($AddURI['result'])) {
                    $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue`
                    (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `STATUS`, `TIMESTAMP`)
                    VALUES(?, ?, ?, ?, ?, ?)';

                    if ($this->DbType == 1) {
                        $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue
                        ("UID", "GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP")
                        VALUES(?, ?, ?, ?, ?, ?)';
                    }

                    $Query = \OCP\DB::prepare($SQL);
                    $Result = $Query->execute(array(
                          $this->CurrentUID,
                          $AddURI['result'],
                          $DL['FILENAME'],
                          $DL['TYPE'],
                          1,
                          time()
                    ));

                    sleep(1);
                    $Status = Aria2::tellStatus($AddURI['result']);

                    $Progress = 0;
                    if ($Status['result']['totalLength'] > 0) {
                        $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                    }

                    $ProgressString = Tools::getProgressString(
                        $Status['result']['completedLength'],
                        $Status['result']['totalLength'],
                        $Progress
                    );

                    return new JSONResponse(array(
                          'ERROR' => false,
                          'MESSAGE' =>(string)$this->L10N->t('Download started'),
                          'GID' => $AddURI['result'],
                          'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                          'PROGRESS' => is_null($ProgressString) ?(string)$this->L10N->t('N/A') : $ProgressString,
                          'STATUS' => isset($Status['result']['status'])
                          ?(string)$this->L10N->t(ucfirst($Status['result']['status']))
                          :(string)$this->L10N->t('N/A'),
                          'STATUSID' => Tools::getDownloadStatusID($Status['result']['status']),
                          'SPEED' => isset($Status['result']['downloadSpeed'])
                          ?Tools::formatSizeUnits($Status['result']['downloadSpeed'])
                          .'/s' :(string)$this->L10N->t('N/A'),
                          'FILENAME' =>$DL['FILENAME'],
                          'FILENAME_SHORT' => Tools::getShortFilename($DL['FILENAME']),
                          'PROTO' => $DL['TYPE'],
                          'ISTORRENT' => false
                    ));
                } else {
                    return new JSONResponse(array(
                          'ERROR' => true,
                          'MESSAGE' =>(string)$this->L10N->t('Returned GID is null ! Is Aria2c running as a daemon ?')
                    ));
                }
            } catch (Exception $E) {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
            }
        } else {
            return new JSONResponse(
                array(
                    'ERROR' => true,
                    'MESSAGE' =>(string)$this->L10N->t('Please check the URL you\'ve just provided')
                )
            );
        }
    }
}
