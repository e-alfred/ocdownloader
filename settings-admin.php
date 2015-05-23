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

use \OCP\Config;

\OC_Util::checkAdminUser ();

$DbType = 0;
if (strcmp (Config::getSystemValue ('dbtype'), 'pgsql') == 0)
{
      $DbType = 1;
}

// Load settings
$SQL = 'SELECT * FROM `*PREFIX*ocdownloader_adminsettings`';
if ($DbType == 1)
{
      $SQL = 'SELECT * FROM *PREFIX*ocdownloader_adminsettings';
}
$Query = \OCP\DB::prepare ($SQL);
$Result = $Query->execute ();

// Display template
style ('ocdownloader', 'settings-admin');
script ('ocdownloader', 'settings-admin');

$Tmpl = new OCP\Template ('ocdownloader', 'settings-admin');
while ($Row = $Result->fetchRow())
{
      // Only one setting !! for now ;-)
      $Tmpl->assign('OCDS_' . $Row['KEY'], $Row['VAL']);
}

return $Tmpl->fetchPage ();