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

$BTSeedTimeToReach = 1;
$BTSeedTimeToReachUnit = 'w';
if (isset ($_['OCDS_BTSeedTimeToReach_BTSeedTimeToReachUnit']))
{
	$SeedTime = explode ('_', $_['OCDS_BTSeedTimeToReach_BTSeedTimeToReachUnit']); 
	if (count ($SeedTime) == 2)
	{
		$BTSeedTimeToReach = $SeedTime[0];
		$BTSeedTimeToReachUnit = $SeedTime[1];
	}
}
 
?>
<form id="ocdownloader" class="section">
	<h2>ocDownloader</h2>
	<p><span class="info"><?php print ($l->t ('Leave fields blank to reset a setting value')); ?></span><span id="OCDSLoader" class="OCDSLoader icon-loading-small"></span><span class="msg" id="OCDSMsg"></span></p>
	<p id="DownloadsFolder">
		<label for="OCDDownloadsFolder"><?php print ($l->t ('Default Downloads Folder')); ?></label>
		<input type="text" class="OCDDownloadsFolder" id="OCDDownloadsFolder" value="<?php print(isset ($_['OCDS_DownloadsFolder']) ? '/' . $_['OCDS_DownloadsFolder'] : '/Downloads'); ?>" placeholder="/Downloads" />
		<input type="button" value="<?php print ($l->t ('Save')); ?>" data-rel="OCDDownloadsFolder" />
	</p>
	<?php if ($_['AllowProtocolBT']): ?>
	<hr />
	<div style="clear:both;"></div>
	<p id="TorrentsFolder">
		<label for="OCDTorrentsFolder"><?php print ($l->t ('Default Torrents Folder')); ?></label>
		<input type="text" class="OCDTorrentsFolder" id="OCDTorrentsFolder" value="<?php print(isset ($_['OCDS_TorrentsFolder']) ? '/' . $_['OCDS_TorrentsFolder'] : '/Downloads/Files/Torrents'); ?>" placeholder="/Downloads/Files/Torrents" />
		<input type="button" value="<?php print ($l->t ('Save')); ?>" data-rel="OCDTorrentsFolder" />
	</p>
	<hr />
	<div style="clear:both;"></div>
	<p><span class="info"><?php print ($l->t ('BitTorrent protocol settings - Ratio')); ?></span></p>
	<p id="RatioValue">
		<label for="OCDBTRatioToReach"><?php print ($l->t ('Default ratio to reach ?')); ?></label>
		<select id="OCDBTRatioToReach">
			<option value="0.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '0.0' ? ' selected="selected"' : ''); ?>>0 (<?php print($l->t ('unlimited')); ?>)</option>
			<option value="1.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '1.0' ? ' selected="selected"' : ''); ?>>1.0</option>
			<option value="2.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '2.0' ? ' selected="selected"' : ''); ?>>2.0</option>
			<option value="3.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '3.0' ? ' selected="selected"' : ''); ?>>3.0</option>
			<option value="4.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '4.0' ? ' selected="selected"' : ''); ?>>4.0</option>
			<option value="5.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '5.0' ? ' selected="selected"' : ''); ?>>5.0</option>
			<option value="6.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '6.0' ? ' selected="selected"' : ''); ?>>6.0</option>
			<option value="7.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '7.0' ? ' selected="selected"' : ''); ?>>7.0</option>
			<option value="8.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '8.0' ? ' selected="selected"' : ''); ?>>8.0</option>
			<option value="9.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '9.0' ? ' selected="selected"' : ''); ?>>9.0</option>
			<option value="10.0"<?php print (isset ($_['OCDS_BTRatioToReach']) && $_['OCDS_BTRatioToReach'] == '10.0' ? ' selected="selected"' : ''); ?>>10.0</option>
		</select>
		<input type="button" value="<?php print ($l->t ('Save')); ?>" data-rel="OCDBTRatioToReach" />
	</p>
	<p><span class="info"><?php print ($l->t ('BitTorrent protocol settings - Seed time')); ?></span></p>
	<p id="SeedTimeValue">
		<label for="OCDBTSeedTimeToReach"><?php print ($l->t ('Seed time to reach ?')); ?></label>
		<select id="OCDBTSeedTimeToReach">
			<?php for ($N = 1; $N < 60; $N++): ?>
			<option value="<?php print ($N); ?>"<?php print ($BTSeedTimeToReach == $N ? ' selected="selected"' : ''); ?>><?php print ($N); ?></option>
			<?php endfor; ?>
		</select>
		<select id="OCDBTSeedTimeToReachUnit">
			<option value="i"<?php print ($BTSeedTimeToReachUnit == 'i' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('minute(s)')); ?></option>
			<option value="h"<?php print ($BTSeedTimeToReachUnit == 'h' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('hour(s)')); ?></option>
			<option value="d"<?php print ($BTSeedTimeToReachUnit == 'd' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('day(s)')); ?></option>
			<option value="w"<?php print ($BTSeedTimeToReachUnit == 'w' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('week(s)')); ?></option>
			<option value="m"<?php print ($BTSeedTimeToReachUnit == 'm' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('month(s)')); ?></option>
			<option value="y"<?php print ($BTSeedTimeToReachUnit == 'y' ? ' selected="selected"' : ''); ?>><?php print ($l->t ('year(s)')); ?></option>
		</select>
		<input type="button" value="<?php print ($l->t ('Save')); ?>" data-rel="OCDBTSeedTimeToReach_OCDBTSeedTimeToReachUnit" />
	</p>
	<?php endif; ?>
</form>