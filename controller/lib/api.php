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
    private static $DbType = 0;
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
      trigger_error('Deprecated: '.__CLASS__.'::'.__FUNCTION__, E_NOTICE);
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
                    $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue`
                        (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `IS_CLEANED`, `STATUS`, `TIMESTAMP`)
                        VALUES(?, ?, ?, ?, ?, ?, ?)';
                    if (self::$DbType == 1) {
                        $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue
                            ("UID", "GID", "FILENAME", "PROTOCOL", "IS_CLEANED", "STATUS", "TIMESTAMP")
                            VALUES(?, ?, ?, ?, ?, ?, ?)';
                    }

                    $Query = \OC_DB::prepare($SQL);
                    $Result = $Query->execute(array(
                        self::$CurrentUID,
                        $AddURI['result'],
                        $DL['FILENAME'],
                       (strcmp($DL['PROTO'], 'Video') == 0?'YT ' .(string)self::$L10N->t('Video'):$DL['PROTO']),
                        1, 1,
                        time()
                    ));

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
      trigger_error('Deprecated: '.__CLASS__.'::'.__FUNCTION__, E_NOTICE);
      $AppVersion = \OC::$server->getConfig()->getAppValue('ocdownloader', 'installed_version');
        return array('RESULT' => version_compare($Version, $AppVersion, '<='));
    }

    public static function getQueue()
    {
      trigger_error('Deprecated: '.__CLASS__.'::'.__FUNCTION__, E_NOTICE);  
    }
      

	public static function Handler ($URI)
	{
    trigger_error('Deprecated: '.__CLASS__.'::'.__FUNCTION__, E_NOTICE);
            try
            {
                  self::Load ();

                //  $URL = urldecode ($URI);
								$URL = $URI;
                  if (Tools::CheckURL ($URL))
                  {
										// FIXME: make handlers pluggeable.
										\OCP\JSON::setContentTypeHeader ('application/json');

										if (preg_match ('/^https{0,1}:\/\/www\.youtube\.com\/watch\?v=.*$/', $URL) == 1)
										{
													if (!self::$AllowProtocolYT && !\OC_User::isAdminUser (self::$CurrentUID))
													{
																return Array ('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolyt');
													}

													$YouTube = new YouTube (self::$YTDLBinary, $URL);

													if (!is_null (self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536)
													{
																$YouTube->SetProxy (self::$ProxyAddress, self::$ProxyPort);
													}

													$VideoData = $YouTube->GetVideoData ();
													if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
													{
																return Array ('ERROR' => true, 'MESSAGE' => 'UnabletoretrievetrueYouTubevideoURL');
													}
													$DL = Array (
																'URL' => $VideoData['VIDEO'],
																'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']),
																'PROTO' => 'Video'
													);
													return Array (
																'ERROR' => false,
																'HANDLER'  => 'youtube',
																'OPTIONS' => Array(
																		array('yt-extractaudio', 'checkbox', 'Only Extract audio ?', 'No post-processing, just extract the best audio quality'),
																		array('yt-foceipv4', 'checkbox', 'Force IPv4 ?'),
																	),
																'INFO'=> $DL,
															);
										}

										if (Tools::StartsWith (strtolower ($URL), 'http'))
										{
											if (!self::$AllowProtocolHTTP && !\OC_User::isAdminUser (self::$CurrentUID))
													return Array ('ERROR' => true, 'HANDLER' => 'http', 'MESSAGE' => 'Notallowedtouseprotocolhttp');

										return Array (
													'ERROR' => false,
													'HANDLER'  => 'http',
													'OPTIONS' => Array(
															array('http-user', 'text', 'Basic Auth User', 'Username'),
															array('http-pwd', 'password', 'Basic Auth Password', 'Password'),
															)
												);
											}

											if (Tools::StartsWith (strtolower ($URL), 'ftp'))
											{
												if (!self::$AllowProtocolFTP && !\OC_User::isAdminUser (self::$CurrentUID))
														return Array ('ERROR' => true, 'HANDLER' => 'ftp', 'MESSAGE' => 'Notallowedtouseprotocolftp');

											return Array (
														'ERROR' => false,
														'HANDLER'  => 'ftp',
														'OPTIONS' => Array(
																array('ftp-user', 'text', 'FTP User', 'Username'),
																array('ftp-pwd', 'password', 'FTP Password', 'Password'),
																array('ftp_pasv', 'checkbox', 'Passive Mode' ),
																)
													);
												}

												if (Tools::StartsWith (strtolower ($URL), 'magnet'))
												{
													if (!self::$AllowProtocolBT && !\OC_User::isAdminUser (self::$CurrentUID))
															return Array ('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolbt');

												parse_str(str_replace('tr=','tr[]=',parse_url($URL,PHP_URL_QUERY)),$query);
												return Array (
															'ERROR' => false,
															'HANDLER'  => 'magnet',
															'INFO' => $query
														);
													}
									return Array ('ERROR' => true, 'MESSAGE' => 'No Handler');

                  }

									return Array ('ERROR' => true, 'MESSAGE' => 'InvalidURL');
            }
            catch (Exception $E)
            {
                  return Array ('ERROR' => true, 'MESSAGE' => 'Unabletogethandler');
            }
	}

    /********** PRIVATE STATIC METHODS **********/
    private static function load()
    {
        if (strcmp(\OC::$server->getConfig()->getSystemValue('dbtype'), 'pgsql') == 0) {
            self::$DbType = 1;
        }

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
