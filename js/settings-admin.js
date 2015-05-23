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
	$('#YTBinaryInput').bind ('change', function ()
	{
		$('#YTBinaryLoader').show ();
		
		$.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/adminsettings'),
	        method: 'POST',
			dataType: 'json',
			data: {'YTBinary' : $('#YTBinaryInput').val()},
	        async: true,
	        cache: false,
	        timeout: 30000,
	        success: function (Data)
			{
				$('#YTBinaryLoader').hide ();
			}
	    });
	});
});