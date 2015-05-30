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
	<p>Leave fields blank to erase a setting value<span id="OCDSLoader" class="OCDSLoader icon-loading-small"></span><span class="msg" id="OCDSMsg"></span></p>
	<p id="YTBinary">
		<label for="OCDYTDLBinary">YouTube DL Binary Path</label>
		<input type="text" class="OCDYTDLBinary ToUse" id="OCDYTDLBinary" value="<?php print (isset ($_['OCDS_YTDLBinary']) ? $_['OCDS_YTDLBinary'] : '/usr/local/bin/youtube-dl'); ?>" />
	</p>
	<p id="OCDProxy">
		<label for="OCDProxyAddress">Proxy Address</label>
		<input type="text" class="OCDProxyAddress ToUse" id="OCDProxyAddress" value="<?php print(isset ($_['OCDS_ProxyAddress']) ? $_['OCDS_ProxyAddress'] : ''); ?>" />
		<label for="OCDProxyPort">Proxy Port</label>
		<input type="text" class="OCDProxyPort ToUse" id="OCDProxyPort" value="<?php print(isset ($_['OCDS_ProxyPort']) ? $_['OCDS_ProxyPort'] : ''); ?>" />
	</p>
	<p>
		<span class="info">If authentication is not required by your proxy, leave the following fields blank</span>
	</p>
	<p id="OCDProxyAuth">
		<label for="OCDProxyUser">Proxy User</label>
		<input type="text" class="OCDProxyUser ToUse" id="OCDProxyUser" value="<?php print(isset ($_['OCDS_ProxyUser']) ? $_['OCDS_ProxyUser'] : ''); ?>" placeholder="User" />
		<input type="text" class="OCDProxyUserFake" id="OCDProxyUserFake" />
		<input type="password" class="OCDProxyPasswdFake" id="OCDProxyPasswdFake" />
		<label for="OCDProxyPasswd">Proxy Password</label>
		<input type="password" class="OCDProxyPasswd ToUse" id="OCDProxyPasswd" placeholder="Password" />
	</p>
</form>