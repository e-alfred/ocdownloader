<?php

namespace OCA\Ocdownloader\Settings;

use OC;
use OCA\ocDownloader\Controller\Lib\Settings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Personal implements ISettings {

    public function getForm() {

        $settings = new Settings();
        $settings->setKey('AllowProtocolBT');
        $allowProtocolBT = $settings->getValue();
        $allowProtocolBT = is_null($allowProtocolBT) ? true : strcmp($allowProtocolBT, 'Y') == 0;

        $parameters = [
            'AllowProtocolBT' => $allowProtocolBT,
        ];

        $settings->setTable('personal');
        $settings->setUID(OC::$server->getUserSession()->isLoggedIn());
        $rows = $settings->getAllValues();

        while ($row = $rows->fetchRow()) {
            $parameters['OCDS_' . $row['KEY']] = $row['VAL'];
        }

        return new TemplateResponse('ocdownloader', 'settings/personal', $parameters);

    }

    public function getSection() {
        return 'additional';
    }

    public function getPriority() {
        return 50;
    }

}
