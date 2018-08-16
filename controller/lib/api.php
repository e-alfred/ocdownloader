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

namespace OCA\ocDownloader\Controller\Lib;



use \OCA\ocDownloader\Controller\Lib\YouTube;
use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Tools;
use \OCA\ocDownloader\Controller\Lib\Settings;

class API
{
    private static $AbsoluteDownloadsFolder = null;
    private static $DownloadsFolder = null;
    private static $YTDLBinary = null;
    private static $ProxyAddress = null;
    private static $ProxyPort = 0;
    private static $ProxyUser = null;
    private static $ProxyPasswd = null;
    private static $WhichDownloader = 0;
    private static $CurrentUID = null;
    private static $L10N = null;
    private static $AllowProtocolHTTP = null;
    private static $AllowProtocolFTP = null;
    private static $AllowProtocolYT = null;
    private static $AllowProtocolBT = null;
    private static $MaxDownloadSpeed = null;

    public static function add($URL)
    {
        try {
            self::load();

            $URL = urldecode($URL);
            if (Tools::checkURL($URL)) {
                if (preg_match('/^https{0,1}:\/\/www\.youtube\.com\/watch\?v=.*$/', $URL) == 1) {
                    if (!self::$AllowProtocolYT && !\OC_User::isAdminUser(self::$CurrentUID)) {
                        return array('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolyt');
                    }

                    $YouTube = new YouTube(self::$YTDLBinary, $URL);

                    if (!is_null(self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536) {
                        $YouTube->setProxy(self::$ProxyAddress, self::$ProxyPort);
                    }

                    $VideoData = $YouTube->getVideoData();
                    if (!isset($VideoData['VIDEO']) || !isset($VideoData['FULLNAME'])) {
                        return array('ERROR' => true, 'MESSAGE' => 'UnabletoretrievetrueYouTubevideoURL');
                    }
                    $DL = array(
                        'URL' => $VideoData['VIDEO'],
                        'FILENAME' => Tools::cleanString($VideoData['FULLNAME']),
                        'PROTO' => 'Video'
                    );
                } else {
                    if (!self::$AllowProtocolHTTP && !\OC_User::isAdminUser(self::$CurrentUID)
                        && Tools::startsWith(strtolower($URL), 'http')) {
                        return array('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolhttp');
                    } elseif (!self::$AllowProtocolFTP && !\OC_User::isAdminUser(self::$CurrentUID)
                        && Tools::startsWith(strtolower($URL), 'ftp')) {
                        return array('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolftp');
                    }

                    $DL = array(
                        'URL' => $URL,
                        'FILENAME' => Tools::cleanString(substr($URL, strrpos($URL, '/') + 1)),
                        'PROTO' => strtoupper(substr($URL, 0, strpos($URL, ':')))
                    );
                }


                $OPTIONS = array(
                    'dir' => self::$AbsoluteDownloadsFolder,
                    'out' => $DL['FILENAME'],
                    'follow-torrent' => false
                );
                if (!is_null(self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536) {
                    $OPTIONS['all-proxy'] = rtrim(self::$ProxyAddress, '/') . ':' . self::$ProxyPort;
                    if (!is_null(self::$ProxyUser) && !is_null(self::$ProxyPasswd)) {
                        $OPTIONS['all-proxy-user'] = self::$ProxyUser;
                        $OPTIONS['all-proxy-passwd'] = self::$ProxyPasswd;
                    }
                }

                $AddURI =(
                    self::$WhichDownloader == 0
                        ?Aria2::addUri(array($DL['URL']), array('Params' => $OPTIONS))
                        :CURL::addUri($DL['URL'], $OPTIONS)
                );

                if (isset($AddURI['result']) && !is_null($AddURI['result'])) {
                  $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                    $qb->insert('ocdownloader_queue')
                        ->values([
                            'UID' => $qb->createNamedParameter($this->CurrentUID),
                            'GID' => $qb->createNamedParameter($AddURI['result']),
                            'FILENAME' => $qb->createNamedParameter($DL['FILENAME']),
                            'PROTOCOL' => $qb->createNamedParameter(strcmp($DL['PROTO'], 'Video') == 0?'YT ' .(string)self::$L10N->t('Video'):$DL['PROTO']),
                            'IS_CLEANED' => $qb->createNamedParameter(1),
                            'STATUS' => $qb->createNamedParameter(1),
                            'TIMESTAMP' => time(),
                            ]);
                    $qb->execute();

                    return array('ERROR' => false, 'FILENAME' => $DL['FILENAME']);
                } else {
                    return array('ERROR' => true, 'MESSAGE' => 'ReturnedGIDisnullIsAria2crunningasadaemon');
                }
            } else {
                return array('ERROR' => true, 'MESSAGE' => 'InvalidURL');
            }
        } catch (Exception $E) {
            return array('ERROR' => true, 'MESSAGE' => 'Unabletolaunchthedownload');
        }
    }

    public static function checkAddonVersion($Version)
    {
        $AppVersion = \OC::$server->getConfig()->getAppValue('ocdownloader', 'installed_version');
        return array('RESULT' => version_compare($Version, $AppVersion, '<='));
    }

    public static function getQueue()
    {
        self::load();

        try {
              $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
              $qb->select('*')->from('ocdownloader_queue')
                  ->where($qb->expr()->eq('UID',$qb->createNamedParameter($this->CurrentUID)))
                  ->orderBy('TIMESTAMP', 'ASC');
              $Request = $qb->execute();

            $DownloadUpdated = false;
            $Queue = [];

            while ($Row = $Request->fetch()) {
                $Status =(self::$WhichDownloader == 0?Aria2::tellStatus($Row['GID']):CURL::tellStatus($Row['GID']));
                $DLStatus = 5; // Error

                if (!is_null($Status)) {
                    if (!isset($Status['error'])) {
                        $Progress = 0;
                        if ($Status['result']['totalLength'] > 0) {
                            $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                        }

                        $DLStatus = Tools::getDownloadStatusID($Status['result']['status']);
                        $ProgressString = Tools::getProgressString(
                            $Status['result']['completedLength'],
                            $Status['result']['totalLength'],
                            $Progress
                        );

                        $Queue[] = array(
                            'GID' => $Row['GID'],
                            'PROGRESSVAL' => round((($Progress) * 100), 2),
                            'PROGRESS' => array(
                                'Message' => null,
                                'ProgressString' => is_null($ProgressString)?'N_A':$ProgressString,
                                'NumSeeders' => isset($Status['result']['bittorrent']) && $Progress < 1?$Status['result']['numSeeders']:null,
                                'UploadLength' => isset($Status['result']['bittorrent']) && $Progress == 1?Tools::formatSizeUnits($Status['result']['uploadLength']):null,
                                'Ratio' => isset($Status['result']['bittorrent'])?round(($Status['result']['uploadLength'] / $Status['result']['completedLength']), 2):null
                            ),
                            'STATUS' => array(
                                'Value' => isset($Status['result']['status']) ?($Row['STATUS'] == 4?'Removed':ucfirst($Status['result']['status'])):'N_A',
                                'Seeding' => isset($Status['result']['bittorrent']) && $Progress == 1 && $DLStatus != 3?true:false
                            ),
                            'STATUSID' => $Row['STATUS'] == 4?4:$DLStatus,
                            'SPEED' => isset($Status['result']['downloadSpeed'])
                                ?($Progress == 1
                                    ?(isset($Status['result']['bittorrent'])
                                        ?($Status['result']['uploadSpeed'] == 0
                                            ?'--'
                                            :Tools::formatSizeUnits($Status['result']['uploadSpeed']).'/s')
                                        :'--')
                                    :($DLStatus == 4
                                        ?'--'
                                        :Tools::formatSizeUnits($Status['result']['downloadSpeed']).'/s'))
                                :'N_A',
                            'FILENAME' => $Row['FILENAME'],
                            'PROTO' => $Row['PROTOCOL'],
                            'ISTORRENT' => isset($Status['result']['bittorrent']),
                        );

                        if ($Row['STATUS'] != $DLStatus) {
                          $qb->update('ocdownloader_queue')
                              ->set('STATUS', $qb->createNamedParameter($DLStatus))
                              ->where($qb->expr()->eq('UID', $qb->createNamedParameter(self::$CurrentUID)))
                              ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($Row['GID'])))
                              ->andwhere($qb->expr()->eq('STATUS', 4));
                          $qb->execute();
                        $DownloadUpdated = true;
                        }
                    } else {
                        $Queue[] = array(
                            'GID' => $Row['GID'],
                            'PROGRESSVAL' => 0,
                            'PROGRESS' => array(
                                'Message' => 'ErrorGIDnotfound',
                                'ProgressString' => null,
                                'NumSeeders' => null,
                                'UploadLength' => null,
                                'Ratio' => null
                            ),
                            'STATUS' => array(
                                'Value' => 'N_A',
                                'Seeding' => null
                            ),
                            'STATUSID' => $DLStatus,
                            'SPEED' => 'N_A',
                            'FILENAME' => $Row['FILENAME'],
                            'PROTO' => $Row['PROTOCOL'],
                            'ISTORRENT' => isset($Status['result']['bittorrent'])
                        );
                    }
                } else {
                    $Queue[] = array(
                        'GID' => $Row['GID'],
                        'PROGRESSVAL' => 0,
                        'PROGRESS' => array(
                            'Message' => self::$WhichDownloader == 0
                                ?'ReturnedstatusisnullIsAria2crunningasadaemon'
                                :'Unabletofinddownloadstatusfile',
                            'ProgressString' => null,
                            'NumSeeders' => null,
                            'UploadLength' => null,
                            'Ratio' => null
                        ),
                        'STATUS' => array(
                            'Value' => 'N_A',
                            'Seeding' => null
                        ),
                        'STATUSID' => $DLStatus,
                        'SPEED' => 'N_A',
                        'FILENAME' => $Row['FILENAME'],
                        'PROTO' => $Row['PROTOCOL'],
                        'ISTORRENT' => isset($Status['result']['bittorrent'])
                    );
                }
            }

			// Start rescan on update
			if ($DownloadUpdated) {
        \OC\Files\Filesystem::touch(self::$AbsoluteDownloadsFolder . $DL['FILENAME']);
			}

            return array(
                'ERROR' => false,
                'MESSAGE' => null,
                'QUEUE' => $Queue,
                'COUNTER' => Tools::getCounters(self::$CurrentUID)
            );
        } catch (Exception $E) {
            return array('ERROR' => true, 'MESSAGE' => $E->getMessage(), 'QUEUE' => null, 'COUNTER' => null);
        }
    }

