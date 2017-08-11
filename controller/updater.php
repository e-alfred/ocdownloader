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

use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Settings;

class Updater extends Controller
{
      private $Settings = null;
      private $Allow = false;
      private $L10N = null;
      
      public function __construct ($AppName, IRequest $Request, IL10N $L10N)
      {
            $this->L10N = $L10N;
            $this->Allow = Tools::CanCheckForUpdate ();
      }
      
      /**
       * @AdminRequired
       * @NoCSRFRequired
       */
      public function Check ()
      {
            \OCP\JSON::setContentTypeHeader ('application/json');
            
            if ($this->Allow)
            {
                  try
                  {
                        $LastVersionNumber = Tools::GetLastVersionNumber ();
                        $AppVersion = \OCP\App::getAppVersion ('ocdownloader');
                        
                        $Response = Array ('ERROR' => false, 'RESULT' => version_compare ($AppVersion, $LastVersionNumber, '<'));
                  }
                  catch (Exception $E)
                  {
                        $Response = Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('Error while checking application version on GitHub'));
                  }
            }
            else
            {
                  $Response = Array ('ERROR' => true, 'MESSAGE' => (string)$this->L10N->t ('You are not allowed to check for application updates'));
            }
            
            return new JSONResponse ($Response);
      }
}