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

use \OCA\ocDownloader\Controller\Lib\Aria2;
use \OCA\ocDownloader\Controller\Lib\Tools;
use \OCA\ocDownloader\Controller\Lib\Settings;

class YTDownloaderController extends Controller
{
      private $TargetFolder = null;
      private $DbType = 0;
      private $ProxyAddress = null;
      private $ProxyPort = 0;
      private $ProxyUser = null;
      private $ProxyPasswd = null;
	  
      public function __construct ($AppName, IRequest $Request, $UserStorage)
      {
            parent::__construct ($AppName, $Request);
            $this->TargetFolder = Config::getSystemValue ('datadirectory') . $UserStorage->getPath ();
            
            if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
            {
                  $this->DbType = 1;
            }
            
            $Settings = new Settings ();
            
            $Settings->SetKey ('ProxyAddress');
            $this->ProxyAddress = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPort');
            $this->ProxyPort = intval ($Settings->GetValue ());
            $Settings->SetKey ('ProxyUser');
            $this->ProxyUser = $Settings->GetValue ();
            $Settings->SetKey ('ProxyPasswd');
            $this->ProxyPasswd = $Settings->GetValue ();
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            if (isset ($_POST['URLORPATH']) && strlen (trim ($_POST['URLORPATH'])) > 0 && (Tools::CheckURL ($_POST['URLORPATH']) || Tools::CheckFilepath ($_POST['URLORPATH']))/* && isset ($_POST['OPTIONS'])*/)
            {
                  try
                  {
                        $Target = str_replace ('.torrent', '', Tools::CleanString (substr($_POST['URLORPATH'], strrpos($_POST['URLORPATH'], '/') + 1)));
                        
                        // If target file exists, create a new one
                        if (\OC\Files\Filesystem::is_dir ($Target))
                        {
                              $Target = time () . '_' . $Target;
                        }
                        
                        // Create the target file
                        \OC\Files\Filesystem::mkdir ($Target);
                        
                        $OPTIONS = Array ('dir' => $this->TargetFolder . '/' . $Target);
                        
                        $Aria2 = new Aria2 ();
                        $AddTorrent = $Aria2->addTorrent ();
                  }
                  catch (Exception $E)
                  {
                        die (json_encode (Array ('ERROR' => true, 'MESSAGE' => $E->getMessage ())));
                  }
            }
            else
            {
                  die (json_encode (Array ('ERROR' => true, 'MESSAGE' => 'Please check the URL or filepath you\'ve just provided')));
            }
      }
}