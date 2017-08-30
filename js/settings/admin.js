/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

$(document).ready (function ()
{
	$('#OCDWhichDownloader').bind ('change', function ()
	{
		if ($('#OCDWhichDownloader').val () == 'ARIA2')
		{
			$('#OCDBTSettings').show ();
			$('#OCDAllowProtocolBT').show ();
			$('#OCDAllowProtocolBTDetails').hide ();
		}
		else
		{
			$('#OCDBTSettings').hide ();
			if ($('#OCDAllowProtocolBT').val () != 'N')
			{
				$('#OCDAllowProtocolBT').val ('N');
				$('#OCDAllowProtocolBT').change ();
			}
			$('#OCDAllowProtocolBT').hide ();
			$('#OCDAllowProtocolBTDetails').show ();
		}
	});
	$('#OCDAllowProtocolBT').bind ('change', function ()
	{
		if ($('#OCDAllowProtocolBT').val () == 'Y')
		{
			$('#OCDBTSettings').show ();
		}
		else
		{
			$('#OCDBTSettings').hide ();
		}
	});
	
	$('form#ocdownloader input.ToUse, form#ocdownloader select.ToUse').bind ('change', function ()
	{
		var Field = $(this);
		
		$('#' + Field.attr ('data-loader')).show ();
		$('#' + Field.attr ('data-loader') + 'Msg').hide ();
		$('#' + Field.attr ('data-loader') + 'Msg').removeClass ('error');
		$('#' + Field.attr ('data-loader') + 'Msg').removeClass ('success');
		
		$.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/adminsettings/save'),
	        method: 'POST',
			dataType: 'json',
			data: {
				'KEY': Field.attr ('id').replace ('OCD', ''),
				'VAL': Field.val ()
			},
	        async: true,
	        cache: false,
	        timeout: 30000,
	        success: function (Data)
			{
				$('#' + Field.attr ('data-loader')).hide ();
				
				$('#' + Field.attr ('data-loader') + 'Msg').text (Data.MESSAGE);
				if (Data.ERROR)
				{
					$('#' + Field.attr ('data-loader') + 'Msg').addClass ('error');
				}
				else
				{
					$('#' + Field.attr ('data-loader') + 'Msg').addClass ('success');
				}
			
				$('#' + Field.attr ('data-loader') + 'Msg').show ();
				
				$('#OCDWhichDownloaderDetails > strong').text ($('#OCDWhichDownloader > option:selected').attr ('data-protocols'));
			}
	    });
	});
	
	$('#OCDWhichDownloaderDetails > strong').text ($('#OCDWhichDownloader > option:selected').attr ('data-protocols'));
});