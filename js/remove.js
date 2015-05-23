/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

// Print Error message
function PrintError (Message)
{
	$('.ocd .content-page span.add-msg').attr ('class', 'muted add-msg alert');
	$('.ocd .content-page span.add-msg').text (Message);
}
// Print Info message
function PrintInfo (Message)
{
	$('.ocd .content-page span.add-msg').attr ('class', 'muted add-msg info');
	$('.ocd .content-page span.add-msg').text (Message);
}

$(document).ready (function()
{
	var GIDS = [];
	$('.ocd .content-queue > table > tbody > tr').each (function ()
	{
		GIDS.push($(this).attr ('data-rel'));
	});
	
	if (GIDS.length > 0)
	{
		$('div#loadtext').show();
	    $.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/downloadergetqueue'),
	        method: 'POST',
			dataType: 'json',
			data: {'GIDS' : GIDS},
	        async: true,
	        cache: false,
	        timeout: 30000,
	        success: function (Data)
			{
	            if (Data.ERROR)
				{
					PrintError (Data.MESSAGE);
				}
				else
				{
					$.each (Data.QUEUE, function (Index, Value)
					{
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value > div.pb-text').text ('Progress: ' + Value.PROGRESS);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="STATUS"]').text (Value.STATUS);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value').css ('width', Value.PROGRESSVAL);
					});
				}
				
				$('div#loadtext').hide ();
	        }
	    });
	}
	
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-delete').bind ('click', function ()
	{
		var TR = $(this).parent ().parent ();
		var GID = TR.attr ('data-rel');
		if (GID)
		{
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/downloadertotalremove'),
		        method: 'POST',
				dataType: 'json',
				data: {'GID' : GID},
		        async: true,
		        cache: false,
		        timeout: 30000,
		        success: function (Data)
				{
		            if (Data.ERROR)
					{
						PrintError (Data.MESSAGE);
					}
					else
					{
						PrintInfo (Data.MESSAGE + ' (' + GID + ')');
						TR.remove ();
					}
		        }
		    });
		}
		else
		{
			PrintError ('Unable to find the GID of this download ...')
		}
	});
});