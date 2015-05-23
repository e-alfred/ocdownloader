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
?>
<form id="ocdownloader" class="section">
	<h2>ocDownloader</h2>
	<p id="YTBinary">
		<label for="YTBinaryInput">YouTube DL Binary Path</label>
		<input type="text" name="YTBinaryInput" id="YTBinaryInput" style="width:400px" value="<?php print (isset ($_['OCDS_YTDLBinary']) ? $_['OCDS_YTDLBinary'] : '/usr/local/bin/youtube-dl'); ?>" />
		<span id="YTBinaryLoader" class="OCDSLoader icon-loading-small"></span>
	</p>
</form>