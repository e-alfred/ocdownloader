<?php



namespace OCA\ocDownloader\Backend;

use OCA\ocDownloader\Lib\Settings;
use OCA\ocDownloader\Lib\Tools;
use OCA\ocDownloader\Lib\Aria2;
use OCA\ocDownloader\Lib\Curl;

use OCA\ocDownloader\Service\DBService;
use OCP\IL10N;
use OCP\IUserSession;
/**
 * Extends Backend
 */
abstract class IBackendDownloader {

use \OCA\ocDownloader\Lib\IdentifierTrait;

public $name;

public $pattern;

protected $settings;

protected $L10N;

protected $DBService;
var $options;

var $uri;

var $WhichDownloader;
var $AbsoluteDownloadsFolder;

var $CurrentUID = NULL;

var $DbType = 0;

CONST DOWNLOADER_ARIA = 0;
CONST DOWNLOADER_CURL = 1;

public function __construct(IUserSession $userSession, DBService $dbservice, IL10N $L10N = NULL) {

  $this->WhichDownloader = self::DOWNLOADER_ARIA;
  $this->DownloadsFolder = "/Downloads";
  #FIXME: Files PATH.gl.
  $this->AbsoluteDownloadsFolder = $userSession->getUser()->getHome().'/files'.$this->DownloadsFolder;
  $this->CurrentUID = $userSession->getUser()->getUID();
  $this->DBService = $dbservice;

  $this->L10N = $L10N;
  $this->setIdentifier($this->name);

}

  /**
   * Return regex text
   *  @return string
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * Return options for this backend
   * @return array of options
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * Return true if the URI can be handled by the backend
   * @param mixed $URI
   * @return boolean [description]
   */
  public function checkURI($URI) {
    $ret = preg_match ($this->pattern, $URI, $Matches);
    if ($ret  || count ($Matches) === 1)
    {
      return true;
    }

  return false;
  }

  public function checkURL($URL){
    return $this->checkURI($URL);
  }

  public function getInfo($URI) {
    return NULL;
  }

  public function parseOptions($OPTIONS) {
    $OPTS = $this->getOptions();
    foreach ($OPTS as $k => $v) {
      $o = 'option-'.$v[0];
      $OPTS[$k]['value'] = $OPTIONS[$o];
    }

    return (!$OPTS) ? Array() : $OPTS;
  }

  public function add($URI, $OPTIONS) {
    try {
      $DL = $this->getInfo($URI);
      $OPTIONS = $this->parseOptions($OPTIONS);
      $OPTIONS['PROTOCOL'] = $this->getIdentifier();


      // Set global options
      $OPTIONS = array_merge(
        $OPTIONS,
        Array (
  			     'dir' => $this->AbsoluteDownloadsFolder,
  			     'out' => $DL['FILENAME'],
  					 'follow-torrent' => $this->reqfollow()
  			)
      );

    if (!is_null ($this->getConfig('ProxyAddress')) && $this->getConfig('ProxyPort') > 0 && $this->getConfig('ProxyPort') <= 65536)
        {
            $OPTIONS['all-proxy'] = rtrim ($this->getConfig('ProxyAddress'), '/') . ':' . $this->getConfig('ProxyPort');
            if (!is_null ($this->getConfig('ProxyUser')) && !is_null ($this->getConfig('ProxyPasswd')))
              {
              $OPTIONS['all-proxy-user'] = $this->getConfig('ProxyUser');
              $OPTIONS['all-proxy-passwd'] = $this->getConfig('ProxyPasswd');
              }
        }

      // call backend
      $AddURI = $this->addUriHandler($URI, $OPTIONS);
      $OPTIONS['result'] = $AddURI['result'];
      $this->DBService->addQueue($this->CurrentUID, $OPTIONS);
      
      
      return  $DL;
  } catch (Exception $E)
    {
      throw new BackendException('Unabletolaunchthedownload');
    }

    #FIXME: use custom exception
    // \Exception('NotImplementedYet');
    return false;
  }


  public function isAllowed() {
    return false;
  }

  public function reqFollow() {
    return false;
  }

  public function addUriHandler($URI, $OPTIONS) {
    return ($this->WhichDownloader == 0 ?
      Aria2::AddUri (Array ($URI), Array ('Params' => $OPTIONS)) :
      Curl::AddUri ($URI, $OPTIONS));
  }

  public function getConfig($key) {

    // try get cached
    if (isset($this->settings[$key]))
      return $this->settings[$key];

    $Settings = new Settings ();

    $keys = Array(
       'ProxyAddress',
       'ProxyPort',
       'ProxyUser',
       'ProxyPasswd',
       'WhichDownloader',
       'DownloadsFolder'
     );

     if  (!in_array($key,$keys)) {
       return NULL;
     }

     if ('DownloadsFolder' === $key) {
       $Settings->SetTable ('personal');
       $Settings->SetUID ($this->CurrentUID);
       $Settings->SetKey ('DownloadsFolder');
       $this->DownloadsFolder = $this->settings->GetValue ();
     }

    $Settings->SetKey ($key);
    $ret = $Settings->GetValue();

    // save cache
    $this->settings[$key] = $ret;

    return $ret;
  }

  public function getStatus($GID) {
    return ($this->WhichDownloader == 0 ? Aria2::TellStatus ($GID) : CURL::TellStatus ($GID));
  }

}
