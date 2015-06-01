<?php
/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
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
	
	public function __construct ($YTDLBinary, $URL)
	{
		$this->YTDLBinary = $YTDLBinary;
		$this->URL = $URL;
	}
	
	public function SetForceIPv4 ($ForceIPv4)
	{
		$this->ForceIPv4 = $ForceIPv4;
	}
	
	public function GetVideoData ($ExtractAudio = false)
	{
		exec ($this->YTDLBinary . ' -i \'' . $this->URL . '\' --get-url --get-filename' . ($ExtractAudio ? ' -x' : ' -f best') . ($this->ForceIPv4 ? ' -4' : ''), $Output, $Return);
		
		if ($Return == 0)
		{
			$OutProcessed = Array ();
			for ($I = 0; $I < count ($Output); $I++)
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
			return $OutProcessed;
		}
		return null;
	}
}