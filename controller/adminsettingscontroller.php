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

use \OCP\IRequest;
use \OCP\AppFramework\Controller;

use \OCA\ocDownloader\Controller\Lib\Settings;
use \OCA\ocDownloader\Controller\Lib\Tools;

class AdminSettingsController extends Controller
{
      public function __construct ($AppName, IRequest $Request)
      {
            parent::__construct($AppName, $Request);
      }
      
      /**
       * @AdminRequired
       * @NoCSRFRequired
       */
      public function save ()
      {
            $OCDSettingKeys = Array ('YTDLBinary', 'ProxyAddress', 'ProxyPort', 'ProxyUser', 'ProxyPasswd');
            $Settings = new Settings ();
            $Error = false;
            $Message = '';
            
            foreach ($_POST as $PostKey => $PostValue)
            {
                  if (in_array ($PostKey, $OCDSettingKeys))
                  {
                        $Settings->SetKey ($PostKey);
                        
                        // Pre-Save process
                        if (strcmp ($PostKey, 'YTDLBinary') == 0)
                        {
                              $PostValue = trim (str_replace (' ', '\ ', $PostValue));
                              // check file exists
                        }
                        if (strcmp ($PostKey, 'ProxyAddress') == 0)
                        {
                              if (!Tools::CheckURL ($PostValue) && strlen (trim ($PostValue)) > 0)
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    if (strlen (trim ($Message)) > 0)
                                    {
                                          $Message .= ', ';
                                    }
                                    $Message .= 'Invalid URL for proxy address field';
                              }
                        }
                        if (strcmp ($PostKey, 'ProxyPort') == 0)
                        {
                              if (!is_numeric ($PostValue) && strlen (trim ($PostValue)) > 0)
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    if (strlen (trim ($Message)) > 0)
                                    {
                                          $Message .= ', ';
                                    }
                                    $Message .= 'Proxy port should be a numeric value';
                              }
                              if (is_numeric ($PostValue) && ($PostValue == 0 || $PostValue > 65536))
                              {
                                    $PostValue = null;
                                    $Error = true;
                                    if (strlen (trim ($Message)) > 0)
                                    {
                                          $Message .= ', ';
                                    }
                                    $Message .= 'Proxy port should be a value from 1 to 65536';
                              }
                        }
                        
                        if (strlen (trim ($PostValue)) <= 0)
                        {
                              $PostValue = null;
                        }
                        
                        if ($Settings->CheckIfKeyExists ())
                        {
                              $Settings->UpdateValue ($PostValue);
                        }
                        else
                        {
                              $Settings->InsertValue ($PostValue);
                        }
                  }
            }
            
            $Rows = $Settings->GetAllValues ();
            $Settings = Array ();
            while ($Row = $Rows->fetchRow ())
            {
                  $Settings['OCDS_' . $Row['KEY']] = $Row['VAL'];
            }
            die (json_encode (Array ('ERROR' => $Error, 'MESSAGE' => (strlen (trim ($Message)) == 0 ? 'Saved' : $Message), 'SETTINGS' => $Settings)));
      }
}