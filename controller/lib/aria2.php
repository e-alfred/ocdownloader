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

class Aria2
{
    private static $Server = 'http://127.0.0.1:6800/jsonrpc';
    private static $CurlHandler;
    
    public static function __callStatic ($Name, $Args)
    {
        if (isset ($Args['Server']) && !is_null ($Args['Server']))
        {
            self::$Server = $Args['Server'];
        }
        
        self::Load ();
        
        $Data = Array (
            'jsonrpc'   => '2.0',
            'id'        => 'ocdownloader',
            'method'    => 'aria2.' . lcfirst ($Name),
            'params'    =>  $Args['Params']
        );
        
        return json_decode (self::Request ($Data), 1);
    }
    
    /********** PRIVATE STATIC METHODS **********/
	private static function Load ()
    {
        self::$CurlHandler = curl_init (self::$Server);
        
        curl_setopt_array (self::$CurlHandler, Array (
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ));
    }
    
    private static function Request ($Data)
    {
        curl_setopt (self::$CurlHandler, CURLOPT_POSTFIELDS, json_encode ($Data));
        $Data = curl_exec (self::$CurlHandler);
        curl_close (self::$CurlHandler);
        
        return $Data;
    }
}
?>