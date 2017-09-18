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

namespace OCA\ocDownloader\AppInfo;

$l = \OC::$server->getL10N('ocdownloader');
$g = \OC::$server->getURLGenerator();

\OCP\App::addNavigationEntry([
    'id' => 'ocdownloader',
    'order' => 10,
    'href' => $g->linkToRoute('ocdownloader.Index.Add'),
    'icon' => $g->imagePath('ocdownloader', 'ocdownloader.svg'),
    'name' => 'ocDownloader'
]);

\OCP\App::registerAdmin('ocdownloader', 'settings/admin');
\OCP\App::registerPersonal('ocdownloader', 'settings/personal');
