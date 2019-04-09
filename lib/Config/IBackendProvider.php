<?php


namespace OCA\ocDownloader\Config;

use \OCA\Files_External\Lib\Backend\Backend;

/**
 * Provider of external storage backends
 * @since 9.1.0
 */
interface IBackendProvider {

	/**
	 * @since 9.1.0
	 * @return Backend[]
	 */
	public function getBackends();

}
