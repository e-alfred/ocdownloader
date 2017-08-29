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
?>
<form id="ocdownloader" class="section">
	<h2>ocDownloader</h2>
	<p>
		<span class="info"><?php print ($l->t ('Leave fields blank to reset a setting value')); ?></span>
		<span id="OCDSLoaderYTDLBinary" class="OCDSLoader icon-loading-small"></span><span id="OCDSLoaderYTDLBinaryMsg" class="msg"></span>
	</p>
	<p>
		<label for="OCDYTDLBinary"><?php print ($l->t ('YouTube DL Binary Path')); ?></label>
		<input type="text" class="OCDYTDLBinary ToUse" id="OCDYTDLBinary" data-loader="OCDSLoaderYTDLBinary" value="<?php print (isset ($_['OCDS_YTDLBinary']) ? $_['OCDS_YTDLBinary'] : '/usr/local/bin/youtube-dl'); ?>" />
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p>
		<span class="info"><?php print ($l->t ('Proxy settings')); ?></span>
		<span id="OCDSLoaderProxySettings" class="OCDSLoader icon-loading-small"></span><span id="OCDSLoaderProxySettingsMsg" class="msg"></span>
	</p>
	<p>
		<label for="OCDProxyAddress"><?php print ($l->t ('Proxy Address')); ?></label>
		<input type="text" class="OCDProxyAddress ToUse" id="OCDProxyAddress" data-loader="OCDSLoaderProxySettings" value="<?php print (isset ($_['OCDS_ProxyAddress']) ? $_['OCDS_ProxyAddress'] : ''); ?>" placeholder="http://" />
		<label for="OCDProxyPort"><?php print ($l->t ('Proxy Port')); ?></label>
		<input type="text" class="OCDProxyPort ToUse" id="OCDProxyPort" data-loader="OCDSLoaderProxySettings" value="<?php print (isset ($_['OCDS_ProxyPort']) ? $_['OCDS_ProxyPort'] : ''); ?>" placeholder="8080" />
	</p>
	<p>
		<span class="info"><?php print ($l->t ('If no authentication is required by your proxy, leave the following fields blank')); ?></span>
	</p>
	<p>
		<label for="OCDProxyUser"><?php print ($l->t ('Proxy User')); ?></label>
		<input type="text" class="OCDProxyUser ToUse" id="OCDProxyUser" data-loader="OCDSLoaderProxySettings" value="<?php print (isset ($_['OCDS_ProxyUser']) ? $_['OCDS_ProxyUser'] : ''); ?>" placeholder="<?php print ($l->t ('Username')); ?>" />
		<input type="text" class="OCDProxyUserFake" id="OCDProxyUserFake" />
		<input type="password" class="OCDProxyPasswdFake" id="OCDProxyPasswdFake" />
		<label for="OCDProxyPasswd"><?php print ($l->t ('Proxy Password')); ?></label>
		<input type="password" class="OCDProxyPasswd ToUse" id="OCDProxyPasswd" data-loader="OCDSLoaderProxySettings" placeholder="<?php print ($l->t ('Password')); ?>" />
	</p>
	<p>
		<label for="OCDProxyOnlyWithYTDL"><?php print ($l->t ('Only use proxy settings with YouTube-DL ?')); ?></label>
		<select id="OCDProxyOnlyWithYTDL" class="ToUse" data-loader="OCDSLoaderProxySettings">
			<option value="N"><?php print ($l->t ('No')); ?></option>
			<option value="Y"<?php print ((isset ($_['OCDS_ProxyOnlyWithYTDL']) && strcmp ($_['OCDS_ProxyOnlyWithYTDL'], 'Y') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('Yes')); ?></option>
		</select>
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p>
		<span class="info"><?php print ($l->t ('General settings')); ?></span>
		<span id="OCDSLoaderGeneralSettings" class="OCDSLoader icon-loading-small"></span><span id="OCDSLoaderGeneralSettingsMsg" class="msg"></span>
	</p>
	<p>
		<label for="OCDCheckForUpdates"><?php print ($l->t ('Check for updates ?')); ?></label>
		<select id="OCDCheckForUpdates" class="ToUse" data-loader="OCDSLoaderGeneralSettings">
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print ((isset ($_['OCDS_CheckForUpdates']) && strcmp ($_['OCDS_CheckForUpdates'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
	</p>
	<p>
		<span class="info"><?php print ($l->t ('WARNING !! Switching from ARIA2 to another downloader engine will remove all current downloads from ARIA2')); ?></span>
	</p>
	<p>
		<label for="OCDWhichDownloader"><?php print ($l->t ('Which downloader do you want to use ?')); ?></label>
		<select id="OCDWhichDownloader" class="ToUse" data-loader="OCDSLoaderGeneralSettings">
			<option value="ARIA2" data-protocols="HTTP(S) / FTP(S) / YouTube / BitTorrent">ARIA2</option>
			<option value="CURL" data-protocols="HTTP(S) / FTP(S) / YouTube"<?php print ((isset ($_['OCDS_WhichDownloader']) && strcmp ($_['OCDS_WhichDownloader'], 'CURL') == 0) ? ' selected="selected"' : ''); ?>>cURL</option>
		</select>
		<span id="OCDWhichDownloaderDetails" class="details"><?php print ($l->t ('Available protocols') . ': '); ?><strong></strong></span>
	</p>
	<p>
		<label for="OCDMaxDownloadSpeed"><?php print ($l->t ('Max download speed ?')); ?></label>
		<input type="text" id="OCDMaxDownloadSpeed" data-loader="OCDSLoaderGeneralSettings" class="ToUse" value="<?php print (isset ($_['OCDS_MaxDownloadSpeed']) ? $_['OCDS_MaxDownloadSpeed'] : ''); ?>" placeholder="10000" />
		<span class="details"><?php print ($l->t ('KB/s (empty or 0 : unlimited, default : unlimited)')); ?></span>
	</p>
	<div id="OCDBTSettings"<?php print ((isset ($_['OCDS_AllowProtocolBT']) && isset ($_['OCDS_WhichDownloader']) && strcmp ($_['OCDS_AllowProtocolBT'], 'Y') == 0 && strcmp ($_['OCDS_WhichDownloader'], 'ARIA2') == 0) ? '' : ' style="display:none"'); ?>>
		<hr />
		<div style="clear:both;"></div>
		<p>
			<span class="info"><?php print ($l->t ('BitTorrent protocol settings - Max upload speed')); ?></span>
			<span id="OCDSLoaderBTGeneralSettings" class="OCDSLoader icon-loading-small"></span><span id="OCDSLoaderBTGeneralSettingsMsg" class="msg"></span>
		</p>
		<p>
			<label for="OCDBTMaxUploadSpeed"><?php print ($l->t ('BitTorrent protocol max upload speed ?')); ?></label>
			<input type="text" id="OCDBTMaxUploadSpeed" data-loader="OCDSLoaderBTGeneralSettings" class="ToUse" value="<?php print (isset ($_['OCDS_BTMaxUploadSpeed']) ? $_['OCDS_BTMaxUploadSpeed'] : ''); ?>" placeholder="5000" />
			<span class="details"><?php print ($l->t ('KB/s (empty or 0 : unlimited, default : unlimited)')); ?></span>
		</p>
	</div>
	<hr />
	<div style="clear:both;"></div>
	<p>
		<span class="info"><?php print ($l->t ('Allow protocols for users (except for members of the admin group)')); ?></span>
		<span id="OCDSLoaderPermissionsSettings" class="OCDSLoader icon-loading-small"></span><span id="OCDSLoaderPermissionsSettingsMsg" class="msg"></span>
	</p>
	<p>
		<label for="OCDAllowProtocolHTTP"><?php print ($l->t ('Allow HTTP ?')); ?></label>
		<select id="OCDAllowProtocolHTTP" class="ToUse" data-loader="OCDSLoaderPermissionsSettings">
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print ((isset ($_['OCDS_AllowProtocolHTTP']) && strcmp ($_['OCDS_AllowProtocolHTTP'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
	</p>
	<p>
		<label for="OCDAllowProtocolFTP"><?php print ($l->t ('Allow FTP ?')); ?></label>
		<select id="OCDAllowProtocolFTP" class="ToUse" data-loader="OCDSLoaderPermissionsSettings">
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print ((isset ($_['OCDS_AllowProtocolFTP']) && strcmp ($_['OCDS_AllowProtocolFTP'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
	</p>
	<p>
		<label for="OCDAllowProtocolYT"><?php print ($l->t ('Allow YouTube ?')); ?></label>
		<select id="OCDAllowProtocolYT" class="ToUse" data-loader="OCDSLoaderPermissionsSettings">
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print ((isset ($_['OCDS_AllowProtocolYT']) && strcmp ($_['OCDS_AllowProtocolYT'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
	</p>
	<p>
		<label for="OCDAllowProtocolBT"><?php print ($l->t ('Allow BitTorrent ?')); ?></label>
		<select id="OCDAllowProtocolBT" class="ToUse" data-loader="OCDSLoaderPermissionsSettings"<?php print (isset ($_['OCDS_WhichDownloader']) && strcmp ($_['OCDS_WhichDownloader'], 'CURL') == 0 ? ' style="display:none"' : ''); ?>>
			<option value="Y"><?php print ($l->t ('Yes')); ?></option>
			<option value="N"<?php print ((isset ($_['OCDS_AllowProtocolBT']) && strcmp ($_['OCDS_AllowProtocolBT'], 'N') == 0) ? ' selected="selected"' : ''); ?>><?php print ($l->t ('No')); ?></option>
		</select>
		<span id="OCDAllowProtocolBTDetails" class="details"<?php print (isset ($_['OCDS_WhichDownloader']) && strcmp ($_['OCDS_WhichDownloader'], 'ARIA2') == 0 ? ' style="display:none"' : ''); ?>><strong><?php print ($l->t ('No')); ?></strong></span>
	</p>
</form>