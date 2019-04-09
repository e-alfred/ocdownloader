<?php

namespace OCA\ocDownloader\Backend;

class FtpBackend extends HttpBackend {

  public $name = "FTP";

  public $pattern = '%^(?:(?:ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';

  function getOptions() {
    $OPTIONS = Array(
        array('ftp-user', 'text', 'FTP User', 'Username'),
        array('ftp-pwd', 'password', 'FTP Password', 'Password'),
        array('ftp_pasv', 'checkbox', 'Passive Mode' ),
      );
    return $OPTIONS;
  }

}
