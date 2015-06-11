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
	private static $CurrentUID = null;
      private static $L10N = null;
	
	public static function Download ($URL)
	{
            try
            {
                  self::Load ();
                
                  $URL = urldecode ($URL);
                  if (Tools::CheckURL ($URL))
                  {
                        if (preg_match ('/^https{0,1}:\/\/www\.youtube\.com\/watch\?v=.*$/', $URL) == 1)
                        {
                              $YouTube = new YouTube (self::$YTDLBinary, $URL);
                        
                              if (!is_null (self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536)
                              {
                                    $YouTube->SetProxy (self::$ProxyAddress, self::$ProxyPort);
                              }
                              
                              $VideoData = $YouTube->GetVideoData ();
                              if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
                              {
                                    return Array ('ERROR' => true, 'MESSAGE' => 'Unable to retrieve true YouTube video URL');
                              }
                              $DL = Array (
                                    'URL' => $VideoData['VIDEO'],
                                    'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']),
                                    'PROTO' => 'Video'
                              );
                        }
                        else
                        {
                              $DL = Array (
                                    'URL' => $URL,
                                    'FILENAME' => Tools::CleanString (substr($URL, strrpos($URL, '/') + 1)),
                                    'PROTO' => strtoupper(substr($URL, 0, strpos($URL, ':')))
                              );
                        }
                        
                        
                        $OPTIONS = Array ('dir' => self::$AbsoluteDownloadsFolder, 'out' => $DL['FILENAME']);
                        if (!is_null (self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536)
                        {
                              $OPTIONS['all-proxy'] = rtrim (self::$ProxyAddress, '/') . ':' . self::$ProxyPort;
                              if (!is_null (self::$ProxyUser) && !is_null (self::$ProxyPasswd))
                              {
                                    $OPTIONS['all-proxy-user'] = self::$ProxyUser;
                                    $OPTIONS['all-proxy-passwd'] = self::$ProxyPasswd;
                              }
                        }
                        
                        $Aria2 = new Aria2 ();
                        $AddURI = $Aria2->addUri (Array ($DL['URL']), $OPTIONS);
                        
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
                              return Array ('ERROR' => true, 'MESSAGE' => 'Returned GID is null ! Is Aria2c running as a daemon ?');
                        }
                  }
                  else
                  {
                        return Array ('ERROR' => true, 'MESSAGE' => 'Invalid URL');
                  }
            }
            catch (Exception $E)
            {
                  return Array ('ERROR' => true, 'MESSAGE' => 'Unable to launch the download');
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