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

$Application = new Application ();
$Application->registerRoutes ($this, Array (
    'routes' => [
        // IndexController
        ['name' => 'index#add', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'index#add', 'url' => '/add', 'verb' => 'GET'],
        ['name' => 'index#all', 'url' => '/all', 'verb' => 'GET'],
        ['name' => 'index#completes', 'url' => '/completes', 'verb' => 'GET'],
        ['name' => 'index#actives', 'url' => '/actives', 'verb' => 'GET'],
        ['name' => 'index#waitings', 'url' => '/waitings', 'verb' => 'GET'],
        ['name' => 'index#stopped', 'url' => '/stopped', 'verb' => 'GET'],
        ['name' => 'index#removed', 'url' => '/removed', 'verb' => 'GET'],
        
        // HttpDownloaderController
        ['name' => 'httpdownloader#add', 'url' => '/httpdownloader/add', 'verb' => 'POST'],
        
        // FtpDownloaderController
        ['name' => 'ftpdownloader#add', 'url' => '/ftpdownloader/add', 'verb' => 'POST'],
        
        // YTDownloaderController
        ['name' => 'ytdownloader#add', 'url' => '/ytdownloader/add', 'verb' => 'POST'],
        
        // BTDownloaderController
        ['name' => 'btdownloader#add', 'url' => '/btdownloader/add', 'verb' => 'POST'],
        ['name' => 'btdownloader#listtorrentfiles', 'url' => '/btdownloader/listtorrentfiles', 'verb' => 'POST'],
        
        // QueueController
        ['name' => 'queue#get', 'url' => '/queue/get', 'verb' => 'POST'],
        ['name' => 'queue#count', 'url' => '/queue/count', 'verb' => 'POST'],
        ['name' => 'queue#hide', 'url' => '/queue/hide', 'verb' => 'POST'],
        ['name' => 'queue#hideall', 'url' => '/queue/hideall', 'verb' => 'POST'],
        ['name' => 'queue#remove', 'url' => '/queue/remove', 'verb' => 'POST'],
        ['name' => 'queue#completelyremove', 'url' => '/queue/completelyremove', 'verb' => 'POST'],
        ['name' => 'queue#pause', 'url' => '/queue/pause', 'verb' => 'POST'],
        ['name' => 'queue#unpause', 'url' => '/queue/unpause', 'verb' => 'POST'],
        ['name' => 'queue#removeall', 'url' => '/queue/removeall', 'verb' => 'POST'],
        ['name' => 'queue#completelyremoveall', 'url' => '/queue/completelyremoveall', 'verb' => 'POST'],
        
        // AdminSettingsController
        ['name' => 'adminsettings#save', 'url' => '/adminsettings/save', 'verb' => 'POST'],
        
        // PersonalSettingsController
        ['name' => 'personalsettings#save', 'url' => '/personalsettings/save', 'verb' => 'POST'],
        ['name' => 'personalsettings#get', 'url' => '/personalsettings/get', 'verb' => 'POST']
    ]
));

$APIBasePath = '/apps/ocdownloader/api/';
\OCP\API::register ('POST', $APIBasePath . 'download', function ($URLParams) { return new \OC_OCS_Result (\OCA\ocDownloader\Controller\Lib\API::Download ($_POST['URL'])); }, 'ocdownloader', \OC_API::USER_AUTH);