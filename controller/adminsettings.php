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

use OCA\ocDownloader\Controller\Lib\Settings;
use OCA\ocDownloader\Controller\Lib\Tools;

class AdminSettings extends Controller
{
    private $DbType = 0;
    private $L10N;
    private $OCDSettingKeys = array(
        'YTDLBinary', 'YTDLAudioFormat', 'YTDLVideoFormat', 'ProxyAddress', 'ProxyPort', 'ProxyUser', 'ProxyPasswd', 'WhichDownloader',
        'ProxyOnlyWithYTDL', 'AllowProtocolHTTP', 'AllowProtocolFTP', 'AllowProtocolYT', 'AllowProtocolBT',
        'MaxDownloadSpeed', 'BTMaxUploadSpeed', 'AriaAddress', 'AriaPort', 'AriaToken'
    );
    private $Settings = null;

    public function __construct($AppName, IRequest $Request, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);

        if (strcmp(\OC::$server->getConfig()->getSystemValue('dbtype'), 'pgsql') == 0) {
            $this->DbType = 1;
        }

        $this->L10N = $L10N;

        $this->Settings = new Settings();
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function save()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        $Error = false;
        $Message = null;

        if (isset($_POST['KEY']) && strlen(trim($_POST['KEY'])) > 0 && isset($_POST['VAL'])
            && strlen(trim($_POST['VAL'])) >= 0) {
            $PostKey = $_POST['KEY'];
            $PostValue = $_POST['VAL'];

            if (in_array($PostKey, $this->OCDSettingKeys)) {
                $this->Settings->setKey($PostKey);

                if (strlen(trim($PostValue)) > 0) {
                    if (strcmp($PostKey, 'YTDLBinary') == 0) {
                        $PostValue = trim(str_replace(' ', '\ ', $PostValue));
                        if (!file_exists($PostValue)) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Unable to find YouTube-DL binary');
                        }
                    } elseif (strcmp($PostKey, 'YTDLAudioFormat') == 0) {
                    } elseif (strcmp($PostKey, 'YTDLVideoFormat') == 0) {
                    } elseif (strcmp($PostKey, 'ProxyAddress') == 0) {
                        if (!Tools::checkURL($PostValue) && strlen(trim($PostValue)) > 0) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Invalid proxy address URL');
                        }
                    } elseif (strcmp($PostKey, 'ProxyPort') == 0) {
                        if (!is_numeric($PostValue)) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Proxy port should be a numeric value');
                        } elseif ($PostValue <= 0 || $PostValue > 65536) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Proxy port should be a value from 1 to 65536');
                        }
                    } elseif (strcmp($PostKey, 'ProxyUser') == 0) {
                        $Error = false;
                    } elseif (strcmp($PostKey, 'ProxyPasswd') == 0) {
                        $Error = false;
                    } elseif (strcmp($PostKey, 'CheckForUpdates') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'Y';
                        }
                    } elseif (strcmp($PostKey, 'AriaAddress') == 0) {
                        if (!Tools::checkURL($PostValue) && strlen(trim($PostValue)) > 0) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Invalid Aria address URL');
                        }
                    } elseif (strcmp($PostKey, 'AriaPort') == 0) {
                        if (!is_numeric($PostValue)) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Aria port should be a numeric value');
                        } elseif ($PostValue <= 0 || $PostValue > 65536) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Aria port should be a value from 1 to 65536');
                        }
                    } elseif (strcmp($PostKey, 'AriaToken') == 0) {
                        $Error = false;
                    } elseif (strcmp($PostKey, 'WhichDownloader') == 0) {
                        if (!in_array($PostValue, array('ARIA2', 'CURL'))) {
                            $PostValue = 'ARIA2';
                        } elseif (strcmp($PostValue, 'ARIA2') != 0) {
                            Tools::resetAria2($this->DbType);
                        }
                    } elseif (strcmp($PostKey, 'ProxyOnlyWithYTDL') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'N';
                        }
                    } elseif (strcmp($PostKey, 'AllowProtocolHTTP') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'N';
                        }
                    } elseif (strcmp($PostKey, 'AllowProtocolFTP') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'N';
                        }
                    } elseif (strcmp($PostKey, 'AllowProtocolYT') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'N';
                        }
                    } elseif (strcmp($PostKey, 'AllowProtocolBT') == 0) {
                        if (!in_array($PostValue, array('Y', 'N'))) {
                            $PostValue = 'N';
                        }
                    } elseif (strcmp($PostKey, 'MaxDownloadSpeed') == 0) {
                        if (!is_numeric($PostValue)) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t('Max download speed setting should be a numeric value');
                        }
                    } elseif (strcmp($PostKey, 'BTMaxUploadSpeed') == 0) {
                        if (!is_numeric($PostValue)) {
                            $PostValue = null;
                            $Error = true;
                            $Message =(string)$this->L10N->t(
                                'BitTorrent protocol max upload speed setting should be a numeric value'
                            );
                        }
                    } else {
                        $PostValue = null;
                        $Error = true;
                    }
                }

                if ($this->Settings->checkIfKeyExists()) {
                    $this->Settings->updateValue($PostValue);
                } else {
                    $this->Settings->insertValue($PostValue);
                }
            }
        }

        return new JSONResponse(
            array('ERROR' => $Error, 'MESSAGE' => is_null($Message) ?(string)$this->L10N->t('Saved') : $Message)
        );
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function get()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        $AdminSettings = array();
        foreach ($_POST['KEYS'] as $PostKey) {
            if (in_array($PostKey, $this->OCDSettingKeys)) {
                $this->Settings->setKey($PostKey);
                $AdminSettings[$PostKey] = $this->Settings->getValue();

                // Set default if not set in the database
                if (is_null($AdminSettings[$PostKey])) {
                    switch ($PostKey) {
                        case 'YTDLBinary':
                            $AdminSettings[$PostKey] = '/usr/local/bin/youtube-dl';
                            break;
                        case 'YTDLAudioFormat':
                            $AdminSettings[$PostKey] = 'bestaudio';
                            break;
                        case 'YTDLVideoFormat':
                            $AdminSettings[$PostKey] = 'best[width<=1280]';
                            break;
                        case 'CheckForUpdates':
                            $AdminSettings[$PostKey] = 'Y';
                            break;
                        case 'WhichDownloader':
                            $AdminSettings[$PostKey] = 'ARIA2';
                            break;
                    }
                }
            }
        }

        return new JSONResponse(array('ERROR' => false, 'VALS' => $AdminSettings));
    }
}
