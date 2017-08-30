<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */
      
namespace OCA\ocDownloader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\Settings;
use OCA\ocDownloader\Controller\Lib\Tools;

class PersonalSettings extends Controller
{
    private $CurrentUID = null;
    private $OCDSettingKeys = array(
        'DownloadsFolder', 'TorrentsFolder', 'BTRatioToReach', 'BTSeedTimeToReach_BTSeedTimeToReachUnit'
    );
    private $Settings = null;
    private $L10N = null;
      
    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);
        $this->CurrentUID = $CurrentUID;
            
        $this->Settings = new Settings('personal');
        $this->Settings->setUID($this->CurrentUID);
            
        $this->L10N = $L10N;
    }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function save()
    {
        \OCP\JSON::setContentTypeHeader('application/json');
            
        $Error = false;
        $Message = '';
            
        if (isset($_POST['KEY']) && strlen(trim($_POST['KEY'])) > 0
        && isset($_POST['VAL']) && strlen(trim($_POST['VAL'])) > 0) {
            $PostKey = str_replace('OCD', '', $_POST['KEY']);
            $PostValue = ltrim(trim(str_replace(' ', '\ ', $_POST['VAL'])), '/');
                  
            if (in_array($PostKey, $this->OCDSettingKeys)) {
                $this->Settings->setKey($PostKey);
                        
                // Pre-Save process
                if (strcmp($PostKey, 'DownloadsFolder') == 0 || strcmp($PostKey, 'TorrentsFolder') == 0) {
                    // check folder exists, if not create it
                    if (!\OC\Files\Filesystem::is_dir($PostValue)) {
                        // Create the target file
                        \OC\Files\Filesystem::mkdir($PostValue);
                                    
                        $Message .= $this->L10N->t('The folder doesn\'t exist. It has been created.');
                    }
                }
                        
                if (strlen(trim($PostValue)) <= 0) {
                    $PostValue = null;
                }
                        
                if ($this->Settings->checkIfKeyExists()) {
                    $this->Settings->updateValue($PostValue);
                } else {
                    $this->Settings->insertValue($PostValue);
                }
            } else {
                $Error = true;
                $Message = $this->L10N->t('Unknown field');
            }
        } else {
            $Error = true;
            $Message = $this->L10N->t('Undefined field');
        }
            
        return new JSONResponse(
            array(
                'ERROR' => $Error,
                'MESSAGE' =>(strlen(trim($Message)) == 0 ?(string)$this->L10N->t('Saved') : $Message)
            )
        );
    }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function get()
    {
        \OCP\JSON::setContentTypeHeader('application/json');
            
        $PersonalSettings = array();
        foreach ($this->OCDSettingKeys as $SettingKey) {
            $this->Settings->setKey($SettingKey);
            $PersonalSettings[$SettingKey] = $this->Settings->getValue();
                  
            // Set default if not set in the database
            if (is_null($PersonalSettings[$SettingKey])) {
                switch ($SettingKey) {
                    case 'DownloadsFolder':
                        $PersonalSettings[$SettingKey] = 'Downloads';
                        break;
                    case 'TorrentsFolder':
                        $PersonalSettings[$SettingKey] = 'Downloads/Files/Torrents';
                        break;
                }
            }
        }
            
        return new JSONResponse(array('ERROR' => false, 'VALS' => $PersonalSettings));
    }
}
