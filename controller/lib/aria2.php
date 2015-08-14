<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Creative Commons BY-SA License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */
namespace OCA\ocDownloader\Controller\Lib;

class Aria2
{
    private static $Server = null;
    private static $CurlHandler;
    
    public static function __callStatic ($Name, $Args)
    {
        self::$Server = 'http://127.0.0.1:6800/jsonrpc';
        $Args = (strcmp ($Name, 'AddTorrent') == 0 ? self::RebuildTorrentArgs ($Args) : self::RebuildArgs ($Args));
        
        self::Load ();
        
        $Data = Array (
            'jsonrpc'   => '2.0',
            'id'        => 'ocdownloader',
            'method'    => 'aria2.' . lcfirst ($Name),
            'params'    => $Args
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
    
    private static function RebuildArgs ($Args)
    {
        if (isset ($Args[1]['Server']) && !is_null ($Args[1]['Server']))
        {
            self::$Server = $Args[1]['Server'];
        }
        
        $RebuildArgs = Array ($Args[0]);
        
        if (isset ($Args[1]['Params']))
        {
            $RebuildArgs[1] = $Args[1]['Params'];
        }
        
        return $RebuildArgs;
    }
    
    private static function RebuildTorrentArgs ($Args)
    {
        if (isset ($Args[2]['Server']) && !is_null ($Args[2]['Server']))
        {
            self::$Server = $Args[2]['Server'];
        }
        
        $RebuildArgs = Array ($Args[0], Array ());
        
        if (isset ($Args[2]['Params']))
        {
            $RebuildArgs[2] = $Args[2]['Params'];
        }
        
        return $RebuildArgs;
    }
}
?>