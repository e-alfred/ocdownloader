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

namespace OCA\ocDownloader\AppInfo;

\OCP\App::addNavigationEntry
([
    'id' => 'ocdownloader',
    'order' => 10,
    'href' => \OCP\Util::linkToRoute ('ocdownloader.index.add'),
    'icon' => \OCP\Template::image_path ('ocdownloader', 'ocdownloader.svg'),
    'name' => 'ocDownloader'
]);

\OCP\App::registerAdmin('ocdownloader', 'settings-admin');