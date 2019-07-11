<?php

namespace OCA\ocDownloader\Controller\Lib;


class QuotaManager
{
    //Amount of space for downloading files
    private static $ReservedSpace = 0;

    /**
     * @param $URI
     * @return bool
     */
    public static function allowedByQuota($URI)
    {
        $SessionUID = \OC::$server->getUserSession()->getUser();
        $HomeDir = $SessionUID->getHome();
        $FreeSpace = \OC\Files\Filesystem::free_space($HomeDir);

        $FileSize = self::getSize($URI);

        return ($FileSize > $FreeSpace)? false : true;
    }

    /**
     * @param $URI
     * @return int|mixed, -1 if error
     */
    private static function getSize($URI)
    {
        $Handle = curl_init($URI);

        curl_setopt($Handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($Handle, CURLOPT_HEADER, TRUE);
        curl_setopt($Handle, CURLOPT_NOBODY, TRUE);

        $FileSize = -1;
        if (curl_exec($Handle) != false) {
            $FileSize = curl_getinfo($Handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        }

        curl_close($Handle);
        return $FileSize;
    }
}