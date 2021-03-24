<?php

namespace OCA\Ocdownloader\Settings;

use OCA\ocDownloader\Controller\Lib\Settings;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {

    public function getForm() {

        $settings = new Settings();
        $rows = $settings->getAllValues();
        $parameters = [];

        while ($Row = $rows->fetchRow()) {
            $parameters['OCDS_' . $Row['KEY']] = $Row['VAL'];
        }

        return new TemplateResponse('ocdownloader', 'settings/admin', $parameters);

    }

    public function getSection() {
        return 'additional';
    }

    public function getPriority() {
        return 50;
    }

}
