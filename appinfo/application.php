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

use \OCP\AppFramework\App;
use \OCA\ocDownloader\Controller\IndexController;
use \OCA\ocDownloader\Controller\HttpDownloaderController;
use \OCA\ocDownloader\Controller\DownloaderQueueController;

class Application extends App
{
	public function __construct (array $URLParams = array ())
	{
		parent::__construct ('ocdownloader', $URLParams);
		
		$container = $this->getContainer ();
		$container->registerService ('IndexController', function ($c)
		{
	      	return new IndexController
			(
		        $c->query ('AppName'),
		        $c->query ('Request')
	      	);
	    });
		
		$container->registerService ('UserStorage', function ($c) {
            return $c->query ('ServerContainer')->getUserFolder ();
        });
		
		$container->registerService ('HttpDownloaderController', function ($c)
		{
	      	return new HttpDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('UserStorage')
	      	);
	    });
		
		$container->registerService ('FtpDownloaderController', function ($c)
		{
	      	return new FtpDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('UserStorage')
	      	);
	    });
		
		$container->registerService ('YTDownloaderController', function ($c)
		{
	      	return new YTDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('UserStorage')
	      	);
	    });
		
		$container->registerService ('DownloaderQueueController', function ($c)
		{
	      	return new DownloaderQueueController
			(
		        $c->query ('AppName'),
		        $c->query ('Request')
	      	);
	    });
		
		$container->registerService ('AdminSettingsController', function ($c)
		{
	      	return new AdminSettingsController
			(
		        $c->query ('AppName'),
		        $c->query ('Request')
	      	);
	    });
	}
}
