<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\ocDownloader\Service;

use \OCP\IConfig;

use OCA\ocDownloader\Backend\BackendException;
use \OCA\ocDownloader\Backend\IBackendDownloader as Backend;
use \OCA\ocDownloader\Config\IBackendProvider;

use OCA\ocDownloader\Lib\Tools;

/**
 * Service class to manage backend definitions
 */
class BackendService {

	/** Visibility constants for VisibilityTrait */
	const VISIBILITY_NONE = 0;
	const VISIBILITY_PERSONAL = 1;
	const VISIBILITY_ADMIN = 2;
	//const VISIBILITY_ALIENS = 4;

	const VISIBILITY_DEFAULT = 3; // PERSONAL | ADMIN

	/** Priority constants for PriorityTrait */
	const PRIORITY_DEFAULT = 100;

	/** @var IConfig */
	protected $config;

	/** @var bool */
	private $userMountingAllowed = true;

	/** @var string[] */
	private $userMountingBackends = [];

	/** @var Backend[] */
	private $backends = [];

	/** @var IBackendProvider[] */
	private $backendProviders = [];

	/**
	 * FIXME: USE config Interface
	 * @param IConfig $config
	 */
	public function __construct(
		//IConfig $config
	) {
		//$this->config = $config;

		// Load config values
		/*if ($this->config->getAppValue('files_external', 'allow_user_mounting', 'yes') !== 'yes') {
			$this->userMountingAllowed = false;
		}
		$this->userMountingBackends = explode(',',
			$this->config->getAppValue('files_external', 'user_mounting_backends', '')
		);

		// if no backend is in the list an empty string is in the array and user mounting is disabled
		if ($this->userMountingBackends === ['']) {
			$this->userMountingAllowed = false;
		}*/
			$this->userMountingAllowed = true;
	}

	/**
	 * Register a backend provider
	 *
	 * @since 9.1.0
	 * @param IBackendProvider $provider
	 */
	public function registerBackendProvider(IBackendProvider $provider) {
		$this->backendProviders[] = $provider;
	}

	private function loadBackendProviders() {
		foreach ($this->backendProviders as $provider) {
			$this->registerBackends($provider->getBackends());
		}
		$this->backendProviders = [];
	}

	/**
	 * Register a backend
	 *
	 * @deprecated 9.1.0 use registerBackendProvider()
	 * @param Backend $backend
	 */
	public function registerBackend(Backend $backend) {
		if (!$this->isAllowedUserBackend($backend)) {
			$backend->removeVisibility(BackendService::VISIBILITY_PERSONAL);
		}
		foreach ($backend->getIdentifierAliases() as $alias) {
			$this->backends[$alias] = $backend;
		}
	}

	/**
	 * @deprecated 9.1.0 use registerBackendProvider()
	 * @param Backend[] $backends
	 */
	public function registerBackends(array $backends) {
		foreach ($backends as $backend) {
			$this->registerBackend($backend);
		}
	}

	/**
	 * Get all backends
	 *
	 * @return Backend[]
	 */
	public function getBackends() {
		$this->loadBackendProviders();
		// only return real identifiers, no aliases
		$backends = [];
		foreach ($this->backends as $backend) {
			$backends[$backend->getIdentifier()] = $backend;
		}
		return $backends;
	}

	/**
	 * Get all available backends
	 *
	 * @return Backend[]
	 */
	public function getAvailableBackends() {
		return array_filter($this->getBackends(), function($backend) {
			return !($backend->checkDependencies());
		});
	}

	/**
	 * @param string $identifier
	 * @return Backend|null
	 */
	public function getBackend($identifier) {
		$this->loadBackendProviders();
		if (isset($this->backends[$identifier])) {
			return $this->backends[$identifier];
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public function isUserMountingAllowed() {
		return $this->userMountingAllowed;
	}

	/**
	 * Check a backend if a user is allowed to mount it
	 * FIXME: really use this
	 * @param Backend $backend
	 * @return bool
	 */
	protected function isAllowedUserBackend(Backend $backend) {
		return true;
		if ($this->userMountingAllowed &&
			array_intersect($backend->getIdentifierAliases(), $this->userMountingBackends)
		) {
			return true;
		}
		return false;
	}
	
	public function getBackendByUri($uri) {
		$backend = false;

		$be =  $this->getBackends();
		foreach ($be as $b) {
			if ($b->checkUri($uri)) {
				$backend = $b;
				break;
			}
		}
		if (!$backend)
			throw new BackendException("no backends aviable");

		return $backend;
	}
	
	/**
	 * list update status data from backend 
	 * @param  [type] $recordset [description]
	 * @return [array]            [description]
	 */
	public function updateStatusList($recordset) {
		$rows = [];
		foreach ($recordset as $row) {
			//error_log($row['GID']);
			// $backend = $this->getBackendByUri($row['']);
			$status = $this->updateStatus($row['GID']);
			$rows[] = $status;
		}
		
		return $rows;
	}
	
	/**
	 * get status from backend 
	 * @param  [array] $row [row of queue database]
	 * @return [array] map status 
	 */
	public function updateStatus($row) {
		$ba = $this->getBackends();
		$b = array_pop($ba);
		
		$Status = $b->getStatus($row['GID']);
		
		$Progress = 0;
		if ($Status['result']['totalLength'] > 0) {
				$Progress = $Status['result']['completedLength'] / $Status['result']['totalLength'];
		}
		$DLStatus = Tools::getDownloadStatusID($Status['result']['status']);
		$ProgressString = Tools::getProgressString(
				$Status['result']['completedLength'],
				$Status['result']['totalLength'],
				$Progress
				);
			
		$row = [
		  'FILENAME_SHORT' => $row['FILENAME'],
			'GID' => $row['GID'],
			'PROGRESSVAL' => round((($Progress) * 100), 2),
			'PROGRESS' => [
					'Message' => null,
					'ProgressString' => is_null($ProgressString)?'N_A':$ProgressString,
					'NumSeeders' => isset($Status['result']['bittorrent']) && $Progress < 1?$Status['result']['numSeeders']:null,
					'UploadLength' => isset($Status['result']['bittorrent']) && $Progress == 1?Tools::formatSizeUnits($Status['result']['uploadLength']):null,
					'Ratio' => isset($Status['result']['bittorrent'])?round(($Status['result']['uploadLength'] / $Status['result']['completedLength']), 2):null
			],
			'STATUS' => [
					'Value' => isset($Status['result']['status']) ?($row['STATUS'] == 4?'Removed':ucfirst($Status['result']['status'])):'N_A',
					'Seeding' => isset($Status['result']['bittorrent']) && $Progress == 1 && $DLStatus != 3?true:false
			],
			'STATUSID' => $row['STATUS'] == 4?4:$DLStatus,
			'SPEED' => isset($Status['result']['downloadSpeed'])
					?($Progress == 1
							?(isset($Status['result']['bittorrent'])
									?($Status['result']['uploadSpeed'] == 0
											?'--'
											:Tools::formatSizeUnits($Status['result']['uploadSpeed']).'/s')
									:'--')
							:($DLStatus == 4
									?'--'
									:Tools::formatSizeUnits($Status['result']['downloadSpeed']).'/s'))
					:'N_A',
			'FILENAME' => $row['FILENAME'],
			'PROTO' => $row['PROTOCOL'],
			'ISTORRENT' => isset($Status['result']['bittorrent']),
			];

		return $row;
	}
	
}
