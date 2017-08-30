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

use OCP\AppFramework\App;
use OCP\IContainer;

use OCA\ocDownloader\Controller\Index;
use OCA\ocDownloader\Controller\HttpDownloader;
use OCA\ocDownloader\Controller\FtpDownloader;
use OCA\ocDownloader\Controller\YTDownloader;
use OCA\ocDownloader\Controller\BTDownloader;
use OCA\ocDownloader\Controller\Queue;
use OCA\ocDownloader\Controller\Updater;
use OCA\ocDownloader\Controller\PersonalSettings;
use OCA\ocDownloader\Controller\AdminSettings;

class Application extends App
{
    public function __construct(array $URLParams = array())
    {
        parent::__construct('ocdownloader', $URLParams);
        $container = $this->getContainer();
        
        $container->registerService('CurrentUID', function (IContainer $Container) {
            $User = $Container->query('ServerContainer')->getUserSession()->getUser();
            return($User) ? $User->getUID() : '';
        });
        
        $container->registerService('IndexController', function (IContainer $Container) {
            return new Index(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('HttpDownloaderController', function (IContainer $Container) {
            return new HttpDownloader(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('FtpDownloaderController', function (IContainer $Container) {
            return new FtpDownloader(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('YTDownloaderController', function (IContainer $Container) {
            return new YTDownloader(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('BTDownloaderController', function (IContainer $Container) {
            return new BTDownloader(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('QueueController', function (IContainer $Container) {
            return new Queue(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('UpdaterController', function (IContainer $Container) {
            return new Updater(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('AdminSettingsController', function (IContainer $Container) {
            return new AdminSettings(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
        
        $container->registerService('PersonalSettingsController', function (IContainer $Container) {
            return new PersonalSettings(
                $Container->query('AppName'),
                $Container->query('Request'),
                $Container->query('CurrentUID'),
                $Container->getServer()->getL10N('ocdownloader')
            );
        });
    }
}
