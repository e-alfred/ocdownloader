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

use OCA\ocDownloader\Controller\Lib\Aria2;
use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Settings;

class BTDownloader extends Controller
{
    private $CurrentUID = null;
    private $DownloadsFolder = null;
    private $TorrentsFolder = null;
    private $AbsoluteDownloadsFolder = null;
    private $AbsoluteTorrentsFolder = null;
    private $DbType = 0;
    private $ProxyAddress = null;
    private $ProxyPort = 0;
    private $ProxyUser = null;
    private $ProxyPasswd = null;
    private $ProxyOnlyWithYTDL = null;
    private $Settings = null;
    private $L10N = null;
    private $AllowProtocolBT = null;
    private $MaxDownloadSpeed = null;
    private $BTMaxUploadSpeed = null;
    private $BTRatioToReach = null;
    private $SeedTime = null;

    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);

        if (strcmp(Config::getSystemValue('dbtype'), 'pgsql') == 0) {
            $this->DbType = 1;
        }

        $this->CurrentUID = $CurrentUID;

        $this->Settings = new Settings();

        $this->Settings->setKey('ProxyAddress');
        $this->ProxyAddress = $this->Settings->getValue();
        $this->Settings->setKey('ProxyPort');
        $this->ProxyPort = intval($this->Settings->getValue());
        $this->Settings->setKey('ProxyUser');
        $this->ProxyUser = $this->Settings->getValue();
        $this->Settings->setKey('ProxyPasswd');
        $this->ProxyPasswd = $this->Settings->getValue();
        $this->Settings->setKey('ProxyOnlyWithYTDL');
        $this->ProxyOnlyWithYTDL = $this->Settings->getValue();
        $this->ProxyOnlyWithYTDL = is_null($this->ProxyOnlyWithYTDL)?false:(strcmp($this->ProxyOnlyWithYTDL, 'Y') == 0);

        $this->Settings->setKey('MaxDownloadSpeed');
        $this->MaxDownloadSpeed = $this->Settings->getValue();
        $this->Settings->setKey('BTMaxUploadSpeed');
        $this->BTMaxUploadSpeed = $this->Settings->getValue();

        $this->Settings->setKey('AllowProtocolBT');
        $this->AllowProtocolBT = $this->Settings->getValue();
        $this->AllowProtocolBT = is_null($this->AllowProtocolBT) ? true : strcmp($this->AllowProtocolBT, 'Y') == 0;

        $this->Settings->setTable('personal');
        $this->Settings->setUID($this->CurrentUID);
        $this->Settings->setKey('DownloadsFolder');
        $this->DownloadsFolder = $this->Settings->getValue();
        $this->Settings->setKey('TorrentsFolder');
        $this->TorrentsFolder = $this->Settings->getValue();
        $this->Settings->setKey('BTRatioToReach');
        $this->BTRatioToReach = $this->Settings->getValue();
        $this->BTRatioToReach = is_null($this->BTRatioToReach) ? '0.0' : $this->BTRatioToReach;

        $this->Settings->setKey('BTSeedTimeToReach_BTSeedTimeToReachUnit');
        $this->SeedTime = $this->Settings->getValue();
        if (!is_null($this->SeedTime)) {
            $this->SeedTime = explode('_', $this->SeedTime);
            if (count($this->SeedTime) == 2) {
                $this->SeedTime = Tools::getMinutes($this->SeedTime[0], $this->SeedTime[1]);
            }
        } else {
            $this->SeedTime = 10080; // minutes in 1 week - default
        }

        $this->DownloadsFolder = '/' .(is_null($this->DownloadsFolder) ? 'Downloads' : $this->DownloadsFolder);
        $this->TorrentsFolder = '/'.(is_null($this->TorrentsFolder)?'Downloads/Files/Torrents':$this->TorrentsFolder);

        $this->AbsoluteDownloadsFolder = \OC\Files\Filesystem::getLocalFolder($this->DownloadsFolder);
        $this->AbsoluteTorrentsFolder = \OC\Files\Filesystem::getLocalFolder($this->TorrentsFolder);

        $this->L10N = $L10N;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function add()
    {
        \OCP\JSON::setContentTypeHeader('application/json');

        if (isset($_POST['FILE']) && strlen(trim($_POST['FILE'])) > 0
            && (Tools::checkURL($_POST['FILE']) || Tools::checkFilepath($this->TorrentsFolder . '/' . $_POST['FILE']))
            && isset($_POST['OPTIONS'])) {
            try {
                if (!$this->AllowProtocolBT && !\OC_User::isAdminUser($this->CurrentUID)) {
                    throw new \Exception((string)$this->L10N->t('You are not allowed to use the BitTorrent protocol'));
                }

                $Target = Tools::cleanString(str_replace('.torrent', '', $_POST['FILE']));

                $OPTIONS = array(
                    'dir' => rtrim($this->AbsoluteDownloadsFolder, '/').'/'.$Target,
                    'seed-ratio' => $this->BTRatioToReach,
                    'seed-time' => $this->SeedTime
                );
                // If target file exists, create a new one
                if (!\OC\Files\Filesystem::is_dir(rtrim($this->DownloadsFolder, '/') . '/' . $Target)) {
                    // Create the target file
                    \OC\Files\Filesystem::mkdir(rtrim($this->DownloadsFolder, '/') . '/' . $Target);
                } else {
                    $OPTIONS['bt-hash-check-seed'] = true;
                    $OPTIONS['check-integrity'] = true;
                }
                if (!is_null($this->MaxDownloadSpeed) && $this->MaxDownloadSpeed > 0) {
                    $OPTIONS['max-download-limit'] = $this->MaxDownloadSpeed . 'K';
                }
                if (!is_null($this->BTMaxUploadSpeed) && $this->BTMaxUploadSpeed > 0) {
                    $OPTIONS['max-upload-limit'] = $this->BTMaxUploadSpeed . 'K';
                }
                if (!$this->ProxyOnlyWithYTDL && !is_null($this->ProxyAddress)
                    && $this->ProxyPort > 0 && $this->ProxyPort <= 65536) {
                    $OPTIONS['all-proxy'] = rtrim($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
                    if (!is_null($this->ProxyUser) && !is_null($this->ProxyPasswd)) {
                        $OPTIONS['all-proxy-user'] = $this->ProxyUser;
                        $OPTIONS['all-proxy-passwd'] = $this->ProxyPasswd;
                    }
                }

                $AddTorrent = Aria2::addTorrent(
                    base64_encode(file_get_contents(rtrim($this->AbsoluteTorrentsFolder, '/').'/' . $_POST['FILE'])),
                    array(),
                    array('Params' => $OPTIONS)
                );

                if (isset($AddTorrent['result']) && !is_null($AddTorrent['result'])) {
                    $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue`
                        (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `STATUS`, `TIMESTAMP`) VALUES(?, ?, ?, ?, ?, ?)';
                    if ($this->DbType == 1) {
                        $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue
                            ("UID", "GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES(?, ?, ?, ?, ?, ?)';
                    }

                    $Query = \OCP\DB::prepare($SQL);
                    $Result = $Query->execute(array(
                        $this->CurrentUID,
                        $AddTorrent['result'],
                        $Target,
                        'BitTorrent',
                        1,
                        time()
                    ));

                    if (isset($_POST['OPTIONS']['BTRMTorrent'])
                        && strcmp($_POST['OPTIONS']['BTRMTorrent'], "true") == 0) {
                        \OC\Files\Filesystem::unlink($this->TorrentsFolder . '/' . $_POST['FILE']);
                    }

                    sleep(1);
                    $Status = Aria2::tellStatus($AddTorrent['result']);
                    $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                    return new JSONResponse(array(
                        'ERROR' => false,
                        'MESSAGE' =>(string)$this->L10N->t('Download started'),
                        'GID' => $AddTorrent['result'],
                        'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                        'PROGRESS' => Tools::getProgressString(
                            $Status['result']['completedLength'],
                            $Status['result']['totalLength'],
                            $Progress
                        ).' - '.$this->L10N->t('Seeders').': '.$Status['result']['numSeeders'],
                        'STATUS' => isset($Status['result']['status'])
                            ?(string)$this->L10N->t(ucfirst($Status['result']['status']))
                            :(string)$this->L10N->t('N/A'),
                        'STATUSID' => Tools::getDownloadStatusID($Status['result']['status']),
                        'SPEED' => isset($Status['result']['downloadSpeed'])
                            ?Tools::formatSizeUnits($Status['result']['downloadSpeed']).'/s'
                            :(string)$this->L10N->t('N/A'),
                        'FILENAME' => $Target,
                        'FILENAME_SHORT' => Tools::getShortFilename($Target),
                    'PROTO' => 'BitTorrent',
                        'ISTORRENT' => true
                    ));
                } else {
                    return new JSONResponse(
                        array(
                            'ERROR' => true,
                            'MESSAGE' =>(string)$this->L10N->t('Returned GID is null ! Is Aria2c running as a daemon ?')
                        )
                    );
                }
            } catch (Exception $E) {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
            }
        } else {
            return new JSONResponse(
                array(
                    'ERROR' => true,
                    'MESSAGE' =>(string)$this->L10N->t('Please check the URL or filepath you\'ve just provided')
                )
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function listTorrentFiles()
    {
        \OCP\JSON::setContentTypeHeader('application/json');

        try {
            if (!$this->AllowProtocolBT && !\OC_User::isAdminUser($this->CurrentUID)) {
                throw new \Exception((string)$this->L10N->t('You are not allowed to use the BitTorrent protocol'));
            }

            if (!\OC\Files\Filesystem::is_dir($this->TorrentsFolder)) {
                \OC\Files\Filesystem::mkdir($this->TorrentsFolder);
            }

            $this->TorrentsFolder = \OC\Files\Filesystem::normalizePath($this->TorrentsFolder);

            $Files = \OCA\Files\Helper::getFiles($this->TorrentsFolder, 'name', 'desc', 'application/octet-stream');
            $Files = \OCA\Files\Helper::formatFileInfos($Files);

            return new JSONResponse(array('ERROR' => false, 'FILES' => $Files));
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function uploadFiles()
    {
        \OCP\JSON::setContentTypeHeader('text/plain');

        if (!$this->AllowProtocolBT && !\OC_User::isAdminUser($this->CurrentUID)) {
            return new JSONResponse(
                array(
                    'ERROR' => true,
                    'MESSAGE' =>(string)$this->L10N->t('You are not allowed to use the BitTorrent protocol')
                )
            );
        }

        if (!isset($_FILES['files'])) {
            return new JSONResponse(
                array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Error while uploading torrent file'))
            );
        } else {
            if (!isset($_FILES['files']['name'][0])) {
                throw new \Exception('Unable to find the uploaded file');
            }

            $Target = rtrim($this->TorrentsFolder, '/') . '/' . $_FILES['files']['name'][0];
            try {
                if (is_uploaded_file($_FILES['files']['tmp_name'][0])
                    && \OC\Files\Filesystem::fromTmpFile($_FILES['files']['tmp_name'][0], $Target)) {
                    $StorageStats = \OCA\Files\Helper::buildFileStorageStatistics($this->TorrentsFolder);

                    if (\OC\Files\Filesystem::getFileInfo($Target) !== false) {
                        return new JSONResponse(
                            array('ERROR' => false, 'MESSAGE' =>(string)$this->L10N->t('Upload OK'))
                        );
                    }
                } else {
                    return new JSONResponse(
                        array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Error while uploading torrent file'))
                    );
                }
            } catch (Exception $E) {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
            }
        }
    }
}
