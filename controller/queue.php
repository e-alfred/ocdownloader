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

namespace OCA\ocDownloader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;

use OCP\IL10N;
use OCP\IRequest;

use OCA\ocDownloader\Controller\Lib\Aria2;
use OCA\ocDownloader\Controller\Lib\CURL;
use OCA\ocDownloader\Controller\Lib\Tools;
use OCA\ocDownloader\Controller\Lib\Settings;

class Queue extends Controller
{
    private $UserStorage;
    private $CurrentUID;
    private $WhichDownloader = 0;
    private $DownloadsFolder;

    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);

        $this->CurrentUID = $CurrentUID;

        $Settings = new Settings();
        $Settings->setKey('WhichDownloader');
        $this->WhichDownloader = $Settings->getValue();
        $this->WhichDownloader = is_null($this->WhichDownloader) ? 0 :(strcmp($this->WhichDownloader, 'ARIA2') == 0 ? 0 : 1); // 0 means ARIA2, 1 means CURL

        $Settings->setTable('personal');
        $Settings->setUID($this->CurrentUID);
        $Settings->setKey('DownloadsFolder');
        $this->DownloadsFolder = $Settings->getValue();
        $this->DownloadsFolder = '/' .(is_null($this->DownloadsFolder)?'Downloads':$this->DownloadsFolder);

        $this->L10N = $L10N;
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function get()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['VIEW']) && strlen(trim($_POST['VIEW'])) > 0) {

              $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
              $qb->select('*')
              ->from('ocdownloader_queue')
              ->where($qb->expr()->eq('uid', $qb->createNamedParameter($this->CurrentUID)))
              ->orderBy('timestamp', 'asc');

              switch ($_POST['VIEW']) {
      					case 'completes':
      						$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(0)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					case 'removed':
      						$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(4)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					case 'actives':
      						$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(1)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					case 'stopped':
      						$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(3)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					case 'waitings':
      						$qb->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(2)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					case 'all':
      						$qb->andWhere($qb->expr()->lte('status', $qb->createNamedParameter(4)))
      							->andWhere($qb->expr()->in('is_cleaned', $qb->createNamedParameter([0, 1], IQueryBuilder::PARAM_INT_ARRAY)));
      						break;
      					default: // add view
      						$qb->andWhere($qb->expr()->lte('status', $qb->createNamedParameter(3)))
      							->andWhere($qb->expr()->eq('is_cleaned', $qb->createNamedParameter(0)));
      						break;
      				}
      				$Request = $qb->execute();

      				$Queue = [];
      				$DownloadUpdated = false;
      				while ($Row = $Request->fetch()) {
                    $Status =($this->WhichDownloader == 0
                        ?Aria2::tellStatus($Row['GID']):CURL::tellStatus($Row['GID']));
                    $DLStatus = 5; // Error

                    if (!is_null($Status)) {
                        if (!isset($Status['error'])) {
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

                            $Queue[] = array(
                                'GID' => $Row['GID'],
                                'PROGRESSVAL' => round((($Progress) * 100), 2) . '%',
                                'PROGRESS' =>(is_null($ProgressString)
                                    ?(string)$this->L10N->t('N/A')
                                    :$ProgressString).(isset($Status['result']['bittorrent']) && $Progress < 1
                                        ?' - <strong>'.$this->L10N->t('Seeders').'</strong>: '.$Status['result']['numSeeders']
                                        :(isset($Status['result']['bittorrent']) && $Progress == 1
                                            ?' - <strong>'.$this->L10N->t('Uploaded').'</strong>: '.Tools::formatSizeUnits($Status['result']['uploadLength']).' - <strong>' . $this->L10N->t('Ratio') . '</strong>: ' . round(($Status['result']['uploadLength'] / $Status['result']['completedLength']), 2) : '')),
                                'STATUS' => isset($Status['result']['status'])
                                    ? $this->L10N->t(
                                        $Row['STATUS'] == 4?'Removed':ucfirst($Status['result']['status'])
                                    ).(isset($Status['result']['bittorrent']) && $Progress == 1 && $DLStatus != 3?' - '
                                    .$this->L10N->t('Seeding') : '') :(string)$this->L10N->t('N/A'),
                                'STATUSID' => $Row['STATUS'] == 4 ? 4 : $DLStatus,
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
                                    :(string)$this->L10N->t('N/A'),
                                'FILENAME' => $Row['FILENAME'],
                                'FILENAME_SHORT' => Tools::getShortFilename($Row['FILENAME']),
                                'PROTO' => $Row['PROTOCOL'],
                                'ISTORRENT' => isset($Status['result']['bittorrent'])
                            );

                            if ($Row['STATUS'] != $DLStatus) {
                              $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                              $qb->update('ocdownloader_queue')
                                ->set('STATUS', $qb->createNamedParameter($DLStatus))
                                ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                                ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($Row['GID'])))
                                ->andwhere($qb->expr()->neq('STATUS', $qb->createNamedParameter(4)));
                              $qb->execute();

                              $DownloadUpdated = true;
                            }
                        } else {
                            $Queue[] = array(
                                  'GID' => $Row['GID'],
                                  'PROGRESSVAL' => 0,
                                  'PROGRESS' =>(string)$this->L10N->t('Error, GID not found !'),
                                  'STATUS' =>(string)$this->L10N->t('N/A'),
                                  'STATUSID' => $DLStatus,
                                  'SPEED' =>(string)$this->L10N->t('N/A'),
                                  'FILENAME' => $Row['FILENAME'],
                                  'FILENAME_SHORT' => Tools::getShortFilename($Row['FILENAME']),
                                  'PROTO' => $Row['PROTOCOL'],
                                  'ISTORRENT' => isset($Status['result']['bittorrent'])
                            );
                        }
                    } else {
                        $Queue[] = array(
                              'GID' => $Row['GID'],
                              'PROGRESSVAL' => 0,
                              'PROGRESS' => $this->WhichDownloader==0
                                ?(string)$this->L10N->t('Returned status is null ! Is Aria2c running as a daemon ?')
                                :(string)$this->L10N->t('Unable to find download status file %s', '/tmp/'
                                .$Row['GID'].'.curl'),
                              'STATUS' =>(string)$this->L10N->t('N/A'),
                              'STATUSID' => $DLStatus,
                              'SPEED' =>(string)$this->L10N->t('N/A'),
                              'FILENAME' => $Row['FILENAME'],
                              'FILENAME_SHORT' => Tools::getShortFilename($Row['FILENAME']),
                              'PROTO' => $Row['PROTOCOL'],
                              'ISTORRENT' => isset($Status['result']['bittorrent'])
                        );
                    }
                }

				// Start rescan on update
				if ($DownloadUpdated) {
          \OC\Files\Filesystem::touch($this->DownloadsFolder . $Row['FILENAME']);
				}

                return new JSONResponse(
                    array(
                        'ERROR' => false,
                        'QUEUE' => $Queue,
                        'COUNTER' => Tools::getCounters($this->CurrentUID)
                    )
                );
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function count()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            return new JSONResponse(
                array('ERROR' => false, 'COUNTER' => Tools::getCounters($this->CurrentUID))
            );
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function pause()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if ($this->WhichDownloader == 0) {
                if (isset($_POST['GID']) && strlen(trim($_POST['GID'])) > 0) {
                    $Status = Aria2::tellStatus($_POST['GID']);

                    $Pause['result'] = $_POST['GID'];
                    if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                        && strcmp($Status['result']['status'], 'complete') != 0
                        && strcmp($Status['result']['status'], 'active') == 0) {
                            $Pause = Aria2::pause($_POST['GID']);
                    }

                    if (strcmp($Pause['result'], $_POST['GID']) == 0) {
                      $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                      $qb->update('ocdownloader_queue')
                        ->set('STATUS', $qb->createNamedParameter(3))
                        ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                        ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($_POST['GID'])));
                      $qb->execute();

                      return new JSONResponse(
                            array('ERROR' => false, 'MESSAGE' =>(string)$this->L10N->t('The download has been paused'))
                        );
                    } else {
                        return new JSONResponse(
                            array(
                                'ERROR' => true,
                                'MESSAGE' =>(string)$this->L10N->t('An error occurred while pausing the download')
                            )
                        );
                    }
                } else {
                    return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
                }
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function unPause()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if ($this->WhichDownloader == 0) {
                if (isset($_POST['GID']) && strlen(trim($_POST['GID'])) > 0) {
                    $Status = Aria2::tellStatus($_POST['GID']);

                    $UnPause['result'] = $_POST['GID'];
                    if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                        && strcmp($Status['result']['status'], 'complete') != 0
                        && strcmp($Status['result']['status'], 'paused') == 0) {
                            $UnPause = Aria2::unpause($_POST['GID']);
                    }

                    if (strcmp($UnPause['result'], $_POST['GID']) == 0) {
                        $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                        $qb->update('ocdownloader_queue')
                          ->set('STATUS', $qb->createNamedParameter(1))
                          ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                          ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($_POST['GID'])));
                        $qb->execute();

                        return new JSONResponse(
                            array(
                                'ERROR' => false,
                                'MESSAGE' =>(string)$this->L10N->t('The download has been unpaused')
                            )
                        );
                    } else {
                        return new JSONResponse(
                            array(
                                'ERROR' => true,
                                'MESSAGE' =>(string)$this->L10N->t('An error occurred while unpausing the download')
                            )
                        );
                    }
                } else {
                    return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
                }
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function hide()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GID']) && strlen(trim($_POST['GID'])) > 0) {
              $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
              $qb->update('ocdownloader_queue')
                ->set('IS_CLEANED', $qb->createNamedParameter(1))
                ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($_POST['GID'])));
              $qb->execute();

                return new JSONResponse(
                    array('ERROR' => false, 'MESSAGE' =>(string)$this->L10N->t('The download has been cleaned'))
                );
            } else {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function hideAll()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GIDS']) && count($_POST['GIDS']) > 0) {
                $Queue = array();

                foreach ($_POST['GIDS'] as $GID) {
                    $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                    $qb->update('ocdownloader_queue')
                      ->set('IS_CLEANED', $qb->createNamedParameter(1))
                      ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                      ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($GID)));
                    $qb->execute();

                    $Queue[] = array(
                          'GID' => $GID
                    );
                }

                return new JSONResponse(
                    array(
                        'ERROR' => false,
                        'MESSAGE' =>(string)$this->L10N->t('All downloads have been cleaned'),
                        'QUEUE' => $Queue
                    )
                );
            } else {
                return new JSONResponse(
                    array(
                        'ERROR' => true,
                        'MESSAGE' =>(string)$this->L10N->t('No GIDS in the download queue')
                    )
                );
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function remove()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GID']) && strlen(trim($_POST['GID'])) > 0) {
                $Status =(
                    $this->WhichDownloader == 0
                    ?Aria2::tellStatus($_POST['GID'])
                    :CURL::tellStatus($_POST['GID'])
                );

                $Remove['result'] = $_POST['GID'];
                if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                    && strcmp($Status['result']['status'], 'complete') != 0) {
                    $Remove =(
                        $this->WhichDownloader == 0
                        ? Aria2::remove($_POST['GID'])
                        :CURL::remove($Status['result'])
                    );
                } elseif ($this->WhichDownloader != 0 && strcmp($Status['result']['status'], 'complete') == 0) {
                    $Remove = CURL::remove($Status['result']);
                }

                if (!is_null($Remove) && strcmp($Remove['result'], $_POST['GID']) == 0) {
                    $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                    $qb->update('ocdownloader_queue')
                        ->set('STATUS', $qb->createNamedParameter(4))
                        ->set('IS_CLEANED', $qb->createNamedParameter(1))
                        ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                        ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($_POST['GID'])));
                    $qb->execute();

                    return new JSONResponse(
                        array(
                            'ERROR' => false,
                            'MESSAGE' =>(string)$this->L10N->t('The download has been removed')
                        )
                    );
                } else {
                    return new JSONResponse(
                        array(
                            'ERROR' => true,
                            'MESSAGE' =>(string)$this->L10N->t('An error occurred while removing the download')
                        )
                    );
                }
            } else {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function removeAll()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GIDS']) && count($_POST['GIDS']) > 0) {
                $GIDS = array();

                foreach ($_POST['GIDS'] as $GID) {
                    $Status =($this->WhichDownloader == 0 ? Aria2::tellStatus($GID) : CURL::tellStatus($GID));
                    $Remove = array('result' => $GID);

                    if (!isset($Status['error']) && strcmp($Status['result']['status'], 'error') != 0
                        && strcmp($Status['result']['status'], 'complete') != 0) {
                        $Remove =($this->WhichDownloader == 0 ? Aria2::remove($GID) : CURL::remove($Status['result']));
                    }

                    if (!is_null($Remove) && strcmp($Remove['result'], $GID) == 0) {
                        $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                        $qb->update('ocdownloader_queue')
                            ->set('STATUS', $qb->createNamedParameter(4))
                            ->set('IS_CLEANED', $qb->createNamedParameter(1))
                            ->where($qb->expr()->eq('UID', $qb->createNamedParameter($this->CurrentUID)))
                            ->andwhere($qb->expr()->eq('GID', $qb->createNamedParameter($GID)));
                        $qb->execute();

                        $GIDS[] = $GID;
                    }
                }

                return new JSONResponse(
                    array(
                        'ERROR' => false,
                        'MESSAGE' =>(string)$this->L10N->t('All downloads have been removed'),
                        'GIDS' => $GIDS
                    )
                );
            } else {
                return new JSONResponse(
                    array(
                        'ERROR' => true,
                        'MESSAGE' =>(string)$this->L10N->t('No GIDS in the download queue')
                    )
                );
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function completelyRemove()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GID']) && strlen(trim($_POST['GID'])) > 0) {
                $Status =(
                    $this->WhichDownloader == 0
                    ?Aria2::tellStatus($_POST['GID'])
                    :CURL::tellStatus($_POST['GID'])
                );

                if (!isset($Status['error']) && strcmp($Status['result']['status'], 'removed') == 0) {
                    $Remove =(
                        $this->WhichDownloader == 0
                        ? Aria2::removeDownloadResult($_POST['GID'])
                        :CURL::removeDownloadResult($_POST['GID'])
                    );
                }

                $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                $qb->delete()->from('ocdownloader_queue')
                    ->where($qb->expr()->eq('UID',$qb->createNamedParameter($this->CurrentUID)))
                    ->andwhere($qb->expr()->eq('GID',$qb->createNamedParameter($_POST['GID'])));
                $Request = $qb->execute();

                return new JSONResponse(
                    array(
                        'ERROR' => false,
                        'MESSAGE' =>(string)$this->L10N->t('The download has been totally removed')
                    )
                );
            } else {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }

      /**
       * @NoAdminRequired
       * @NoCSRFRequired
       */
    public function completelyRemoveAll()
    {
        header( 'Content-Type: application/json; charset=utf-8');

        try {
            if (isset($_POST['GIDS']) && count($_POST['GIDS']) > 0) {
                $GIDS = array();

                foreach ($_POST['GIDS'] as $GID) {
                    $Status =($this->WhichDownloader == 0 ? Aria2::tellStatus($GID) : CURL::tellStatus($GID));

                    if (!isset($Status['error']) && strcmp($Status['result']['status'], 'removed') == 0) {
                        $Remove =(
                            $this->WhichDownloader == 0
                            ?Aria2::removeDownloadResult($GID)
                            :CURL::removeDownloadResult($GID)
                        );
                    }

                    $qb = \OC::$server->getDatabaseConnection()->getQueryBuilder();
                    $qb->delete()->from('ocdownloader_queue')
                        ->where($qb->expr()->eq('UID',$qb->createNamedParameter($this->CurrentUID)))
                        ->andwhere($qb->expr()->eq('GID',$qb->createNamedParameter($GID)));
                    $Request = $qb->execute();

                    $GIDS[] = $GID;
                }

                return new JSONResponse(
                    array(
                        'ERROR' => false,
                        'MESSAGE' =>(string)$this->L10N->t('The download has been totally removed'),
                        'GIDS' => $GIDS
                    )
                );
            } else {
                return new JSONResponse(array('ERROR' => true, 'MESSAGE' =>(string)$this->L10N->t('Bad GID')));
            }
        } catch (Exception $E) {
            return new JSONResponse(array('ERROR' => true, 'MESSAGE' => $E->getMessage()));
        }
    }
}
