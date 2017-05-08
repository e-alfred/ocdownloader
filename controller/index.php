<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Creative Commons BY-SA License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

namespace OCA\ocDownloader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Config;
use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Settings;

class Index extends Controller
{
      private $DbType = 0;
      private $CurrentUID = null;
      private $CanCheckForUpdate = false;
      private $Settings = null;
      private $WhichDownloader = null;
      private $L10N = null;
      private $AllowProtocolHTTP = null;
      private $AllowProtocolFTP = null;
      private $AllowProtocolYT = null;
      private $AllowProtocolBT = null;
      
      public function __construct ($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
      {
            parent::__construct ($AppName, $Request);
            $this->CurrentUID = $CurrentUID;
            $this->L10N = $L10N;
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $this->CanCheckForUpdate = Tools::CanCheckForUpdate ();
            
            $this->Settings = new Settings ();
            $this->Settings->SetKey ('WhichDownloader');
            $this->WhichDownloader = $this->Settings->GetValue ();
            $this->WhichDownloader = is_null ($this->WhichDownloader) ? 'ARIA2' : $this->WhichDownloader;
            
            $this->Settings->SetKey ('AllowProtocolHTTP');
            $this->AllowProtocolHTTP = $this->Settings->GetValue ();
            $this->AllowProtocolHTTP = is_null ($this->AllowProtocolHTTP) || \OC_User::isAdminUser ($this->CurrentUID) ? true : strcmp ($this->AllowProtocolHTTP, 'Y') == 0;
            $this->Settings->SetKey ('AllowProtocolFTP');
            $this->AllowProtocolFTP = $this->Settings->GetValue ();
            $this->AllowProtocolFTP = is_null ($this->AllowProtocolFTP) || \OC_User::isAdminUser ($this->CurrentUID) ? true : strcmp ($this->AllowProtocolFTP, 'Y') == 0;
            $this->Settings->SetKey ('AllowProtocolYT');
            $this->AllowProtocolYT = $this->Settings->GetValue ();
            $this->AllowProtocolYT = is_null ($this->AllowProtocolYT) || \OC_User::isAdminUser ($this->CurrentUID) ? true : strcmp ($this->AllowProtocolYT, 'Y') == 0;
            $this->Settings->SetKey ('AllowProtocolBT');
            $this->AllowProtocolBT = $this->Settings->GetValue ();
            $this->AllowProtocolBT = is_null ($this->AllowProtocolBT) || \OC_User::isAdminUser ($this->CurrentUID) ? true : strcmp ($this->AllowProtocolBT, 'Y') == 0;
      }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Add ()
      {
            $this->Settings->SetTable ('personal');
            $this->Settings->SetUID ($this->CurrentUID);
            $this->Settings->SetKey ('TorrentsFolder');
            $TorrentsFolder = $this->Settings->GetValue ();
            
            return new TemplateResponse ('ocdownloader', 'add', [ 
                  'PAGE' => 0,
                  'TTSFOLD' => $TorrentsFolder,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader,
                  'AllowProtocolHTTP' => $this->AllowProtocolHTTP,
                  'AllowProtocolFTP' => $this->AllowProtocolFTP,
                  'AllowProtocolYT' => $this->AllowProtocolYT,
                  'AllowProtocolBT' => $this->AllowProtocolBT
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function All ()
      {
            return new TemplateResponse ('ocdownloader', 'all', [ 
                  'PAGE' => 1,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Completes ()
      {
            return new TemplateResponse ('ocdownloader', 'completes', [ 
                  'PAGE' => 2,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Actives ()
      {
            return new TemplateResponse('ocdownloader', 'actives', [
                  'PAGE' => 3,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Waitings ()
      {
            if (strcmp ($this->WhichDownloader, 'ARIA2') != 0)
            {
                  return $this->L10N->t ('You are using %s ! This page is only available with the following engines : ', $this->WhichDownloader) . 'ARIA2';
            }
            return new TemplateResponse('ocdownloader', 'waitings', [
                  'PAGE' => 4,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Stopped ()
      {
            if (strcmp ($this->WhichDownloader, 'ARIA2') != 0)
            {
                  return $this->L10N->t ('You are using %s ! This page is only available with the following engines : ', $this->WhichDownloader) . 'ARIA2';
            }
            return new TemplateResponse('ocdownloader', 'stopped', [
                  'PAGE' => 5,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function Removed ()
      {
            //if (strcmp ($this->WhichDownloader, 'ARIA2') != 0)
            //{
            //      return $this->L10N->t ('You are using %s ! This page is only available with the following engines : ', $this->WhichDownloader) . 'ARIA2';
            //}
            return new TemplateResponse('ocdownloader', 'removed', [
                  'PAGE' => 6,
                  'CANCHECKFORUPDATE' => $this->CanCheckForUpdate,
                  'WD' => $this->WhichDownloader
            ]);
      }
}
