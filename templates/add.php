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
                <h1>Manage Your Downloads Anywhere!</h1>
                <p class="lead">Enough dealing with tricky downloads syntax. Manage your downloads via the web easily with <a href="http://aria2.sourceforge.net/manual/en/html/aria2c.html" target="_blank">ARIA2</a>.</p>
            </div>
            <div id="controls">
                <div class="actions">
                    <div class="button" id="new">
        				<a>New Download<div class="icon-caret-dark svg"></div></a>
        				<ul>
        					<li><p data-rel="OCDHTTP">HTTP</p></li>
        					<li><p data-rel="OCDFTP">FTP</p></li>
                            <li><p data-rel="OCDYT">YOUTUBE</p></li>
        				</ul>
        			</div>
                    <div id="loadtext"<?php print ($_['NBELT'] > 0 ? '' : ' style="display: none;"'); ?>>Loading ...</div>
                </div>
                <div class="righttitle">Add Download</div>
            </div>
            <div class="content-page" rel="OCDHTTP">
                <h3>
                    New HTTP download<span class="muted add-msg"></span>
                    <div class="button launch">
        				<a>Launch HTTP Download</a>
                    </div>
                </h3>
                <input type="text" placeholder="HTTP URL to download" class="form-control url" />
                <div class="jumbotron">
                    <h5>Options</h5>
                    <div class="group-option">
                        <label for="option-http-user">Basic Auth User :</label><input type="text" id="option-http-user" placeholder="Username" />
                        <label for="option-http-pwd">Basic Auth Password :</label><input type="password" id="option-http-pwd" placeholder="Password" /> 
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDFTP" style="display:none;">
                <h3>
                    New FTP download<span class="muted add-msg"></span>
                    <div class="button launch">
        				<a>Launch FTP Download</a>
                    </div>
                </h3>
                <input type="text" placeholder="FTP URL to download" class="form-control url" />
                <div class="jumbotron">
                    <h5>Options</h5>
                    <div class="group-option">
                        <label for="option-ftp-user">FTP User :</label><input type="text" id="option-ftp-user" placeholder="Username" />
                        <label for="option-ftp-pwd">FTP Password :</label><input type="password" id="option-ftp-pwd" placeholder="Password" /> 
                    </div>
                    <div class="group-option">
                        <label for="option-ftp-pasv">Passive Mode :</label><input type="checkbox" id="option-ftp-pasv" checked />
                    </div>
                </div>
            </div>
            <div class="content-page" rel="OCDYT" style="display:none;">
                <h3>
                    New YouTube download<span class="muted add-msg"></span>
                    <?php if ($_['YTDL']): ?>
                    <div class="button launch">
        				<a>Launch YouTube Download</a>
                    </div>
                    <?php else: ?>
                    <span class="muted pull-right highalert">Unable to find YouTube-DL !</span>
                    <?php endif; ?>
                </h3>
                <input type="text" placeholder="YouTube Video URL to download" class="form-control url" />
                <div class="jumbotron">
                    <h5>Options</h5>
                    <div class="group-option">
                        <label for="option-yt-extractaudio">Only Extract audio ?</label><input type="checkbox" id="option-yt-extractaudio" /><?php print (!$_['FFMPEG'] ? '&nbsp;<i>(No post-processing because FFMpeg was not found)</i>' : ''); ?>
                        <?php if ($_['FFMPEG']): ?>
                        <br />
                        <label for="option-yt-ea-format">Format :</label>
                        <select id="option-yt-ea-format">
                            <option value="best">Best Format</option>
                            <option value="aac">AAC</option>
                            <option value="vorbis">Vorbis</option>
                            <option value="mp3">MP3</option>
                            <option value="m4a">M4A</option>
                            <option value="opus">Opus</option>
                            <option value="wav">WAV</option>
                        </select>
                        <label for="option-yt-ea-qual">Quality :</label>
                        <select id="option-yt-ea-qual">
                            <option value="0">Better Quality</option>
                            <option value="5">Default Quality</option>
                            <option value="9">Worse Quality</option>
                        </select>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="content-queue">
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th width="20%">ID</th>
                            <th width="10%" class="border">PROTOCOL</th>
                            <th width="35%" class="border">INFO</th>
                            <th width="10%" class="border">SPEED</th>
                            <th width="15%" class="border">STATUS</th>
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
                                        <div class="pb-text">N/A</div>
                                    </div>
                                </div>
                            </td>
                            <td data-rel="SPEED" class="border padding">N/A</td>
                            <td data-rel="STATUS" class="border padding">N/A</td>
                            <td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>