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

class CURL
{
    private static $GID = null;
    private static $URI = null;
    private static $OPTIONS = null;
    
    public static function addUri($URI, $OPTIONS)
    {
        self::$GID = uniqid();
        self::$URI = $URI;
        self::$OPTIONS = $OPTIONS;
        
        self::run();
        
        return array('result' => self::$GID);
    }
    
    public static function tellStatus($GID)
    {
        if (file_exists('/tmp/' . $GID . '.curl')) {
            $Line = null;
    
            $TFHandle = fopen('/tmp/' . $GID . '.curl', 'r');
            $Cursor = -1;
    
            fseek($TFHandle, $Cursor, SEEK_END);
            $Char = fgetc($TFHandle);
            
            while ($Char === "\n" || $Char === "\r") {
                fseek($TFHandle, $Cursor--, SEEK_END);
                $Char = fgetc($TFHandle);
            }
            
            while ($Char !== false && $Char !== "\n" && $Char !== "\r") {
                $Line = $Char . $Line;
                fseek($TFHandle, $Cursor--, SEEK_END);
                $Char = fgetc($TFHandle);
            }
            
            $StatusArray = array(
                'status' => 'waiting',
                'completedLength' => 0,
                'totalLength' => 0,
                'downloadSpeed' => 0,
                'PID' => 0,
                'GID' => $GID
            );
            
            if (!is_null($Line)) {
                $Status = explode(';', $Line);
                if (count($Status) == 5) {
                    $StatusArray['status'] = $Status[0];
                    $StatusArray['completedLength'] = $Status[1];
                    $StatusArray['totalLength'] = $Status[2];
                    $StatusArray['downloadSpeed'] = $Status[3];
                    $StatusArray['PID'] = $Status[4];
                }
            }
            return array(
                'result' => $StatusArray
            );
        } else {
            return array(
                'error' => true
            );
        }
    }
    
    public static function remove($Status)
    {
        $Return = null;
        if (isset($Status['PID']) && is_numeric($Status['PID']) && isset($Status['GID'])) {
            if (posix_kill($Status['PID'], 15) === false) {
                $Return = null;
            }
            
            if (file_exists('/tmp/' . $Status['GID'] . '.curl')) {
                $PFHandle = fopen('/tmp/' . $Status['GID'] . '.curl', 'a');
                if (is_resource($PFHandle)) {
                    fwrite(
                        $PFHandle,
                        'removed;'.$Status['completedLength'].';'.$Status['totalLength'].';'.$Status['downloadSpeed']
                            .';'.$Status['PID']."\n"
                    );
                    fclose($PFHandle);
                    
                    $Return = array('result' => $Status['GID']);
                }
            }
        }
        return $Return;
    }
    
    public static function removeDownloadResult($GID)
    {
        if (file_exists('/tmp/' . $GID . '.curl')) {
            unlink('/tmp/' . $GID . '.curl');
        }
    }
    
    /********** PRIVATE STATIC METHODS **********/
    private static function run()
    {
        shell_exec(
            rtrim(dirname(__FILE__), '/') . '/../../SERVER/fallback.sh "' . self::$GID . '" "'
            .urlencode(self::$URI) . '" "' . urlencode(json_encode(self::$OPTIONS, JSON_HEX_APOS | JSON_HEX_QUOT)).'"'
        );
    }
}
