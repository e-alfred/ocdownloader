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

use OCP\IDBConnection;
use OCA\ocDownloader\Controller\Lib\Settings;

\OC_Util::checkAdminUser();

// Display template
style('ocdownloader', 'settings/admin');
script('ocdownloader', 'settings/admin');

$Tmpl = new OCP\Template('ocdownloader', 'settings/admin');

$Settings = new Settings();
$Settings->getAllValues();

while ($Row = $Settings->fetch()) {
      $Tmpl->assign('OCDS_' . $Row['KEY'], $Row['VAL']);
}

return $Tmpl->fetchPage();
