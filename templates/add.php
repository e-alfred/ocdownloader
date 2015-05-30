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
script ('ocdownloader', 'script');
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
                    <div class="button" id="new">
        				<a><?php print ($l->t ('New Download')); ?><div class="icon-caret-dark svg"></div></a>
        				<ul>
        					<li><p data-rel="OCDHTTP">HTTP</p></li>
        					<li><p data-rel="OCDFTP">FTP</p></li>
                            <li><p data-rel="OCDYT">YOUTUBE</p></li>
                            <li><p data-rel="OCDBT">BITTORRENT</p></li>
        				</ul>
        			</div>
                    <div id="loadtext"<?php print ($_['NBELT'] > 0 ? '' : ' style="display: none;"'); ?>><?php print ($l->t ('Loading')); ?> ...</div>
                </div>
                <div class="righttitle"><?php print ($l->t ('Add Download')); ?></div>
            </div>
            <div class="content-page" rel="OCDHTTP">
                <h3>
                    <?php print ($l->t ('New HTTP download')); ?><span class="muted add-msg"></span>
                    <div class="button launch">
        				<a><?php print ($l->t ('Launch HTTP Download')); ?></a>
                    </div>
                </h3>
                <input type="text" placeholder="<?php print ($l->t ('HTTP URL to download')); ?>" class="form-control url" />
                <div class="jumbotron">
                    <h5><?php print ($l->t ('Options')); ?></h5>
                    <div class="group-option">
                        <label for="option-http-user"><?php print ($l->t ('Basic Auth User')); ?> :</label><input type="text" id="option-http-user" placeholder="<?php print ($l->t ('Username')); ?>" />
                        <label for="option-http-pwd"><?php print ($l->t ('Basic Auth Password')); ?> :</label><input type="password" id="option-http-pwd" placeholder="<?php print ($l->t ('Password')); ?>" /> 
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDFTP" style="display:none;">
                <h3>
                    <?php print ($l->t ('New FTP download')); ?><span class="muted add-msg"></span>
                    <div class="button launch">
        				<a><?php print ($l->t ('Launch FTP Download')); ?></a>
                    </div>
                </h3>
                <input type="text" placeholder="<?php print ($l->t ('FTP URL to download')); ?>" class="form-control url" />
                <div class="jumbotron">
                    <h5><?php print ($l->t ('Options')); ?></h5>
                    <div class="group-option">
                        <label for="option-ftp-user"><?php print ($l->t ('FTP User')); ?> :</label><input type="text" id="option-ftp-user" placeholder="<?php print ($l->t ('Username')); ?>" />
                        <label for="option-ftp-pwd"><?php print ($l->t ('FTP Password')); ?> :</label><input type="password" id="option-ftp-pwd" placeholder="<?php print ($l->t ('Password')); ?>" /> 
                    </div>
                    <div class="group-option">
                        <label for="option-ftp-pasv"><?php print ($l->t ('Passive Mode')); ?> :</label><input type="checkbox" id="option-ftp-pasv" checked />
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDYT" style="display:none;">
                <h3>
                    <?php print ($l->t ('New YouTube download')); ?><span class="muted add-msg"></span>
                    <div class="button launch">
        				<a><?php print ($l->t ('Launch YouTube Download')); ?></a>
                    </div>
                </h3>
                <input type="text" placeholder="<?php print ($l->t ('YouTube Video URL to download')); ?>" class="form-control url" />
                <div class="jumbotron">
                    <h5><?php print ($l->t ('Options')); ?></h5>
                    <div class="group-option">
                        <label for="option-yt-extractaudio"><?php print ($l->t ('Only Extract audio ?')); ?></label><input type="checkbox" id="option-yt-extractaudio" />&nbsp;<i><?php print ($l->t ('(No post-processing, just extract the best audio quality)')); ?></i>
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDBT" style="display:none;">
                <h3>
                    <?php print ($l->t ('New BitTorrent download')); ?><span class="muted add-msg"></span>
                    <div class="button launch">
        				<a><?php print ($l->t ('Launch BitTorrent Download')); ?></a>
                    </div>
                </h3>
                <div class="actions">
                    <div class="button" id="torrentlist">
        				<a><?php print ($l->t ('Select a file.torrent')); ?> <?php print (strlen (trim ($_['TTSFOLD'])) > 0 ? '' : '&nbsp;<i>' . $l->t ('(Default : List torrent files in the folder /Downloads/Files/Torrents, go to the Personnal Settings panel)') . '</i>'); ?><div class="icon-caret-dark svg"></div></a>
        				<ul>
                            <li><p class="loader"><span class="icon-loading-small"></span></p></li>
                        </ul>
        			</div>
                </div>
                <div class="jumbotron">
                    <h5><?php print ($l->t ('Options')); ?></h5>
                    <i>No options, for now ;-)</i>
                </div>
            </div>
            <div class="content-queue">
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th width="20%"><?php print ($l->t ('FILENAME')); ?></th>
                            <th width="10%" class="border"><?php print ($l->t ('PROTOCOL')); ?></th>
                            <th width="35%" class="border"><?php print ($l->t ('INFORMATION')); ?></th>
                            <th width="10%" class="border"><?php print ($l->t ('SPEED')); ?></th>
                            <th width="15%" class="border"><?php print ($l->t ('STATUS')); ?></th>
                            <th width="10%"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($Row = $_['QUEUE']->fetchRow()): ?>
                        <tr data-rel="<?php print($Row['GID']); ?>">
                            <td data-rel="NAME" class="padding"><?php print(strlen ($Row['FILENAME']) > 40 ? substr ($Row['FILENAME'], 0, 40) . '...' : $Row['FILENAME']); ?></td>
                            <td data-rel="PROTO" class="border padding"><?php print($Row['PROTOCOL']); ?></td>
                            <td data-rel="MESSAGE" class="border">
                                <div class="pb-wrap">
                                    <div class="pb-value" style="width: 0%;">
                                        <div class="pb-text"><?php print ($l->t ('N/A')); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-rel="SPEED" class="border padding"><?php print ($l->t ('N/A')); ?></td>
                            <td data-rel="STATUS" class="border padding"><?php print ($l->t ('N/A')); ?></td>
                            <td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>