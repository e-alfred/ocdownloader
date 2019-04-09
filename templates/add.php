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
style ('ocdownloader', 'styles.min');
script ('ocdownloader', 'badger.min');
script ('ocdownloader', 'ocdownloader.min');
//script ('files', 'jquery.iframe-transport');
script ('files', 'jquery.fileupload');
script ('ocdownloader', 'add.min');

$g = \OC::$server->getURLGenerator ();

if ($_['CANCHECKFORUPDATE']) script ('ocdownloader', 'updater');
?>
<div id="app">
    <div id="app-navigation">
        <?php print_unescaped ($this->inc ('part.navigation')); ?>
    </div>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div id="controls">
                <div class="righttitle"><?php print ($l->t ('Add Download')); ?></div>
                <div style="float: right;"><p class="lead"><?php print ($l->t (' using <strong>%s</strong>', $_['WD'])); ?></p></div>
            </div>
            <div class="content-page" rel="OCDURI">
                <h3>
                    <?php print ($l->t ('New URI download')); ?><span class="muted OCDLRMsg"></span>
                </h3>
                <div class="handler info"></div>
                <input type="text" placeholder="<?php print ($l->t ('URI to download')); ?>" class="form-control url" />
                <div class="button launch">
                    <a><?php print ($l->t ('Launch Download')); ?></a>
                </div>
            </div>
          <!--
            <?php if (false and $_['AllowProtocolHTTP']): ?>
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
                        <label for="option-http-referer"><?php print ($l->t ('HTTP Referer')); ?> :</label><input type="text" id="option-http-referer" placeholder="<?php print ($l->t ('Referer')); ?>" />
                        <label for="option-http-useragent"><?php print ($l->t ('HTTP User Agent')); ?> :</label><input type="text" id="option-http-useragent" placeholder="<?php print ($l->t ('Useragent')); ?>" />
                        <label for="option-http-outfilename"><?php print ($l->t ('HTTP Output Filename')); ?> :</label><input type="text" id="option-http-outfilename" placeholder="<?php print ($l->t ('Filename')); ?>" />
                    </div>
                </div>
            </div>
            <?php endif; if ($_['AllowProtocolFTP']): ?>
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
                        <label for="option-ftp-referer"><?php print ($l->t ('FTP Referer')); ?> :</label><input type="text" id="option-ftp-referer" placeholder="<?php print ($l->t ('Referer')); ?>" />
                        <label for="option-ftp-useragent"><?php print ($l->t ('FTP User Agent')); ?> :</label><input type="text" id="option-ftp-useragent" placeholder="<?php print ($l->t ('Useragent')); ?>" />
                        <label for="option-ftp-outfilename"><?php print ($l->t ('FTP Output Filename')); ?> :</label><input type="text" id="option-ftp-outfilename" placeholder="<?php print ($l->t ('Filename')); ?>" />
                    </div>
                    <div class="group-option">
                        <label for="option-ftp-pasv"><?php print ($l->t ('Passive Mode')); ?> :</label><input type="checkbox" id="option-ftp-pasv" checked />
                    </div>
                </div>
            </div>
            <?php endif; if ($_['AllowProtocolYT']): ?>
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
            <?php endif; if ($_['AllowProtocolBT']): ?>
            <div class="content-page" rel="OCDBT" style="display:none;">
                <h3>
                    <?php print ($l->t ('New BitTorrent download')); ?><span class="muted OCDLRMsg"></span>
                    <div class="button launch">
                        <a><?php print ($l->t ('Launch BitTorrent Download')); ?></a>
                    </div>
                    <div class="button uploadfile">
                        <input type="file" name="files[]" id="uploadfile" accept=".torrent" multiple="multiple" data-url="<?php print_unescaped($g->linkToRoute ('ocdownloader.BTDownloader.UploadFiles')); ?>">
                        <label class="svg icon-upload" for="uploadfile">
                            <span class="hidden-visually">Upload</span>
                        </label>
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
          <?php endif; ?> -->
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
            <div class="loadingtext loadinginline" style="display:none;"><?php print ($l->t ('Loading')); ?> ...</div>
        </div>
    </div>
</div>
