<?php

namespace OCA\ocDownloader\Controller;

use OCA\ocDownloader\Backend\BackendException;
use OCA\ocDownloader\Service\BackendService;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;

use OCP\IRequest;
use OCP\Files\IRootFolder;

class ApiController extends OCSController {

  /** @var OCA\ocDownloader\Service\BackendService */
  protected $backendService;


  /**
   * Constructor
   * @param mixed $appName AppName
   * @param IRequest $request
  *  @param BackendService $backendService
  */
	public function __construct($appName,
      IRequest $request,
      BackendService $backendService) {
          parent::__construct($appName, $request);
		      $this->backendService = $backendService;
  }

  public function Add($URL, $OPTIONS) {
    try {
      $backend = $this->backendService->getBackendbyUri($URL);

      $DL = $backend->add($URL, $OPTIONS);

      return new DataResponse([
          'ERROR' => False,
          'FILENAME' =>  $DL['FILENAME']
      ]);

    } catch (BackendException $e) {

      return new DataResponse([
          'ERROR' => true,
          'MESSAGE' => $e->getMessage()
      ]);

    }

  }

  public function Handler ($URL) {
    try {
      $backend = $this->backendService->getBackendByUri($URL);
      $data  = Array(
        'ERROR' => False,
        'HANDLER' => $backend->getIdentifier()
      );
      $info = $backend->getInfo($URL);
      $options = $backend->getOptions($URL);
      if ($info) $data['INFO'] = $info;
      if ($options) $data['OPTIONS'] = $options;

      return new DataResponse($data);

    } catch (BackendException $e) {

      return new DataResponse([
          'ERROR' => true,
          'MESSAGE' => $e->getMessage()
      ]);

    }

  }

}
