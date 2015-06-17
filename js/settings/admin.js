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
	$('form#ocdownloader > p > input.ToUse, form#ocdownloader > p > select.ToUse').bind ('change', function ()
	{
		$('#OCDSLoader').show ();
		$('#OCDSMsg').hide ();
		$('#OCDSMsg').removeClass ('error');
		$('#OCDSMsg').removeClass ('success');
		
		var Field = $(this);
		
		$.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/adminsettings/save'),
	        method: 'POST',
			dataType: 'json',
			data: {
				[Field.attr ('id').replace ('OCD', '')]: Field.val ()
			},
	        async: true,
	        cache: false,
	        timeout: 30000,
	        success: function (Data)
			{
				$('#OCDSLoader').hide ();
				
				$('#OCDSMsg').text (Data.MESSAGE);
				if (Data.ERROR)
				{
					$('#OCDSMsg').addClass ('error');
				}
				else
				{
					$('#OCDSMsg').addClass ('success');
				}
			
				$('#OCDSMsg').show ();
				
				$('form#ocdownloader > p > span.details > strong').text ($('#OCDWhichDownloader > option:selected').attr ('data-protocols'));
			}
	    });
	});
	
	$('form#ocdownloader > p > span.details > strong').text ($('#OCDWhichDownloader > option:selected').attr ('data-protocols'));
});