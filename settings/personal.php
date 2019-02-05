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

\OCP\User::checkLoggedIn();

// Display template
style('ocdownloader', 'settings/personal');
script('ocdownloader', 'settings/personal');

$Tmpl = new OCP\Template('ocdownloader', 'settings/personal');

$Settings = new Settings();
$Settings->setKey('AllowProtocolBT');
$AllowProtocolBT = $Settings->getValue();
$AllowProtocolBT = is_null($AllowProtocolBT) ? true : strcmp($AllowProtocolBT, 'Y') == 0;

$Tmpl->assign('AllowProtocolBT', $AllowProtocolBT);

$Settings->setTable('personal');
$Settings->setUID(OC_User::getUser());
$Rows = $Settings->getAllValues();

while ($Row = $Rows->fetchRow()) {
      $Tmpl->assign('OCDS_' . $Row['KEY'], $Row['VAL']);
}

return $Tmpl->fetchPage();
