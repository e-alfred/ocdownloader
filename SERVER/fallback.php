#!/usr/bin/php -q
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

class OCD
{
    private static $CurlHandler = null;
    private static $ProgressFile = null;
    private static $CHInfo = null;
    private static $PFHandle = null;
    
    public static function load($GID, $URI, $OPTIONS)
    {
        self::$ProgressFile = '/tmp/' . $GID . '.curl';
        self::$PFHandle = fopen(self::$ProgressFile, 'w+');
        
        self::$CurlHandler = curl_init();
        
        $DestFile = fopen(rtrim($OPTIONS['dir'], '/') . '/' . $OPTIONS['out'], 'w+');
        
        curl_setopt_array(self::$CurlHandler, array(
            CURLOPT_FILE => $DestFile,
            CURLOPT_URL => $URI
        ));
        self::curlSetBasicOptions();
        self::curlSetAdvancedOptions($OPTIONS);
        
        curl_exec(self::$CurlHandler);
        curl_close(self::$CurlHandler);
        fclose($DestFile);
    }
    
    private static function writeProgress($DownloadStatus = 0)
    {
        if (self::$CHInfo['download_content_length'] != -1 && is_resource(self::$PFHandle)) {
            $State = $DownloadStatus == 0 ? 'active' :($DownloadStatus == 1 ? 'complete' : 'error');
            $Downloaded = $DownloadStatus == 2 ? 0 : self::$CHInfo['size_download'];
            $Size = $DownloadStatus == 2 ? 0 : self::$CHInfo['download_content_length'];
            $Speed = $DownloadStatus == 2 ? 0 : self::$CHInfo['speed_download'];
            
            if (($PID = getmypid()) === false) {
                $PID = 0;
            }
            
            fwrite(self::$PFHandle, $State . ';' . $Downloaded . ';' . $Size . ';' . $Speed . ';' . $PID . "\n");
        }
    }
    
    private static function curlSetBasicOptions()
    {
        // Basic options
        curl_setopt_array(self::$CurlHandler, array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 20,
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function ($Resource, $TotalDL, $Downloaded, $UpSize, $Uploaded) {
                self::$CHInfo = curl_getinfo(self::$CurlHandler);
                
                if (self::$CHInfo['size_download'] == self::$CHInfo['download_content_length'] && self::$CHInfo['http_code'] == 200) {
                    self::writeProgress(1);
                    if (is_resource(self::$PFHandle)) {
                        fclose(self::$PFHandle);
                    }
                } else {
                    self::writeProgress();
                }
            },
        ));
    }
    
    private static function curlSetAdvancedOptions($OPTIONS)
    {
        if (isset($OPTIONS['http-user']) && isset($OPTIONS['http-passwd'])) {
            curl_setopt(self::$CurlHandler, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt(self::$CurlHandler, CURLOPT_USERPWD, $OPTIONS['http-user'] . ':' . $OPTIONS['http-passwd']);
        }
        if (isset($OPTIONS['ftp-user']) && isset($OPTIONS['ftp-passwd'])) {
            curl_setopt(self::$CurlHandler, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt(self::$CurlHandler, CURLOPT_USERPWD, $OPTIONS['ftp-user'] . ':' . $OPTIONS['ftp-passwd']);
        }
        if (isset($OPTIONS['ftp-pasv'])) {
            curl_setopt(self::$CurlHandler, CURLOPT_FTP_USE_EPSV, $OPTIONS['ftp-pasv']);
        }
        if (isset($OPTIONS['all-proxy'])) {
            curl_setopt(self::$CurlHandler, CURLOPT_PROXY, $OPTIONS['all-proxy']);
            if (isset($OPTIONS['all-proxy-user']) && isset($OPTIONS['all-proxy-passwd'])) {
                curl_setopt(self::$CurlHandler, CURLOPT_PROXYUSERPWD, $OPTIONS['all-proxy-user'] . ':' . $OPTIONS['all-proxy-passwd']);
            }
        }
        if (isset($OPTIONS['max-download-limit'])) {
            $OPTIONS['max-download-limit'] =(rtrim($OPTIONS['max-download-limit'], 'K')) * 1024;
            curl_setopt(self::$CurlHandler, CURLOPT_MAX_RECV_SPEED_LARGE, $OPTIONS['max-download-limit']);
        }
    }
}

set_time_limit(0);
OCD::load($argv[1], urldecode($argv[2]), json_decode(urldecode($argv[3]), true, 512, JSON_HEX_APOS | JSON_HEX_QUOT));
