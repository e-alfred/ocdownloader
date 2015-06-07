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
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\Config;

use \OCA\ocDownloader\Controller\Lib\Tools;
use \OCA\ocDownloader\Controller\Lib\Settings;

class IndexController extends Controller
{
      private $DbType = 0;
      private $CurrentUID = null;
      
      public function __construct ($AppName, IRequest $Request, $CurrentUID)
      {
            parent::__construct($AppName, $Request);
            $this->CurrentUID = $CurrentUID;
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
      }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            $Settings = new Settings ('personal');
            $Settings->SetUID ($this->CurrentUID);
            
            $Settings->SetKey ('TorrentsFolder');
            $TorrentsFolder = $Settings->GetValue ();
            
            return new TemplateResponse ('ocdownloader', 'add', [ 
                  'PAGE' => 0,
                  'TTSFOLD' => $TorrentsFolder
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function all ()
      {
            return new TemplateResponse ('ocdownloader', 'all', [ 
                  'PAGE' => 1
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function completes ()
      {
            return new TemplateResponse ('ocdownloader', 'completes', [ 
                  'PAGE' => 2
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function actives ()
      {
            return new TemplateResponse('ocdownloader', 'actives', [
                  'PAGE' => 3
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function waitings ()
      {
            return new TemplateResponse('ocdownloader', 'waitings', [
                  'PAGE' => 4
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function stopped ()
      {
            return new TemplateResponse('ocdownloader', 'stopped', [
                  'PAGE' => 5
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function removed ()
      {
            return new TemplateResponse('ocdownloader', 'removed', [
                  'PAGE' => 6
            ]);
      }
}