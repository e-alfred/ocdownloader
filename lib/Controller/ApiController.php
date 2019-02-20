<?php

namespace OCA\ocDownloader\Controller;

use OCA\ocDownloader\Backend\BackendException;
use OCA\ocDownloader\Service\BackendService;
use OCA\ocDownloader\Service\DBService;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;

use OCP\IRequest;
use OCP\IUserSession;
use OCP\Files\IRootFolder;

class ApiController extends OCSController {

  /** @var OCA\ocDownloader\Service\BackendService */
  protected $backendService;
  
  /** @var OCA\ocDownloader\Service\DBService */
  protected $dbService;

  protected $userSession;
  
  /**
   * Constructor
   * @param mixed $appName AppName
   * @param IRequest $request
   * @param IUserSession $userSession
  *  @param BackendService $backendService
  */
	
  public function __construct($appName,
      IRequest $request,
      IUserSession $userSession,
      BackendService $backendService,
      DBService $dbService) {
          parent::__construct($appName, $request);
          $this->backendService = $backendService;
          $this->userSession = $userSession;
          $this->dbService = $dbService;
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
  
  public function getQueue($VIEW) {
    $filter = ($VIEW == 'all' ? [0,1,2,3,4] : [Tools::getDownloadStatusID($VIEW)]);
    
    try {
    
      $list = $this->dbService->getQueueByUser($this->userSession->getUser(), $filter);
       
        return new DataResponse(array(
            'SQL' => $SQL,
            'ERROR' => false,
            'MESSAGE' => null,
            'QUEUE' => $this->backendService->updateStatusList($list),
            #'COUNTER' => Tools::getCounters(self::$DbType, self::$CurrentUID)
        ));
        
      } catch (BackendException $e) {

        return new DataResponse([
            'ERROR' => true,
            'MESSAGE' => $e->getMessage()
        ]);

      }
  }

}
