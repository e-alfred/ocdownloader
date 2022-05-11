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
use Psr\Log\LoggerInterface;
use OC\ForbiddenException;
use OC_Util;
use OCA\ocDownloader\Controller\Lib\Settings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IL10N;
use OCP\IRequest;
use Throwable;

class Index extends Controller
{
    private $DbType = 0;
    private $CurrentUID = null;
    private $Settings = null;
    private $WhichDownloader = null;
    private $L10N = null;
    private $AllowProtocolHTTP = null;
    private $AllowProtocolFTP = null;
    private $AllowProtocolYT = null;
    private $AllowProtocolBT = null;
    private $DownloadsFolder = null;

    public function __construct($AppName, IRequest $Request, $CurrentUID, IL10N $L10N)
    {
        parent::__construct($AppName, $Request);
        $this->CurrentUID = $CurrentUID;
        $this->L10N = $L10N;

        if (strcmp(\OC::$server->getConfig()->getSystemValue('dbtype'), 'pgsql') == 0) {
            $this->DbType = 1;
        }

        OC_Util::setupFS();

        $this->Settings = new Settings();
        $this->Settings->setKey('WhichDownloader');
        $this->WhichDownloader = $this->Settings->getValue();
        $this->WhichDownloader = is_null($this->WhichDownloader) ? 'ARIA2' : $this->WhichDownloader;

        $this->Settings->setKey('AllowProtocolHTTP');
        $this->AllowProtocolHTTP = $this->Settings->getValue();
        $this->AllowProtocolHTTP = is_null($this->AllowProtocolHTTP) || \OC_User::isAdminUser($this->CurrentUID)
            ? true : strcmp($this->AllowProtocolHTTP, 'Y') == 0;
        $this->Settings->setKey('AllowProtocolFTP');
        $this->AllowProtocolFTP = $this->Settings->getValue();
        $this->AllowProtocolFTP = is_null($this->AllowProtocolFTP) || \OC_User::isAdminUser($this->CurrentUID)
            ? true : strcmp($this->AllowProtocolFTP, 'Y') == 0;
        $this->Settings->setKey('AllowProtocolYT');
        $this->AllowProtocolYT = $this->Settings->getValue();
        $this->AllowProtocolYT = is_null($this->AllowProtocolYT) || \OC_User::isAdminUser($this->CurrentUID)
            ? true : strcmp($this->AllowProtocolYT, 'Y') == 0;
        $this->Settings->setKey('AllowProtocolBT');
        $this->AllowProtocolBT = $this->Settings->getValue();
        $this->AllowProtocolBT = is_null($this->AllowProtocolBT) || \OC_User::isAdminUser($this->CurrentUID)
            ? true : strcmp($this->AllowProtocolBT, 'Y') == 0;

        $this->Settings->setTable('personal');
        $this->Settings->setUID($this->CurrentUID);

        $this->Settings->setKey('DownloadsFolder');
        $this->DownloadsFolder = $this->Settings->getValue();
        $this->DownloadsFolder = '/' .(is_null($this->DownloadsFolder) ? 'Downloads' : $this->DownloadsFolder);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function add()
    {
        $this->Settings->setTable('personal');
        $this->Settings->setUID($this->CurrentUID);
        $this->Settings->setKey('TorrentsFolder');
        $TorrentsFolder = $this->Settings->getValue();

        self::syncDownloadsFolder();
        $response = new TemplateResponse('ocdownloader', 'add', [
            'PAGE' => 0,
            'TTSFOLD' => $TorrentsFolder,
            'WD' => $this->WhichDownloader,
            'AllowProtocolHTTP' => $this->AllowProtocolHTTP,
            'AllowProtocolFTP' => $this->AllowProtocolFTP,
            'AllowProtocolYT' => $this->AllowProtocolYT,
            'AllowProtocolBT' => $this->AllowProtocolBT
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function all()
    {
        self::syncDownloadsFolder();
        $response = new TemplateResponse('ocdownloader', 'all', [
            'PAGE' => 1,
            'WD' => $this->WhichDownloader
        ]);


        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function completes()
    {
        self::syncDownloadsFolder();
        $response = new TemplateResponse('ocdownloader', 'completes', [
            'PAGE' => 2,
            'WD' => $this->WhichDownloader
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function actives()
    {
        self::syncDownloadsFolder();
        $response = new TemplateResponse('ocdownloader', 'actives', [
            'PAGE' => 3,
            'WD' => $this->WhichDownloader
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function waitings()
    {
        self::syncDownloadsFolder();
        if (strcmp($this->WhichDownloader, 'ARIA2') != 0) {
            //return $this->L10N->t('You are using %s ! This page is only available with the following engines : ', $this->WhichDownloader) . 'ARIA2';
        }
        $response = new TemplateResponse('ocdownloader', 'waitings', [
            'PAGE' => 4,
            'WD' => $this->WhichDownloader
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function stopped()
    {
        self::syncDownloadsFolder();
        if (strcmp($this->WhichDownloader, 'ARIA2') != 0) {
            //return $this->L10N->t('You are using %s ! This page is only available with the following engines : ', $this->WhichDownloader) . 'ARIA2';
        }
        $response = new TemplateResponse('ocdownloader', 'stopped', [
            'PAGE' => 5,
            'WD' => $this->WhichDownloader
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function removed()
    {
        self::syncDownloadsFolder();
        $response = new TemplateResponse('ocdownloader', 'removed', [
            'PAGE' => 6,
            'WD' => $this->WhichDownloader
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->allowInlineStyle();

        $response->setContentSecurityPolicy($csp);

        return $response;
    }

     /**
     * Testfunction by Nibbels
     * Fix for https://github.com/DjazzLab/ocdownloader/issues/44
     */
    protected function syncDownloadsFolder()
    {
        // check whether the download folder exists, if not create it
        if (!\OC\Files\Filesystem::is_dir($this->DownloadsFolder)) {
            \OC\Files\Filesystem::mkdir($this->DownloadsFolder);
        }

        $user = $this->CurrentUID; //or normally \OC::$server->getUserSession()->getUser()->getUID();
        $scanner = new \OC\Files\Utils\Scanner($user, \OC::$server->getDatabaseConnection(), \OC::$server->query(IEventDispatcher::class), \OC::$server->query(LoggerInterface::class));
        $path = '/'.$user.'/files/'.ltrim($this->DownloadsFolder, '/\\');
        try {
            $scanner->scan($path);
        } catch (ForbiddenException $e) {
            //$arr['forbidden'] = 1;
            //"<error>Home storage for user $user not writable</error>" "Make sure you're running the scan command only as the user the web server runs as"
        } catch (Throwable $e) {
            //$arr['exception'] = 1;
            //'<error>Exception during scan: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</error>');
        }
    }
}
