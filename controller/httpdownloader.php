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

use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\Aria2;
use OCA\ocDownloader\Controller\Lib\CURL;
use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Settings;

class HttpDownloader extends Controller
{
    private $AbsoluteDownloadsFolder = null;
    private $DownloadsFolder = null;
    private $DbType = 0;
    private $ProxyAddress = null;
    private $ProxyPort = 0;
    private $ProxyUser = null;
    private $ProxyPasswd = null;
    private $ProxyOnlyWithYTDL = null;
    private $WhichDownloader = 0;
    private $CurrentUID = null;
    private $L10N = null;
    private $AllowProtocolHTTP = null;
    private $MaxDownloadSpeed = null;

    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);

        if (strcmp(\OC::$server->getConfig()->getSystemValue('dbtype'), 'pgsql') == 0) {
            $this->DbType = 1;
        }

        $this->CurrentUID = $CurrentUID;

        $Settings = new Settings();
        $Settings->setKey('ProxyAddress');
        $this->ProxyAddress = $Settings->getValue();
        $Settings->setKey('ProxyPort');
        $this->ProxyPort = intval($Settings->getValue());
        $Settings->setKey('ProxyUser');
        $this->ProxyUser = $Settings->getValue();
        $Settings->setKey('ProxyPasswd');
        $this->ProxyPasswd = $Settings->getValue();
        $Settings->setKey('ProxyOnlyWithYTDL');
        $this->ProxyOnlyWithYTDL = $Settings->getValue();
        $this->ProxyOnlyWithYTDL = is_null($this->ProxyOnlyWithYTDL)?false:(strcmp($this->ProxyOnlyWithYTDL, 'Y') == 0);
        $Settings->setKey('WhichDownloader');
        $this->WhichDownloader = $Settings->getValue();
        $this->WhichDownloader = is_null($this->WhichDownloader)
            ?0:(strcmp($this->WhichDownloader, 'ARIA2') == 0 ? 0 : 1); // 0 means ARIA2, 1 means CURL
        $Settings->setKey('MaxDownloadSpeed');
        $this->MaxDownloadSpeed = $Settings->getValue();
        $Settings->setKey('AllowProtocolHTTP');
        $this->AllowProtocolHTTP = $Settings->getValue();
        $this->AllowProtocolHTTP = is_null($this->AllowProtocolHTTP)?true:strcmp($this->AllowProtocolHTTP, 'Y') == 0;

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
        header( 'Content-Type: application/json; charset=utf-8');

        if (isset($_POST['FILE']) && strlen($_POST['FILE']) > 0 && Tools::checkURL($_POST['FILE'])
            && isset($_POST['OPTIONS'])) {
            try {
                if (!$this->AllowProtocolHTTP && !\OC_User::isAdminUser($this->CurrentUID)) {
                    throw new \Exception((string)$this->L10N->t('You are not allowed to use the HTTP protocol'));
                }

                $Target=parse_url($_POST['FILE'], PHP_URL_PATH);
                $Target = Tools::cleanString(substr($Target, strrpos($Target, '/') + 1));

                // If target file exists, create a new one
                if (\OC\Files\Filesystem::file_exists($this->DownloadsFolder . '/' . $Target)) {
                    $Target = time() . '_' . $Target;
                }

                // Download in the user root folder
                $OPTIONS = array('dir' => $this->AbsoluteDownloadsFolder, 'out' => $Target, 'follow-torrent' => false);
                if (isset($_POST['OPTIONS']['HTTPUser']) && strlen(trim($_POST['OPTIONS']['HTTPUser'])) > 0
                    && isset($_POST['OPTIONS']['HTTPPasswd']) && strlen(trim($_POST['OPTIONS']['HTTPPasswd'])) > 0) {
                    $OPTIONS['http-user'] = $_POST['OPTIONS']['HTTPUser'];
                    $OPTIONS['http-passwd'] = $_POST['OPTIONS']['HTTPPasswd'];
                }
                if (isset ($_POST['OPTIONS']['HTTPReferer']) && strlen (trim ($_POST['OPTIONS']['HTTPReferer'])) > 0) {
                              $OPTIONS['referer'] = $_POST['OPTIONS']['HTTPReferer'];
                     }
                if (isset ($_POST['OPTIONS']['HTTPUseragent']) && strlen (trim ($_POST['OPTIONS']['HTTPUseragent'])) > 0) {
                              $OPTIONS['user-agent'] = $_POST['OPTIONS']['HTTPUseragent'];
                    }
                if (isset ($_POST['OPTIONS']['HTTPOutfilename']) && strlen (trim ($_POST['OPTIONS']['HTTPOutfilename'])) > 0) {
                                 $OPTIONS['out'] = $_POST['OPTIONS']['HTTPOutfilename'];
                    }
                if (!$this->ProxyOnlyWithYTDL && !is_null($this->ProxyAddress)
                    && $this->ProxyPort > 0 && $this->ProxyPort <= 65536) {
                    $OPTIONS['all-proxy'] = rtrim($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
                    if (!is_null($this->ProxyUser) && !is_null($this->ProxyPasswd)) {
                        $OPTIONS['all-proxy-user'] = $this->ProxyUser;
                        $OPTIONS['all-proxy-passwd'] = $this->ProxyPasswd;
                    }
                }
                if (!is_null($this->MaxDownloadSpeed) && $this->MaxDownloadSpeed > 0) {
                    $OPTIONS['max-download-limit'] = $this->MaxDownloadSpeed . 'K';
                }

                $AddURI =(
                    $this->WhichDownloader == 0
                    ?Aria2::addUri(array($_POST['FILE']), array('Params' => $OPTIONS))
                    : CURL::addUri($_POST['FILE'], $OPTIONS)
                );

                if (isset($AddURI['result']) && !is_null($AddURI['result'])) {
                    $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue`
                        (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `STATUS`, `TIMESTAMP`) VALUES(?, ?, ?, ?, ?, ?)';
                    if ($this->DbType == 1) {
                        $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue
                            ("UID", "GID", "FILENAME", "PROTOCOL", "STATUS", "TIMESTAMP") VALUES(?, ?, ?, ?, ?, ?)';
                    }

                    $Query = \OC_DB::prepare($SQL);
                    $Result = $Query->execute(array(
                        $this->CurrentUID,
                        $AddURI['result'],
                        $Target,
                        strtoupper(substr($_POST['FILE'], 0, strpos($_POST['FILE'], ':'))),
                        1,
                        time()
                    ));

                    sleep(1);
                    $Status =(
                        $this->WhichDownloader == 0
                        ?Aria2::tellStatus($AddURI['result'])
                        :CURL::tellStatus($AddURI['result'])
                    );

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
                            ?Tools::formatSizeUnits($Status['result']['downloadSpeed']).'/s'
                            :(string)$this->L10N->t('N/A'),
                        'FILENAME' => $Target,
                        'FILENAME_SHORT' => Tools::getShortFilename($Target),
                        'PROTO' => strtoupper(substr($_POST['FILE'], 0, strpos($_POST['FILE'], ':'))),
                        'ISTORRENT' => false
                    ));
                } else {
                    return new JSONResponse(
                        array(
                            'ERROR' => true,
                            'MESSAGE' =>(string)$this->L10N->t(
                                $this->WhichDownloader == 0
                                ?'Returned GID is null ! Is Aria2c running as a daemon ?'
                                : 'An error occurred while running the CURL download'
                            )
                        )
                    );
                }
            } catch (Exception $E) {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
            }
        } else {
            return new JSONResponse(
                array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Please check the URL you\'ve just provided'))
            );
        }
    }
}
