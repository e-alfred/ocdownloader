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
style ('ocdownloader', 'styles.min');
script ('ocdownloader', 'badger.min');
script ('ocdownloader', 'ocdownloader.min');
script ('ocdownloader', 'add.min');

if ($_['CANCHECKFORUPDATE']) script ('ocdownloader', 'updater');
?>
<div id="app">
    <div id="app-navigation">
        <?php print_unescaped ($this->inc ('part.navigation')); ?>
    </div>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div class="jumbotron">
                <h1><?php print ($l->t ('Manage Your Downloads Anywhere!')); ?></h1>
                <p class="lead"><?php print ($l->t ('Enough dealing with tricky downloads syntax. Manage your downloads via the web easily with <strong>%s</strong>', $_['WD'])); ?></p>
            </div>
            <div id="controls">
                <div class="actions">
                    <div class="button" id="NewDL">
        				<a><?php print ($l->t ('New Download')); ?><div class="icon-caret-dark svg"></div></a>
        				<ul>
        					<li><p data-rel="OCDHTTP">HTTP</p></li>
        					<li><p data-rel="OCDFTP">FTP</p></li>
                            <li><p data-rel="OCDYT">YOUTUBE</p></li>
                            <?php if (strcmp ($_['WD'], 'ARIA2') == 0): ?><li><p data-rel="OCDBT">BITTORRENT</p></li><?php endif; ?>
        				</ul>
        			</div>
                    <div class="loadingtext loadinginline" style="display:none;"><?php print ($l->t ('Loading')); ?> ...</div>
                </div>
                <div class="righttitle"><?php print ($l->t ('Add Download')); ?></div>
            </div>
            <div class="content-page" rel="OCDHTTP">
                <h3>
                    <?php print ($l->t ('New HTTP download')); ?><span class="muted OCDLRMsg"></span>
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
                    <?php print ($l->t ('New FTP download')); ?><span class="muted OCDLRMsg"></span>
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
                    <?php print ($l->t ('New YouTube download')); ?><span class="muted OCDLRMsg"></span>
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
                    <div class="group-option">
                        <label for="option-yt-forceipv4"><?php print ($l->t ('Force IPv4 ?')); ?></label><input type="checkbox" id="option-yt-forceipv4" />
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDBT" style="display:none;">
                <h3>
                    <?php print ($l->t ('New BitTorrent download')); ?><span class="muted OCDLRMsg"></span>
                    <div class="button launch">
        				<a><?php print ($l->t ('Launch BitTorrent Download')); ?></a>
                    </div>
                </h3>
                <div class="actions">
                    <div class="button" id="TorrentsList">
        				<a><?php print ($l->t ('Select a file.torrent')); ?> <?php print (strlen (trim ($_['TTSFOLD'])) > 0 ? '' : '&nbsp;<i>' . $l->t ('(Default : List torrent files in the folder /Downloads/Files/Torrents, go to the Personal Settings panel)') . '</i>'); ?><div class="icon-caret-dark svg"></div></a>
        				<ul>
                            <li><p class="loader"><span class="icon-loading-small"></span></p></li>
                        </ul>
        			</div>
                </div>
                <div class="jumbotron">
                    <h5><?php print ($l->t ('Options')); ?></h5>
                    <div class="group-option">
                        <label for="option-bt-rmtorrent"><?php print ($l->t ('Remove torrent file ?')); ?></label><input type="checkbox" id="option-bt-rmtorrent" />
                    </div>
                </div>
            </div>
            <div class="content-queue">
                <table id="Queue" border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th width="20%" data-rel="FILENAME"><?php print ($l->t ('FILENAME')); ?></th>
                            <th width="10%" data-rel="PROTO" class="border"><?php print ($l->t ('PROTOCOL')); ?></th>
                            <th width="35%" data-rel="MESSAGE" class="border"><?php print ($l->t ('INFORMATION')); ?></th>
                            <th width="10%" data-rel="SPEED" class="border"><?php print ($l->t ('SPEED')); ?></th>
                            <th width="15%" data-rel="STATUS" class="border"><?php print ($l->t ('STATUS')); ?></th>
                            <th width="10%" data-rel="ACTION"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-rel="LOADER">
                            <td colspan="6"><div class="icon-loading-small"></div><?php print ($l->t ('Loading')); ?> ...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>