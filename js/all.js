/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */
 
$(document).ready (function ()
{
	$('#ball').Badger ('...');
	$('#bcompletes').Badger ('...');
	$('#bactives').Badger ('...');
	$('#bwaitings').Badger ('...');
	$('#bstopped').Badger ('...');
	$('#bremoved').Badger ('...');
	
	// Get completes downloads every 5 seconds
    setInterval (function ()
	{
		if ($(OCDLR.Utils.QueueElt + '[data-rel="LOADER"]').length == 0)
		{
			OCDLR.Utils.UpdateQueue (true, 'all');
		}
	}, 5000);
	
	OCDLR.Utils.UpdateQueue (true, 'all');
	
	OCDLR.Utils.GetCounters ();
});