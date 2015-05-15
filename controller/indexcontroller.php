<?php
namespace OCA\ocDownloader\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;

class IndexController extends Controller
{
      public function __construct ($AppName, IRequest $Request)
      {
            parent::__construct($AppName, $Request);
      }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function add ()
      {
            $SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue` WHERE IS_DELETED = ?';
            $Query = \OCP\DB::prepare ($SQL);
            $Result = $Query->execute (Array (0));
            
            return new TemplateResponse ('ocdownloader', 'add', [ 'NBELT' => $Query->rowCount(), 'QUEUE' => $Result ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function actives ()
      {
            $SQL = 'SELECT * FROM `*PREFIX*ocdownloader_queue` WHERE IS_ACTIVE = ? AND IS_DELETED = ?';
            $Query = \OCP\DB::prepare ($SQL);
            $Result = $Query->execute (Array (1, 0));
            
            return new TemplateResponse('ocdownloader', 'actives', [ 'NBELT' => $Query->rowCount(), 'QUEUE' => $Result ]);
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function waitings ()
      {
            return new TemplateResponse('ocdownloader', 'waitings');
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function stopped ()
      {
            return new TemplateResponse('ocdownloader', 'stopped');
      }
      
      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
      public function history ()
      {
            return new TemplateResponse('ocdownloader', 'history');
      }
}