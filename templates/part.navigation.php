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
<ul>
    <li data-id="add"<?php print ($_['PAGE'] === 0 ? ' class="active"' : ''); ?>>
        <a href="add"><?php print ($l->t ('Add Download')); ?></a>
    </li>
    <li data-id="all"<?php print ($_['PAGE'] === 1 ? ' class="active"' : ''); ?>>
        <div class="badge" id="ball">
            <a href="all"><?php print ($l->t ('All Downloads')); ?></a>
        </div>
    </li>
    <li data-id="completes"<?php print ($_['PAGE'] === 2 ? ' class="active"' : ''); ?>>
        <div class="badge" id="bcompletes">
            <a href="completes"><?php print ($l->t ('Complete Downloads')); ?></a>
        </div>
    </li>
    <li data-id="actives"<?php print ($_['PAGE'] === 3 ? ' class="active"' : ''); ?>>
        <div class="badge" id="bactives">
            <a href="actives"><?php print ($l->t ('Active Downloads')); ?></a>
        </div>
    </li>
    <li data-id="waiting"<?php print ($_['PAGE'] === 4 ? ' class="active"' : ''); ?>>
        <div class="badge" id="bwaitings">
            <a href="waitings"><?php print ($l->t ('Waiting Downloads')); ?></a>
        </div>
    </li>
    <li data-id="stopped"<?php print ($_['PAGE'] === 5 ? ' class="active"' : ''); ?>>
        <div class="badge" id="bstopped">
            <a href="stopped"><?php print ($l->t ('Stopped Downloads')); ?></a>
        </div>
    </li>
    <li data-id="removed"<?php print ($_['PAGE'] === 6 ? ' class="active"' : ''); ?>>
        <div class="badge" id="bremoved">
            <a href="removed"><?php print ($l->t ('Removed Downloads')); ?></a>
        </div>
    </li>
    <li class="nav-updater">
        <h3>
            <?php print ($l->t ('Update available !')); ?>
            <div class="button">
                <a><?php print ($l->t ('Download')); ?></a>
            </div>
        </h3>
    </li>
</ul>