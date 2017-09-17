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

$Application = new Application();
$Application->registerRoutes($this, array(
    'routes' => [
        // Index
        ['name' => 'Index#Add', 'url' => '/add', 'verb' => 'GET'],
        ['name' => 'Index#All', 'url' => '/all', 'verb' => 'GET'],
        ['name' => 'Index#Completes', 'url' => '/completes', 'verb' => 'GET'],
        ['name' => 'Index#Actives', 'url' => '/actives', 'verb' => 'GET'],
        ['name' => 'Index#Waitings', 'url' => '/waitings', 'verb' => 'GET'],
        ['name' => 'Index#Stopped', 'url' => '/stopped', 'verb' => 'GET'],
        ['name' => 'Index#Removed', 'url' => '/removed', 'verb' => 'GET'],
        
        // HttpDownloader
        ['name' => 'HttpDownloader#Add', 'url' => '/httpdownloader/add', 'verb' => 'POST'],
        
        // FtpDownloader
        ['name' => 'FtpDownloader#Add', 'url' => '/ftpdownloader/add', 'verb' => 'POST'],
        
        // YTDownloader
        ['name' => 'YTDownloader#Add', 'url' => '/ytdownloader/add', 'verb' => 'POST'],
        
        // BTDownloader
        ['name' => 'BTDownloader#Add', 'url' => '/btdownloader/add', 'verb' => 'POST'],
        ['name' => 'BTDownloader#ListTorrentFiles', 'url' => '/btdownloader/listtorrentfiles', 'verb' => 'POST'],
        ['name' => 'BTDownloader#UploadFiles', 'url' => '/btdownloader/uploadfiles', 'verb' => 'POST'],
        
        // Queue
        ['name' => 'Queue#Get', 'url' => '/queue/get', 'verb' => 'POST'],
        ['name' => 'Queue#Count', 'url' => '/queue/count', 'verb' => 'POST'],
        ['name' => 'Queue#Hide', 'url' => '/queue/hide', 'verb' => 'POST'],
        ['name' => 'Queue#HideAll', 'url' => '/queue/hideall', 'verb' => 'POST'],
        ['name' => 'Queue#Remove', 'url' => '/queue/remove', 'verb' => 'POST'],
        ['name' => 'Queue#CompletelyRemove', 'url' => '/queue/completelyremove', 'verb' => 'POST'],
        ['name' => 'Queue#Pause', 'url' => '/queue/pause', 'verb' => 'POST'],
        ['name' => 'Queue#UnPause', 'url' => '/queue/unpause', 'verb' => 'POST'],
        ['name' => 'Queue#RemoveAll', 'url' => '/queue/removeall', 'verb' => 'POST'],
        ['name' => 'Queue#CompletelyRemoveAll', 'url' => '/queue/completelyremoveall', 'verb' => 'POST'],
        
        // AdminSettings
        ['name' => 'AdminSettings#Save', 'url' => '/adminsettings/save', 'verb' => 'POST'],
        ['name' => 'AdminSettings#Get', 'url' => '/adminsettings/get', 'verb' => 'POST'],
        
        // PersonalSettings
        ['name' => 'PersonalSettings#Save', 'url' => '/personalsettings/save', 'verb' => 'POST'],
        ['name' => 'PersonalSettings#Get', 'url' => '/personalsettings/get', 'verb' => 'GET'],
        
        // Updater
        ['name' => 'Updater#Check', 'url' => '/updater/check', 'verb' => 'GET']
    ]
));

$APIBasePath = '/apps/ocdownloader/api/';
\OCP\API::register(
    'POST',
    $APIBasePath.'version',
    function ($URLParams) {
        return new \OC_OCS_Result(\OCA\ocDownloader\Controller\Lib\API::checkAddonVersion($_POST['AddonVersion']));
    },
    'ocdownloader',
    \OC_API::USER_AUTH
);

\OCP\API::register(
    'GET',
    $APIBasePath.'queue/get',
    function ($URLParams) {
        return new \OC_OCS_Result(\OCA\ocDownloader\Controller\Lib\API::getQueue());
    },
    'ocdownloader',
    \OC_API::USER_AUTH
);

\OCP\API::register(
    'POST',
    $APIBasePath.'add',
    function ($URLParams) {
        return new \OC_OCS_Result(\OCA\ocDownloader\Controller\Lib\API::add($_POST['URL']));
    },
    'ocdownloader',
    \OC_API::USER_AUTH
);
