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
    private $Server;
    private $CurlHandler;
    
    public function __construct ($Server = 'http://127.0.0.1:6800/jsonrpc')
    {
        $this->Server = $Server;
        $this->CurlHandler = curl_init ($this->Server);
        
        curl_setopt_array ($this->CurlHandler, Array (
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false
        ));
    }
    
    public function __destruct ()
    {
        curl_close ($this->CurlHandler);
    }
    
    public function __call ($Name, $Arg)
    {
        $Data = Array (
            'jsonrpc'   => '2.0',
            'id'        => 'ocdownloader',
            'method'    => 'aria2.' . $Name,
            'params'    =>  $Arg
        );
        
        $Data = json_encode ($Data);
        return json_decode ($this->Request ($Data), 1);
    }
    
    private function Request ($Data)
    {
        curl_setopt ($this->CurlHandler, CURLOPT_POSTFIELDS, $Data);
        return curl_exec ($this->CurlHandler);
    }
}
?>