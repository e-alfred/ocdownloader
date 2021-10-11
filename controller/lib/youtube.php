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

namespace OCA\ocDownloader\Controller\Lib;

class YouTube
{
    private $YTDLBinary = null;
    private $YTDLAudioFormat = null;
    private $YTDLVideoFormat = null;
    private $URL = null;
    private $ForceIPv4 = true;
    private $ProxyAddress = null;
    private $ProxyPort = 0;

    public function __construct($YTDLBinary, $URL, $YTDLAudioFormat, $YTDLVideoFormat)
    {
        $this->YTDLBinary = $YTDLBinary;
        $this->YTDLAudioFormat = $YTDLAudioFormat;
        $this->YTDLVideoFormat = $YTDLVideoFormat;
        $this->URL = $URL;
    }

    public function setForceIPv4($ForceIPv4)
    {
        $this->ForceIPv4 = $ForceIPv4;
    }

    public function setProxy($ProxyAddress, $ProxyPort)
    {
        $this->ProxyAddress = $ProxyAddress;
        $this->ProxyPort = $ProxyPort;
    }

    public function getVideoData($ExtractAudio = false)
    {
        $Proxy = null;
        if (!is_null($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536) {
            $Proxy = ' --proxy ' . rtrim($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
        }


        //youtube multibyte support
        //get avalible locales
        $locale_array  = explode("\n", trim(shell_exec("locale -a|grep .utf8")));
        $locale=array_pop($locale_array);
        //set locale
        putenv('LANG='.$locale);

        $fAudio = escapeshellarg($this->YTDLAudioFormat);
        $fVideo = escapeshellarg($this->YTDLVideoFormat);
        $Output = shell_exec(
            $this->YTDLBinary.' -i \''.$this->URL.'\' --get-url --get-filename'
            .(!is_null($fAudio) ? " -f $fAudio" : '')
            .(!is_null($fVideo) ? " -f $fVideo" : '')
            .($ExtractAudio? " -x" : '')
            .($this->ForceIPv4 ? ' -4' : '')
            .(is_null($Proxy) ? '' : $Proxy)
        );

        $index=(preg_match('/&index=(\d+)/', $this->URL, $current))?$current[1]:1;

        if (!is_null($Output)) {
            $Output = explode("\n", $Output);

            if (count($Output) >= 2) {
                $OutProcessed = array();
                $current_index=1;
                for ($I = 0; $I < count($Output); $I++) {
                    if (mb_strlen(trim($Output[$I])) > 0) {
                      if (mb_strpos(urldecode($Output[$I]), 'http://') === 0 || mb_strpos(urldecode($Output[$I]), 'https://') === 0) {
                          if (mb_strpos(urldecode($Output[$I]), '&mime=audio/') !== false)
                              $OutProcessed['AUDIO'] = $Output[$I];
                          else
                              $OutProcessed['VIDEO'] = $Output[$I];
                        } else {
                            $OutProcessed['FULLNAME'] = $Output[$I];
                        }
                    }
                 if ((!empty($OutProcessed['VIDEO']) || !empty($OutProcessed['AUDIO'])) && !empty($OutProcessed['FULLNAME']))
                    {
                        if ($index == $current_index)
                        {
                            break;
                        } else {
                            $OutProcessed = array();
                            $current_index++;
                        }
                    }
                }
                return $OutProcessed;
            }
        }
        return null;
    }

}