    /********** PRIVATE STATIC METHODS **********/
    private static function load()
    {
        self::$CurrentUID = \OC::$server->getUserSession()->getUser();
        self::$CurrentUID =(self::$CurrentUID)?self::$CurrentUID->getUID():'';

        self::$L10N = \OC::$server->getL10N('ocdownloader');

        $Settings = new Settings();

        $Settings->setKey('ProxyAddress');
        self::$ProxyAddress = $Settings->getValue();
        $Settings->setKey('ProxyPort');
        self::$ProxyPort = intval($Settings->getValue());
        $Settings->setKey('ProxyUser');
        self::$ProxyUser = $Settings->getValue();
        $Settings->setKey('ProxyPasswd');
        self::$ProxyPasswd = $Settings->getValue();
        $Settings->setKey('WhichDownloader');
        self::$WhichDownloader = $Settings->getValue();
        self::$WhichDownloader = is_null(self::$WhichDownloader)?0 :(strcmp(self::$WhichDownloader, 'ARIA2') == 0?0:1); // 0 means ARIA2, 1 means CURL

        $Settings->setKey('AllowProtocolHTTP');
        self::$AllowProtocolHTTP = $Settings->getValue();
        self::$AllowProtocolHTTP = is_null(self::$AllowProtocolHTTP)?true:strcmp(self::$AllowProtocolHTTP, 'Y') == 0;
        $Settings->setKey('AllowProtocolFTP');
        self::$AllowProtocolFTP = $Settings->getValue();
        self::$AllowProtocolFTP = is_null(self::$AllowProtocolFTP)?true:strcmp(self::$AllowProtocolFTP, 'Y') == 0;
        $Settings->setKey('AllowProtocolYT');
        self::$AllowProtocolYT = $Settings->getValue();
        self::$AllowProtocolYT = is_null(self::$AllowProtocolYT)?true:strcmp(self::$AllowProtocolYT, 'Y') == 0;
        $Settings->setKey('AllowProtocolBT');
        self::$AllowProtocolBT = $Settings->getValue();
        self::$AllowProtocolBT = is_null(self::$AllowProtocolBT)?true:strcmp(self::$AllowProtocolBT, 'Y') == 0;

        $Settings->setTable('personal');
        $Settings->setUID(self::$CurrentUID);
        $Settings->setKey('DownloadsFolder');
        self::$DownloadsFolder = $Settings->getValue();

        self::$DownloadsFolder = '/' .(is_null(self::$DownloadsFolder)?'Downloads':self::$DownloadsFolder);
        self::$AbsoluteDownloadsFolder = \OC\Files\Filesystem::getLocalFolder(self::$DownloadsFolder);

        $Settings->setKey('YTDLBinary');
        $YTDLBinary = $Settings->getValue();

        self::$YTDLBinary = '/usr/local/bin/youtube-dl'; // default path
        if (!is_null($YTDLBinary)) {
            self::$YTDLBinary = $YTDLBinary;
        }
    }
}
