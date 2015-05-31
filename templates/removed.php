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

style ('ocdownloader', 'styles');
script ('ocdownloader', 'remove');
?>
<div id="app" class="ocd">
    <div id="app-navigation">
        <?php print_unescaped ($this->inc ('part.navigation')); ?>
    </div>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div class="jumbotron">
                <h1><?php print ($l->t ('Manage Your Downloads Anywhere!')); ?></h1>
                <p class="lead"><?php print ($l->t ('Enough dealing with tricky downloads syntax. Manage your downloads via the web easily with')); ?> <a href="http://aria2.sourceforge.net/manual/en/html/aria2c.html" target="_blank">ARIA2</a>.</p>
            </div>
            <div id="controls">
                <div class="actions">
                    <div id="loadtext" style="<?php print ($_['NBELT'] > 0 ? 'display: block;' : 'display: none;'); ?>"><?php print ($l->t ('Loading')); ?> ...</div>
                </div>
                <div class="righttitle"><?php print ($l->t ('Removed Downloads')); ?></div>
            </div>
            <div class="content-queue">
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th width="20%" data-rel="FILENAME"><?php print ($l->t ('FILENAME')); ?></th>
                            <th width="10%" data-rel="PROTO" class="border"><?php print ($l->t ('PROTOCOL')); ?></th>
                            <th width="60%" data-rel="MESSAGE" class="border"><?php print ($l->t ('INFORMATION')); ?></th>
                            <th width="10%" data-rel="ACTION"><?php print ($_['NBELT'] > 0 ? '<div class="icon-delete svg"></div>' : ''); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($Row = $_['QUEUE']->fetchRow()): ?>
                        <tr data-rel="<?php print($Row['GID']); ?>">
                            <td data-rel="FILENAME" class="padding"><?php print(strlen ($Row['FILENAME']) > 40 ? substr ($Row['FILENAME'], 0, 40) . '...' : $Row['FILENAME']); ?></td>
                            <td data-rel="PROTO" class="border padding"><?php print($Row['PROTOCOL']); ?></td>
                            <td data-rel="MESSAGE" class="border">
                                <div class="pb-wrap">
                                    <div class="pb-value" style="width: 0%;">
                                        <div class="pb-text"><?php print ($l->t ('N/A')); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>