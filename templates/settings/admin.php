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
	<p>
		<span class="info"><?php print ($l->t ('Leave fields blank to reset a setting value')); ?></span>
		<span id="OCDSLoader" class="OCDSLoader icon-loading-small"></span><span class="msg" id="OCDSMsg"></span>
	</p>
	<p>
		<label for="OCDYTDLBinary"><?php print ($l->t ('YouTube DL Binary Path')); ?></label>
		<input type="text" class="OCDYTDLBinary ToUse" id="OCDYTDLBinary" value="<?php print (isset ($_['OCDS_YTDLBinary']) ? $_['OCDS_YTDLBinary'] : '/usr/local/bin/youtube-dl'); ?>" />
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p>
		<label for="OCDProxyAddress"><?php print ($l->t ('Proxy Address')); ?></label>
		<input type="text" class="OCDProxyAddress ToUse" id="OCDProxyAddress" value="<?php print(isset ($_['OCDS_ProxyAddress']) ? $_['OCDS_ProxyAddress'] : ''); ?>" />
		<label for="OCDProxyPort"><?php print ($l->t ('Proxy Port')); ?></label>
		<input type="text" class="OCDProxyPort ToUse" id="OCDProxyPort" value="<?php print(isset ($_['OCDS_ProxyPort']) ? $_['OCDS_ProxyPort'] : ''); ?>" />
	</p>
	<p>
		<span class="info"><?php print ($l->t ('If no authentication is required by your proxy, leave the following fields blank')); ?></span>
	</p>
	<p>
		<label for="OCDProxyUser"><?php print ($l->t ('Proxy User')); ?></label>
		<input type="text" class="OCDProxyUser ToUse" id="OCDProxyUser" value="<?php print(isset ($_['OCDS_ProxyUser']) ? $_['OCDS_ProxyUser'] : ''); ?>" placeholder="<?php print ($l->t ('Username')); ?>" />
		<input type="text" class="OCDProxyUserFake" id="OCDProxyUserFake" />
		<input type="password" class="OCDProxyPasswdFake" id="OCDProxyPasswdFake" />
		<label for="OCDProxyPasswd"><?php print ($l->t ('Proxy Password')); ?></label>
		<input type="password" class="OCDProxyPasswd ToUse" id="OCDProxyPasswd" placeholder="<?php print ($l->t ('Password')); ?>" />
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p>
		<label for="OCDCheckForUpdates"><?php print ($l->t ('Check for updates ?')); ?></label>
		<select id="OCDCheckForUpdates" class="ToUse">
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print((isset ($_['OCDS_CheckForUpdates']) && strcmp ($_['OCDS_CheckForUpdates'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
	</p>
	<p>
		<span class="info"><?php print ($l->t ('WARNING !! Switching from ARIA2 to another downloader engine will remove all current downloads from ARIA2')); ?></span>
	</p>
	<p>
		<label for="OCDWhichDownloader"><?php print ($l->t ('Which downloader do you want to use ?')); ?></label>
		<select id="OCDWhichDownloader" class="ToUse">
			<option value="ARIA2" data-protocols="HTTP(S) / FTP(S) / YouTube / BitTorrent">ARIA2</option>
			<option value="CURL" data-protocols="HTTP(S) / FTP(S) / YouTube"<?php print((isset ($_['OCDS_WhichDownloader']) && strcmp ($_['OCDS_WhichDownloader'], 'CURL') == 0) ? ' selected="selected"' : ''); ?>>cURL</option>
		</select>
		<span class="details"><?php print ($l->t ('Available protocols') . ': '); ?><strong></strong></span>
	</p>
</form>