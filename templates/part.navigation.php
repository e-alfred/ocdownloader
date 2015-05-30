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
<ul>
    <li data-id="add"<?php print ($_['PAGE'] === 0 ? 'class="active"' : ''); ?>><a href="add"><?php print ($l->t ('Add Download')); ?></a></li>
    <li data-id="actives"<?php print ($_['PAGE'] === 1 ? 'class="active"' : ''); ?>><a href="actives"><?php print ($l->t ('Active Downloads')); ?></a></li>
    <li data-id="waiting"<?php print ($_['PAGE'] === 2 ? 'class="active"' : ''); ?>><a href="waitings"><?php print ($l->t ('Waiting Downloads')); ?></a></li>
    <li data-id="stopped"<?php print ($_['PAGE'] === 3 ? 'class="active"' : ''); ?>><a href="stopped"><?php print ($l->t ('Stopped Downloads')); ?></a></li>
    <li data-id="removed"<?php print ($_['PAGE'] === 4 ? 'class="active"' : ''); ?>><a href="removed"><?php print ($l->t ('Removed Downloads')); ?></a></li>
</ul>