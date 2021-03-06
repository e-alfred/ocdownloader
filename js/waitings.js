/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

window.addEventListener('DOMContentLoaded', function () {
	$('#ball').Badger ('...');
	$('#bcompletes').Badger ('...');
	$('#bactives').Badger ('...');
	$('#bwaitings').Badger ('...');
	$('#bstopped').Badger ('...');
	$('#bremoved').Badger ('...');

	OCDLR.Utils.UpdateQueue (true, 'waitings');

	OCDLR.Utils.GetCounters ();
});
