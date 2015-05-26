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
	
	public function __construct ($YTDLBinary, $URL)
	{
		$this->YTDLBinary = $YTDLBinary;
		$this->URL = $URL;
	}
	
	public function GetFileName ()
	{
		exec ($this->YTDLBinary . ' -i \'' . $this->URL . '\' --get-filename', $Output, $Return);
		
		if ($Return == 0 && count ($Output) == 1 && strlen (trim ($Output[0])) > 0)
		{
		    return $Output[0];
		}
		return null;
	}
	
	public function Download ($OutFileName, $GID, $OPTIONSCmd)
	{
		$LogFile = '/tmp/' . $GID . '.log';
		
		exec ('$(which nohup) nice -n 10 ' . $this->YTDLBinary . ' -i \'' . $this->URL . '\'' . $OPTIONSCmd . '--newline --output "' . $OutFileName . '" >' . $LogFile . ' 2>&1 &', $Output, $Return);
		
		if ($Return == 0)
		{
			return true;
		}
		return false;
	}
}