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

use OC\AppFramework\Utility\SimpleContainer;

use OCA\ocDownloader\Service\BackendService;
use \OCA\ocDownloader\Config\IBackendProvider;

use OCA\ocDownloader\Controller\Index;
use OCA\ocDownloader\Controller\Queue;
use OCA\ocDownloader\Controller\Updater;
use OCA\ocDownloader\Controller\PersonalSettings;
use OCA\ocDownloader\Controller\AdminSettings;


class Application extends App implements IBackendProvider
{
	public function __construct (Array $URLParams = Array ())
	{
		parent::__construct ('ocdownloader', $URLParams);
		$container = $this->getContainer ();

		$backendService = $container->query('OCA\\ocDownloader\\Service\\BackendService');
		$backendService->registerBackendProvider($this);
		//$backendService->getBackends();


		$container->registerService ('CurrentUID', function (IContainer $Container)
		{
			$User = $Container->query ('ServerContainer')->getUserSession ()->getUser ();
			return ($User) ? $User->getUID () : '';
		});
		
		$container->registerService ('IndexController', function (IContainer $Container)
		{
	      	return new Index
			(
		        $Container->query ('AppName'),
		        $Container->query ('Request'),
						$Container->query ('CurrentUID'),
						$Container->getServer ()->getL10N ('ocdownloader')
	      	);
	    });


		$container->registerService ('QueueController', function (IContainer $Container)
		{
	      	return new Queue
			(
		        $Container->query ('AppName'),
		        $Container->query ('Request'),
						$Container->query ('CurrentUID'),
						$Container->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('UpdaterController', function (IContainer $Container)
		{
	      	return new Updater
			(
		        $Container->query ('AppName'),
		        $Container->query ('Request'),
						$Container->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('AdminSettingsController', function (IContainer $Container)
		{
	      	return new AdminSettings
			(
		        $Container->query ('AppName'),
		        $Container->query ('Request'),
						$Container->getServer ()->getL10N ('ocdownloader')
	      	);
	    });
		
		$container->registerService ('PersonalSettingsController', function (IContainer $Container)
		{
	      	return new PersonalSettings
			(
		        $Container->query ('AppName'),
		        $Container->query ('Request'),
						$Container->query ('CurrentUID'),
						$Container->getServer ()->getL10N ('ocdownloader')
	      	);
	    });

/*	 $container->registerService('APIController', function(SimpleContainer $Container)  {
				return new ApiController
		(
					$Container->query ('AppName'),
					$Container->query ('Request'),
				);
		});*/

}

	public function getBackends() {
		$container = $this->getContainer();

		$backends = [
			$container->query('OCA\ocDownloader\Backend\MagnetBackend'),
		  $container->query('OCA\ocDownloader\Backend\YTDLBackend'),
			$container->query('OCA\ocDownloader\Backend\HttpBackend'),
			$container->query('OCA\ocDownloader\Backend\FtpBackend'),
		];

		return $backends;
	}
}
