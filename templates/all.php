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
style('ocdownloader', 'styles.min');
#FIXME: depends from files app
style('files', 'merged');
script('ocdownloader', 'badger.min');
script('ocdownloader', 'ocdownloader.min');
script('ocdownloader', 'all');
  
if ($_['CANCHECKFORUPDATE']) {
    script('ocdownloader', 'updater');
}
?>
<div id="app">
    <div id="app-navigation">
        <?php print_unescaped($this->inc('part.navigation')); ?>
    </div>
    <div id="app-content">
        <div id="app-content-wrapper">
            <div id="controls">
              <div class="content-page" rel="OCDURI">
                  <div class="handler info"></div>
                  <input type="text" placeholder="<?php print($l->t('URI to download')); ?>" class="form-control url" />
                  <div class="button launch">
                      <a><?php print($l->t('Launch Download')); ?></a>
                  </div>
              </div>
            </div>
            <table id="Queue" class="list-container">
              <thead>
                <tr>
                  <th id="headerSelection" class=" column-selection">
                    <input type="checkbox" id="select_all_files" class="select-all checkbox"/>
                    <label for="select_all_files">
                            <span class="hidden-visually"><?php p($l->t('Select all'))?></span>
                    </label>
                  </th>
                  <th id='headerName' class=" column-name">
                    <div id="headerName-container">
                      <a class="name sort columntitle" data-sort="name">
                        <span><?php p($l->t('Name')); ?></span>
                        <span class="sort-indicator"></span>
                      </a>
                      <span id="selectedActionsList" class="hidden selectedActions">
                          <a href="" class="actions-selected">
                              <span class="icon icon-more"></span>
                              <span><?php p($l->t('Actions'))?></span>
                          </a>
                      </span>
                    </div>
                  </th>
                  <th id="headerSize" class=" column-size">
                          <a class="size sort columntitle" data-sort="size"><span><?php p($l->t('Size')); ?></span><span class="sort-indicator"></span></a>
                  </th>
                  <th id="headerDate" class=" column-mtime">
                          <a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t('Status')); ?></span><span class="sort-indicator"></span></a>
                  </th>
            </tr>
          </thead>
          <tbody id="list-downloads">
              <tr data-rel="LOADER">
                  <td colspan="6"><div class="icon-loading-small"></div><?php print($l->t('Loading')); ?> ...</td>
              </tr>
          </tbody>
            </table>
        <div class="loadingtext loadingblock" style="display:none;"><?php print($l->t('Loading')); ?> ...</div>
        </div>
    </div>
</div>