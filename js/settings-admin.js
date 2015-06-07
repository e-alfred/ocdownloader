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
	$('form#ocdownloader > p > input[type="text"].ToUse, form#ocdownloader > p > input[type="password"].ToUse').bind ('change', function ()
	{
		$('#OCDSLoader').show ();
		$('#OCDSMsg').hide ();
		$('#OCDSMsg').removeClass ('error');
		$('#OCDSMsg').removeClass ('success');
		
		$.ajax({
	        url: OC.generateUrl ('/apps/ocdownloader/adminsettings/save'),
	        method: 'POST',
			dataType: 'json',
			data: {
				'YTDLBinary': $('#OCDYTDLBinary').val (),
				'ProxyAddress': $('#OCDProxyAddress').val (),
				'ProxyPort': $('#OCDProxyPort').val (),
				'ProxyUser': $('#OCDProxyUser').val (),
				'ProxyPasswd': $('#OCDProxyPasswd').val ()
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
				$('#OCDYTDLBinary').val (Data.SETTINGS.OCDS_YTDLBinary);
				$('#OCDProxyAddress').val (Data.SETTINGS.OCDS_ProxyAddress);
				$('#OCDProxyPort').val (Data.SETTINGS.OCDS_ProxyPort);
				$('#OCDProxyUser').val (Data.SETTINGS.OCDS_ProxyUser);
				$('#OCDProxyPasswd').val (Data.SETTINGS.OCDS_ProxyPasswd);
			}
	    });
	});
});