<?php


use OCA\ocDownloader\Lib\Backend\BackendExcention;
use OCA\ocDownloader\Backend\IBackendDownloader;

use OCP\IRequest;

namespace OCA\ocDownloader\Backend;

use OCA\ocDownloader\Lib\Youtube;
use OCA\ocDownloader\Lib\Tools;
use OCP\IL10N;  
use OCP\IUserSession;

class YTDLBackend extends IBackendDownloader {

  #FIXME: match all sites can be youtube-dl handle
  public $pattern = '%^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$%ui';

  public $name = "YTDL";

  public $YTDLBinary = '/usr/local/bin/youtube-dl';


  public function __construct(IUserSession $userSession, IL10N $L10N) {
    $this->setIdentifier($this->name);

    $this->options = Array(
        array('yt-extractaudio', 'checkbox', 'Only Extract audio ?', 'No post-processing, just extract the best audio quality'),
        array('yt-foceipv4', 'checkbox', 'Force IPv4 ?'),
      );
    //$home = $userSession->getUser()->getHome();
    parent::__construct($userSession, $L10N);

  }

  function no__construct() {
    $this->YTDLBinary = $YTDLBinary;

  }

  function getOptions() {
    return  Array(
        array('yt-extractaudio', 'checkbox', 'Only Extract audio ?', 'No post-processing, just extract the best audio quality'),
        array('yt-foceipv4', 'checkbox', 'Force IPv4 ?'),
      );
  }

  function getInfo($URI) {
    $YouTube = new Youtube($this->YTDLBinary, $URI);

  /*  if (!is_null (self::$ProxyAddress) && self::$ProxyPort > 0 && self::$ProxyPort <= 65536)
    {
          $YouTube->SetProxy (self::$ProxyAddress, self::$ProxyPort);
    } */

    $VideoData = $YouTube->GetVideoData ();
    if (!isset ($VideoData['VIDEO']) || !isset ($VideoData['FULLNAME']))
    {
       throw new BackendException('UnabletoretrievetrueYouTubevideoURL');
          //return Array ('ERROR' => true, 'MESSAGE' => 'UnabletoretrievetrueYouTubevideoURL');
    }
    $DL = Array (
        //  'URL' => $VideoData['VIDEO'],
          'FILENAME' => Tools::CleanString ($VideoData['FULLNAME']),
          'PROTO' => 'Video'
    );
    return $DL;
  }

  public function addUriHandler ($URI, $OPTIONS) {

    $onlyAudio = false;
    $ipv4 = false;

    #$URI = $request->get('URI');
    //$OPTIONS = parent::parseOptions($OPTIONS);
    $YouTube = new Youtube ($this->YTDLBinary, $URI);

    if (!is_null ($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536)
    {
          $YouTube->SetProxy ($this->ProxyAddress, $this->ProxyPort);
    }

    foreach ($OPTIONS as $o) {
      $k = $o[0];
      switch ($k) {
        case 'yt-foceipv4':
          $ipv4 = ($o['value'] == 'on' ? true : false);
          break;
        case 'yt-extractaudio':
          $onlyAudio = ($o['value'] == 'on' ? true : false);
          break;
      }
    }

     // Extract Audio YES
    $YouTube->SetForceIPv4($ipv4);
    $data = $YouTube->GetVideoData ($onlyAudio);
    $k = ($onlyAudio ? 'AUDIO' : 'VIDEO');
    if (!isset ($data[$k]) || !isset ($data['FULLNAME'])) {
      throw new BackendException('Unable to retrieve true YouTube URL');
    }

    $URI = $data[$k];
    $OPTIONS['out'] = Tools::CleanString ($data['FULLNAME']);
    $OPTIONS['TYPE'] = 'YT '.ucfirst($k);


    return parent::addUriHandler($URI, $OPTIONS);
  }
}
