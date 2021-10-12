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

/**
 * @method static addTorrent($hash, $p1, $params)
 * @method static addUri($url, $params)
 * @method static forceRemove($gid)
 * @method static pause($gid)
 * @method static purgeDownloadResult()
 * @method static remove($gid)
 * @method static removeDownloadResult($gid)
 * @method static tellStatus($gid)
 * @method static unpause($gid)
 */
class Aria2
{
    private static $Server = null;
    private static $Token = null;
    private static $CurlHandler;

    public static function __callStatic($Name, $Args)
    {
        $Settings = new Settings();
        $Settings->setKey('AriaAddress');
        self::$Server = $Settings->getValue() ? $Settings->getValue() : '127.0.0.1';
        self::$Server .= ':';
        $Settings->setKey('AriaPort');
        self::$Server .= $Settings->getValue() ? $Settings->getValue() : '6800';
        self::$Server .= '/jsonrpc';

        $Args =(strcmp($Name, 'addTorrent') == 0 ? self::rebuildTorrentArgs($Args) : self::rebuildArgs($Args));

        $Settings->setKey('AriaToken');
        self::$Token = $Settings->getValue();

        if (!empty(self::$Token)) {
            self::$Token = 'token:' . self::$Token;
            array_unshift($Args, self::$Token);
        }

        self::load();

        $Data = array(
        'jsonrpc'   => '2.0',
        'id'      => 'ocdownloader',
        'method'    => 'aria2.' . lcfirst($Name),
        'params'    => $Args
        );

        return json_decode(self::request($Data), 1);
    }

    /********** PRIVATE STATIC METHODS **********/
    private static function load()
    {
        self::$CurlHandler = curl_init(self::$Server);

        curl_setopt_array(self::$CurlHandler, array(
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false
        ));
    }

    private static function request($Data)
    {
        curl_setopt(self::$CurlHandler, CURLOPT_POSTFIELDS, json_encode($Data));
        $Data = curl_exec(self::$CurlHandler);
        curl_close(self::$CurlHandler);

        return $Data;
    }

    private static function rebuildArgs($Args)
    {
        if (!is_array($Args)) {
            //TODO Nibbels: Test: Is this muting another fault or possible state of Args??
            //See https://github.com/e-alfred/ocdownloader/issues/11
            return array();
        }
        if (!count($Args)) {
            //TODO Nibbels: Test: Is this muting another fault or possible state of Args??
            //See https://github.com/e-alfred/ocdownloader/issues/11
            return array();
        }
        if (isset($Args[1]['Server']) && !is_null($Args[1]['Server'])) {
            self::$Server = $Args[1]['Server'];
        }

        $RebuildArgs = array($Args[0]);

        if (isset($Args[1]['Params'])) {
            $RebuildArgs[1] = $Args[1]['Params'];
        }

        return $RebuildArgs;
    }

    private static function rebuildTorrentArgs($Args)
    {
        if (!is_array($Args)) {
            //TODO Nibbels: Test: Is this muting another fault or possible state of Args??
            //See https://github.com/e-alfred/ocdownloader/issues/11
            return array();
        }
        if (!count($Args)) {
            //TODO Nibbels: Test: Is this muting another fault or possible state of Args??
            //See https://github.com/e-alfred/ocdownloader/issues/11
            return array();
        }
        if (isset($Args[2]['Server']) && !is_null($Args[2]['Server'])) {
            self::$Server = $Args[2]['Server'];
        }

        $RebuildArgs = array($Args[0], array());

        if (isset($Args[2]['Params'])) {
            $RebuildArgs[2] = $Args[2]['Params'];
        }

        return $RebuildArgs;
    }
}
