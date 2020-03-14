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

use OCA\ocDownloader\Controller\Lib\Settings;

if (!OC_User::isAdminUser(OC_User::getUser())) {
   return;
}

// Display template
\OCP\Util::addStyle('ocdownloader', 'settings/admin');
\OCP\Util::addScript('ocdownloader', 'settings/admin');

$Tmpl = new OCP\Template('ocdownloader', 'settings/admin');

$Settings = new Settings();
$Rows = $Settings->getAllValues();

while ($Row = $Rows->fetchRow()) {
      $Tmpl->assign('OCDS_' . $Row['KEY'], $Row['VAL']);
}

return $Tmpl->fetchPage();
