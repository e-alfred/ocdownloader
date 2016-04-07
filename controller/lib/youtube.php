<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Creative Commons BY-SA License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

namespace OCA\ocDownloader\Controller\Lib;

class YouTube
{
	private $YTDLBinary = null;
	private $URL = null;
	private $ForceIPv4 = true;
	private $ProxyAddress = null;
	private $ProxyPort = 0;
	
	public function __construct ($YTDLBinary, $URL)
	{
		$this->YTDLBinary = $YTDLBinary;
		$this->URL = $URL;
	}
	
	public function SetForceIPv4 ($ForceIPv4)
	{
		$this->ForceIPv4 = $ForceIPv4;
	}
	
	public function SetProxy ($ProxyAddress, $ProxyPort)
	{
		$this->ProxyAddress = $ProxyAddress;
		$this->ProxyPort = $ProxyPort;
	}
	
	public function GetVideoData ($ExtractAudio = false)
	{
		$Proxy = null;
		if (!is_null ($this->ProxyAddress) && $this->ProxyPort > 0 && $this->ProxyPort <= 65536)
		{
			$Proxy = ' --proxy ' . rtrim($this->ProxyAddress, '/') . ':' . $this->ProxyPort;
		}
		
		//youtube multibyte support
		putenv('LANG=en_US.UTF-8');
		
		$Output = shell_exec ($this->YTDLBinary . ' -i \'' . $this->URL . '\' --get-url --get-filename' . ($ExtractAudio ? ' -f bestaudio -x' : ' -f best') . ($this->ForceIPv4 ? ' -4' : '') . (is_null ($Proxy) ? '' : $Proxy));
		
		if (!is_null ($Output))
		{
			$Output = explode ("\n", $Output);
			if (count ($Output) >= 2)
			{
				$OutProcessed = Array ();
				for ($I = 0; $I < count ($Output); $I++)
				{
					if (strlen (trim ($Output[$I])) > 0)
					{
						if (strpos (urldecode ($Output[$I]), 'https://') == 0 && strpos (urldecode ($Output[$I]), '&mime=video/') !== false)
						{
							$OutProcessed['VIDEO'] = $Output[$I];
						}
						elseif (strpos (urldecode ($Output[$I]), 'https://') == 0 && strpos (urldecode ($Output[$I]), '&mime=audio/') !== false)
						{
							$OutProcessed['AUDIO'] = $Output[$I];
						}
						else
						{
							$OutProcessed['FULLNAME'] = $Output[$I];
						}
					}
				}
				return $OutProcessed;
			}
		}
		return null;
	}
}
