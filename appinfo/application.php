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
		
		$container->registerService ('CurrentUID', function ($c)
		{
			$User = $c->query ('ServerContainer')->getUserSession ()->getUser ();
			return ($User) ? $User->getUID () : '';
		});
		
		$container->registerService ('IndexController', function ($c)
		{
	      	return new IndexController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID')
	      	);
	    });
		
		$container->registerService ('HttpDownloaderController', function ($c)
		{
	      	return new HttpDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('FtpDownloaderController', function ($c)
		{
	      	return new FtpDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('YTDownloaderController', function ($c)
		{
	      	return new YTDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('BTDownloaderController', function ($c)
		{
	      	return new BTDownloaderController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('DownloaderQueueController', function ($c)
		{
	      	return new DownloaderQueueController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('AdminSettingsController', function ($c)
		{
	      	return new AdminSettingsController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('PersonalSettingsController', function ($c)
		{
	      	return new PersonalSettingsController
			(
		        $c->query ('AppName'),
		        $c->query ('Request'),
				$c->query ('CurrentUID'),
				$c->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
	}
}
