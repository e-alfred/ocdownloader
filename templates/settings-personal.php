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
	<p><span class="info">Leave fields blank to erase a setting value</span><span id="OCDSLoader" class="OCDSLoader icon-loading-small"></span><span class="msg" id="OCDSMsg"></span></p>
	<p id="DownloadsFolder">
		<label for="OCDDownloadsFolder">Default Downloads Folder</label>
		<input type="text" class="OCDDownloadsFolder" id="OCDDownloadsFolder" value="<?php print(isset ($_['OCDS_DownloadsFolder']) ? '/' . $_['OCDS_DownloadsFolder'] : '/Downloads'); ?>" />
		<input type="button" value="Save" data-rel="OCDDownloadsFolder" />
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p id="TorrentsFolder">
		<label for="OCDTorrentsFolder">Default Torrents Folder</label>
		<input type="text" class="OCDTorrentsFolder" id="OCDTorrentsFolder" value="<?php print(isset ($_['OCDS_TorrentsFolder']) ? '/' . $_['OCDS_TorrentsFolder'] : '/Downloads/Files/Torrents'); ?>" />
		<input type="button" value="Save" data-rel="OCDTorrentsFolder" />
	</p>
</form>