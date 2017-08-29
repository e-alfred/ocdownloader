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
	$('form#ocdownloader > p > input[type="button"]').bind ('click', function ()
	{
		$('#OCDSLoader').show ();
		$('#OCDSMsg').hide ();
		$('#OCDSMsg').removeClass ('error');
		$('#OCDSMsg').removeClass ('success');
		
		var KEY = $(this).attr ('data-rel');
		var VAL = $('#' + KEY).val ();
		
		if (KEY == 'OCDBTSeedTimeToReach_OCDBTSeedTimeToReachUnit')
		{
			KEY = 'BTSeedTimeToReach_BTSeedTimeToReachUnit';
			VAL = $('#OCDBTSeedTimeToReach').val () + '_' + $('#OCDBTSeedTimeToReachUnit').val ();
		}
		
		$.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/personalsettings/save'),
	        method: 'POST',
			dataType: 'json',
			data: { 'KEY': KEY, 'VAL': VAL },
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
			}
	    });
	});
});