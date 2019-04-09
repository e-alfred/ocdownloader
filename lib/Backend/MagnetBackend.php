<?php

namespace OCA\ocDownloader\Backend;
#use OCA\ocDownloader\Backend\IBackendDownloader;

class MagnetBackend extends IBackendDownloader {

  public $name = "magnet";

  public $pattern = '%magnet:\?xt=urn:[a-z0-9]+:[a-z0-9]{32}%iu';

  function getInfo($URI) {
    parse_str(str_replace('tr=','tr[]=',parse_url($URI,PHP_URL_QUERY)),$query);
    $DL = Array(
      'URL' => $URI,
      'FILENAME' => $query['dn'],
      'PROTO' => 'TORRENT'
    );
    return $DL;

  }

  public function reqFollow() {
    return true;
  }

}
