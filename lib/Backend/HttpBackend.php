<?php


namespace OCA\ocDownloader\Backend;

use OCA\ocDownloader\Lib\Tools;
use OCP\IRequest;
use OCP\IUserSession;

class HttpBackend extends IBackendDownloader {

  public $name = "HTTP";

  public $pattern = '%^(?:(?:https?)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';

  public function __construct(IUserSession $userSession) {

    $this->options =
        Array(
        array('http-user', 'text', 'Basic Auth User', 'Username'),
        array('http-pwd', 'password', 'Basic Auth Password', 'Password'),
      );

    parent::__construct($userSession);
  }

  public function getInfo($URL) {
    $DL = Array (
          'URL' => $URL,
          'FILENAME' => Tools::CleanString (substr($URL, strrpos($URL, '/') + 1)),
          'PROTO' => strtoupper (substr ($URL, 0, strpos ($URL, ':')))
    );
    return $DL;
  }

  public function no_add(IRequest $request) {
    $URL = $request->get('URL');
    $DL = Array (
          'URL' => $URL,
          'FILENAME' => Tools::CleanString (substr($URL, strrpos($URL, '/') + 1)),
          'PROTO' => strtoupper (substr ($URL, 0, strpos ($URL, ':')))
    );


  }
}
