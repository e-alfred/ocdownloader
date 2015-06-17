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
      
namespace OCA\ocDownloader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Config;
use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\Settings;
use OCA\ocDownloader\Controller\Lib\Tools;

class AdminSettings extends Controller
{
      private $DbType = 0;
      private $L10N;
      private $OCDSettingKeys = Array ('YTDLBinary', 'ProxyAddress', 'ProxyPort', 'ProxyUser', 'ProxyPasswd', 'CheckForUpdates', 'WhichDownloader');
      private $Settings = null;
      
      public function __construct ($AppName, IRequest $Request, IL10N $L10N)
      {
            parent::__construct($AppName, $Request);
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $this->L10N = $L10N;
            
            $this->Settings = new Settings ();
      }
      
      /**
       * @AdminRequired
       * @NoCSRFRequired
       */
      public function Save ()
      {
            \OCP\JSON::setContentTypeHeader ('application/json');
            
            $Error = false;
            $Message = null;
            
            foreach ($_POST as $PostKey => $PostValue)
            {
                  if (in_array ($PostKey, $this->OCDSettingKeys))
                  {
                        $this->Settings->SetKey ($PostKey);
                        
                        if (strcmp ($PostKey, 'YTDLBinary') == 0)
                        {
                              $PostValue = trim (str_replace (' ', '\ ', $PostValue));
                              if (!file_exists ($PostValue))
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    $Message = (string)$this->L10N->t ('Unable to find YouTube-DL binary');
                              }
                        }
                        elseif (strcmp ($PostKey, 'ProxyAddress') == 0)
                        {
                              if (!Tools::CheckURL ($PostValue) && strlen (trim ($PostValue)) > 0)
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    $Message = (string)$this->L10N->t ('Invalid proxy address URL');
                              }
                        }
                        elseif (strcmp ($PostKey, 'ProxyPort') == 0)
                        {
                              if (!is_numeric ($PostValue))
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    $Message = (string)$this->L10N->t ('Proxy port should be a numeric value');
                              }
                              elseif ($PostValue <= 0 || $PostValue > 65536)
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    $Message = (string)$this->L10N->t ('Proxy port should be a value from 1 to 65536');
                              }
                        }
                        elseif (strcmp ($PostKey, 'CheckForUpdates') == 0)
                        {
                              if (!in_array ($PostValue, Array ('Y', 'N')))
                              {
                                    $PostValue = 'Y';
                              }
                        }
                        elseif (strcmp ($PostKey, 'WhichDownloader') == 0)
                        {
                              if (!in_array ($PostValue, Array ('ARIA2', 'CURL')))
                              {
                                    $PostValue = 'ARIA2';
                              }
                              elseif (strcmp ($PostValue, 'ARIA2') != 0)
                              {
                                    Tools::ResetAria2 ($this->DbType);
                              }
                        }
                        else
                        {
                              $PostValue = null;
                              $Error = true;
                        }
                        
                        if (strlen (trim ($PostValue)) <= 0)
                        {
                              $PostValue = null;
                        }
                        
                        if ($this->Settings->CheckIfKeyExists ())
                        {
                              $this->Settings->UpdateValue ($PostValue);
                        }
                        else
                        {
                              $this->Settings->InsertValue ($PostValue);
                        }
                  }
            }
            
            return new JSONResponse (Array ('ERROR' => $Error, 'MESSAGE' => is_null ($Message) ? (string)$this->L10N->t ('Saved') : $Message));
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Get ()
      {
            \OCP\JSON::setContentTypeHeader ('application/json');
            
            $AdminSettings = Array ();
            foreach ($_POST['KEYS'] as $PostKey)
            {
                  if (in_array ($PostKey, $this->OCDSettingKeys))
                  {
                        $this->Settings->SetKey ($PostKey);
                        $AdminSettings[$PostKey] = $this->Settings->GetValue ();
                  
                        // Set default if not set in the database
                        if (is_null ($AdminSettings[$PostKey]))
                        {
                              switch ($PostKey)
                              {
                                    case 'YTDLBinary':
                                          $AdminSettings[$PostKey] = '/usr/local/bin/youtube-dl';
                                          break;
                                    case 'CheckForUpdates':
                                          $AdminSettings[$PostKey] = 'Y';
                                          break;
                                    case 'WhichDownloader':
                                          $AdminSettings[$PostKey] = 'ARIA2';
                                          break;
                              }
                        }
                  }
            }
            
            return new JSONResponse (Array ('ERROR' => false, 'VALS' => $AdminSettings));
      }
}