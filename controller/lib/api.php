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

use \OCP\Config;

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
	
	public static function Add ($URL)
	{
            try
            {
                  self::Load ();
                
                  $URL = urldecode ($URL);
                  if (Tools::CheckURL ($URL))
                  {
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
                        }
                        else
                        {
                              if (!self::$AllowProtocolHTTP && !\OC_User::isAdminUser (self::$CurrentUID) && Tools::StartsWith (strtolower ($URL), 'http'))
                              {
                                    return Array ('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolhttp');
                              }
                              elseif (!self::$AllowProtocolFTP && !\OC_User::isAdminUser (self::$CurrentUID) && Tools::StartsWith (strtolower ($URL), 'ftp'))
                              {
                                    return Array ('ERROR' => true, 'MESSAGE' => 'Notallowedtouseprotocolftp');
                              }
                              
                              $DL = Array (
                                    'URL' => $URL,
                                    'FILENAME' => Tools::CleanString (substr($URL, strrpos($URL, '/') + 1)),
                                    'PROTO' => strtoupper (substr ($URL, 0, strpos ($URL, ':')))
                              );
                        }
                        
                        
                        $OPTIONS = Array ('dir' => self::$AbsoluteDownloadsFolder, 'out' => $DL['FILENAME'], 'follow-torrent' => false);
                        if (!is_null (self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536)
                        {
                              $OPTIONS['all-proxy'] = rtrim (self::$ProxyAddress, '/') . ':' . self::$ProxyPort;
                              if (!is_null (self::$ProxyUser) && !is_null (self::$ProxyPasswd))
                              {
                                    $OPTIONS['all-proxy-user'] = self::$ProxyUser;
                                    $OPTIONS['all-proxy-passwd'] = self::$ProxyPasswd;
                              }
                        }
                        
                        $AddURI = (self::$WhichDownloader == 0 ? Aria2::AddUri (Array ($DL['URL']), Array ('Params' => $OPTIONS)) : CURL::AddUri ($DL['URL'], $OPTIONS));
                        
                        if (isset ($AddURI['result']) && !is_null ($AddURI['result']))
                        {
                              $SQL = 'INSERT INTO `*PREFIX*ocdownloader_queue` (`UID`, `GID`, `FILENAME`, `PROTOCOL`, `IS_CLEANED`, `STATUS`, `TIMESTAMP`) VALUES (?, ?, ?, ?, ?, ?, ?)';
                              if (self::$DbType == 1)
                              {
                                    $SQL = 'INSERT INTO *PREFIX*ocdownloader_queue ("UID", "GID", "FILENAME", "PROTOCOL", "IS_CLEANED", "STATUS", "TIMESTAMP") VALUES (?, ?, ?, ?, ?, ?, ?)';
                              }
                              
                              $Query = \OCP\DB::prepare ($SQL);
                              $Result = $Query->execute (Array (
                                    self::$CurrentUID,
                                    $AddURI['result'],
                                    $DL['FILENAME'],
                                    (strcmp ($DL['PROTO'], 'Video') == 0 ? 'YT ' . (string)self::$L10N->t ('Video') : $DL['PROTO']),
                                    1, 1,
                                    time()
                              ));
                              
                              return Array ('ERROR' => false, 'FILENAME' => $DL['FILENAME']);
                        }
                        else
                        {
                              return Array ('ERROR' => true, 'MESSAGE' => 'ReturnedGIDisnullIsAria2crunningasadaemon');
                        }
                  }
                  else
                  {
                        return Array ('ERROR' => true, 'MESSAGE' => 'InvalidURL');
                  }
            }
            catch (Exception $E)
            {
                  return Array ('ERROR' => true, 'MESSAGE' => 'Unabletolaunchthedownload');
            }
	}
      

	public static function Handler ($URI)
	{
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

      public static function CheckAddonVersion ($Version)
	{
            $AppVersion = Config::getAppValue ('ocdownloader', 'installed_version');
            return Array ('RESULT' => version_compare ($Version, $AppVersion, '<='));
      }
      
      public static function GetQueue ()
      {
            self::Load ();
            
            try
            {
                  $Params = Array (self::$CurrentUID);
                  $StatusReq = '(?, ?, ?, ?, ?)';
                  $Params[] = 0; $Params[] = 1; $Params[] = 2; $Params[] = 3; $Params[] = 4;
                  $IsCleanedReq = '(?, ?)';
                  $Params[] = 0; $Params[] = 1;
                        
                  $SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue` WHERE `UID` = ? AND `STATUS` IN ' . $StatusReq . ' AND `IS_CLEANED` IN ' . $IsCleanedReq . ' ORDER BY `TIMESTAMP` ASC';
                  if (self::$DbType == 1)
                  {
                        $SQL = 'SELECT * FROM *PREFIX*ocdownloader_queue WHERE "UID" = ? AND "STATUS" IN ' . $StatusReq . ' AND "IS_CLEANED" IN ' . $IsCleanedReq . ' ORDER BY "TIMESTAMP" ASC';
                  }
                  $Query = \OCP\DB::prepare ($SQL);
                  $Request = $Query->execute ($Params);
                  
                  $Queue = [];
                  
                  while ($Row = $Request->fetchRow ())
                  {
                        $Status = (self::$WhichDownloader == 0 ? Aria2::TellStatus ($Row['GID']) : CURL::TellStatus ($Row['GID']));
                        $DLStatus = 5; // Error
                        
                        if (!is_null ($Status))
                        {
                              if (!isset ($Status['error']))
                              {
                                    $Progress = 0;
                                    if ($Status['result']['totalLength'] > 0)
                                    {
                                          $Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
                                    }
                                    
                                    $DLStatus = Tools::GetDownloadStatusID ($Status['result']['status']);
                                    $ProgressString = Tools::GetProgressString ($Status['result']['completedLength'], $Status['result']['totalLength'], $Progress);
                                    
                                    $Queue[] = Array (
                                          'GID' => $Row['GID'],
                                          'PROGRESSVAL' => round((($Progress) * 100), 2),
                                          'PROGRESS' => Array (
                                                'Message' => null,
                                                'ProgressString' => is_null ($ProgressString) ? 'N_A' : $ProgressString,
                                                'NumSeeders' => isset ($Status['result']['bittorrent']) && $Progress < 1 ? $Status['result']['numSeeders'] : null,
                                                'UploadLength' => isset ($Status['result']['bittorrent']) && $Progress == 1 ? Tools::FormatSizeUnits ($Status['result']['uploadLength']) : null,
                                                'Ratio' => isset ($Status['result']['bittorrent']) ? round (($Status['result']['uploadLength'] / $Status['result']['completedLength']), 2) : null
                                          ),
                                          'STATUS' => Array (
                                                'Value' => isset ($Status['result']['status']) ? ($Row['STATUS'] == 4 ? 'Removed' : ucfirst ($Status['result']['status'])) : 'N_A',
                                                'Seeding' => isset ($Status['result']['bittorrent']) && $Progress == 1 && $DLStatus != 3 ? true : false
                                          ),
                                          'STATUSID' => $Row['STATUS'] == 4 ? 4 : $DLStatus,
                                          'SPEED' => isset ($Status['result']['downloadSpeed']) ? ($Progress == 1 ? (isset ($Status['result']['bittorrent']) ? ($Status['result']['uploadSpeed'] == 0 ? '--' : Tools::FormatSizeUnits ($Status['result']['uploadSpeed']) . '/s') : '--') : ($DLStatus == 4 ? '--' : Tools::FormatSizeUnits ($Status['result']['downloadSpeed']) . '/s')) : 'N_A',
                                          'FILENAME' => $Row['FILENAME'],
                                          'PROTO' => $Row['PROTOCOL'],
                                          'ISTORRENT' => isset ($Status['result']['bittorrent']),
                                    );
                                    
                                    if ($Row['STATUS'] != $DLStatus)
                                    {
                                          $SQL = 'UPDATE `*PREFIX*ocdownloader_queue` SET `STATUS` = ? WHERE `UID` = ? AND `GID` = ? AND `STATUS` != ?';
                                          if (self::$DbType == 1)
                                          {
                                                $SQL = 'UPDATE *PREFIX*ocdownloader_queue SET "STATUS" = ? WHERE "UID" = ? AND "GID" = ? AND "STATUS" != ?';
                                          }
                                          
                                          $Query = \OCP\DB::prepare ($SQL);
                                          $Result = $Query->execute (Array (
                                                $DLStatus,
                                                self::$CurrentUID,
                                                $Row['GID'],
                                                4
                                          ));
                                    }
                              }
                              else
                              {
                                    $Queue[] = Array (
                                          'GID' => $Row['GID'],
                                          'PROGRESSVAL' => 0,
                                          'PROGRESS' => Array (
                                                'Message' => 'ErrorGIDnotfound',
                                                'ProgressString' => null,
                                                'NumSeeders' => null,
                                                'UploadLength' => null,
                                                'Ratio' => null
                                          ),
                                          'STATUS' => Array(
                                                'Value' => 'N_A',
                                                'Seeding' => null
                                          ),
                                          'STATUSID' => $DLStatus,
                                          'SPEED' => 'N_A',
                                          'FILENAME' => $Row['FILENAME'],
                                          'PROTO' => $Row['PROTOCOL'],
                                          'ISTORRENT' => isset ($Status['result']['bittorrent'])
                                    );
                              }
                        }
                        else
                        {
                              $Queue[] = Array (
                                    'GID' => $Row['GID'],
                                    'PROGRESSVAL' => 0,
                                    'PROGRESS' => Array (
                                          'Message' => self::$WhichDownloader == 0 ? 'ReturnedstatusisnullIsAria2crunningasadaemon' : 'Unabletofinddownloadstatusfile',
                                          'ProgressString' => null,
                                          'NumSeeders' => null,
                                          'UploadLength' => null,
                                          'Ratio' => null
                                    ),
                                    'STATUS' => Array(
                                          'Value' => 'N_A',
                                          'Seeding' => null
                                    ),
                                    'STATUSID' => $DLStatus,
                                    'SPEED' => 'N_A',
                                    'FILENAME' => $Row['FILENAME'],
                                    'PROTO' => $Row['PROTOCOL'],
                                    'ISTORRENT' => isset ($Status['result']['bittorrent'])
                              );
                        }
                  }
                  return Array ('ERROR' => false, 'MESSAGE' => null, 'QUEUE' => $Queue, 'COUNTER' => Tools::GetCounters (self::$DbType, self::$CurrentUID));
            }
            catch (Exception $E)
            {
                  return Array ('ERROR' => true, 'MESSAGE' => $E->getMessage (), 'QUEUE' => null, 'COUNTER' => null);
            }
      }
	
	/********** PRIVATE STATIC METHODS **********/
	private static function Load ()
	{
		if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  self::$DbType = 1;
            }
            
            self::$CurrentUID = \OC::$server->getUserSession ()->getUser ();
            self::$CurrentUID = (self::$CurrentUID) ? self::$CurrentUID->getUID () : '';
            
            self::$L10N = \OC::$server->getL10N ('ocdownloader');
            
            $Settings = new Settings ();
            
            $Settings->SetKey ('ProxyAddress');
            self::$ProxyAddress = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPort');
            self::$ProxyPort = intval ($Settings->GetValue ());
            $Settings->SetKey ('ProxyUser');
            self::$ProxyUser = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPasswd');
            self::$ProxyPasswd = $Settings->GetValue ();
            $Settings->SetKey ('WhichDownloader');
            self::$WhichDownloader = $Settings->GetValue ();
            self::$WhichDownloader = is_null (self::$WhichDownloader) ? 0 : (strcmp (self::$WhichDownloader, 'ARIA2') == 0 ? 0 : 1); // 0 means ARIA2, 1 means CURL
            
            $Settings->SetKey ('AllowProtocolHTTP');
            self::$AllowProtocolHTTP = $Settings->GetValue ();
            self::$AllowProtocolHTTP = is_null (self::$AllowProtocolHTTP) ? true : strcmp (self::$AllowProtocolHTTP, 'Y') == 0;
            $Settings->SetKey ('AllowProtocolFTP');
            self::$AllowProtocolFTP = $Settings->GetValue ();
            self::$AllowProtocolFTP = is_null (self::$AllowProtocolFTP) ? true : strcmp (self::$AllowProtocolFTP, 'Y') == 0;
            $Settings->SetKey ('AllowProtocolYT');
            self::$AllowProtocolYT = $Settings->GetValue ();
            self::$AllowProtocolYT = is_null (self::$AllowProtocolYT) ? true : strcmp (self::$AllowProtocolYT, 'Y') == 0;
            $Settings->SetKey ('AllowProtocolBT');
            self::$AllowProtocolBT = $Settings->GetValue ();
            self::$AllowProtocolBT = is_null (self::$AllowProtocolBT) ? true : strcmp (self::$AllowProtocolBT, 'Y') == 0;
            
            $Settings->SetTable ('personal');
            $Settings->SetUID (self::$CurrentUID);
            $Settings->SetKey ('DownloadsFolder');
            self::$DownloadsFolder = $Settings->GetValue ();
            
            self::$DownloadsFolder = '/' . (is_null (self::$DownloadsFolder) ? 'Downloads' : self::$DownloadsFolder);
            self::$AbsoluteDownloadsFolder = \OC\Files\Filesystem::getLocalFolder (self::$DownloadsFolder);
            
            $Settings->SetKey ('YTDLBinary');
            $YTDLBinary = $Settings->GetValue ();
            
            self::$YTDLBinary = '/usr/local/bin/youtube-dl'; // default path
            if (!is_null ($YTDLBinary))
            {
                  self::$YTDLBinary = $YTDLBinary;
            }
	}
}