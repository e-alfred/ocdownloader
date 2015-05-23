/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */
 
// Check URL
function ValidURL (URLString)
{
	return /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(URLString);
}

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

function GetDownloaderQueue(){
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
					PrintError(Data.MESSAGE);
				}
				else
				{
					$.each(Data.QUEUE, function (Index, Value)
					{
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value > div.pb-text').text ('Progress: ' + Value.PROGRESS);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value').css ('width', Value.PROGRESSVAL);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="SPEED"]').text (Value.SPEED);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="STATUS"]').text (Value.STATUS);
						
						if (Value.GID.indexOf('YT_') === 0 && Value.STATUS == 'Complete')
						{
							$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="ACTION"]').html ('<div class="icon-delete svg"></div>');
							SetupRemoverFromQueue ();
						}
					});
				}
				
				$('div#loadtext').hide();
	        }
	    });
	}
}

function SetupRemoverFromQueue ()
{
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-delete').unbind('click');
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-delete').bind ('click', function ()
	{
		var TR = $(this).parent ().parent ();
		var GID = TR.attr ('data-rel');
		if (GID)
		{
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/downloaderremovequeue'),
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
						PrintError(Data.MESSAGE);
					}
					else
					{
						PrintInfo(Data.MESSAGE + ' (' + GID + ')');
						TR.remove();
					}
		        }
		    });
		}
		else
		{
			PrintError ('Unable to find the GID of this download ...')
		}
	});
}

$(document).ready (function()
{
	// Get current downloader queue every 5 seconds
    setInterval(function(){ GetDownloaderQueue(); }, 5000);
    GetDownloaderQueue();
	
	// Display or hide the "New Download" menu
	$('div#new').bind ('click', function ()
	{
		if ($('div#new > ul').is (':visible'))
		{
			$('div#new > ul').hide ();
		}
		else
		{
			$('div#new > ul').show ();
		}
	});
	
	// Display or hide content pages depending on the "New Download" menu
	$('div#new > ul > li > p').bind ('click', function ()
	{
		$('.ocd .content-page').hide ();
		$('.ocd .content-page[rel=' + $(this).attr ('data-rel') + ']').show ();
	});
	
	// Launch HTTP download
	$('.ocd .content-page[rel=OCDHTTP] div.launch').bind ('click', function ()
	{
		var URL = $('.ocd .content-page[rel=OCDHTTP] input.url').val ();
		
		if (ValidURL (URL))
		{
			var OPTIONS = {
				HTTPUser: $('#option-http-user').val (),
				HTTPPasswd: $('#option-http-pwd').val ()
			};
			
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/httpdownloaderadd'),
		        method: 'POST',
				dataType: 'json',
				data: {'URL' : URL, 'OPTIONS' : OPTIONS},
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
						PrintInfo (Data.MESSAGE + ' (' + Data.GID + ')');
						
						$('.ocd .content-queue > table > tbody').prepend ('<tr data-rel="' + Data.GID + '">' + 
							'<td data-rel="NAME" class="padding">' + Data.NAME + '</td>' +
							'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
							'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
							'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
							'<td data-rel="STATUS" class="border padding">Waiting</td>' +
							'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div></td>' +
							'</tr>'
						);
						
						SetupRemoverFromQueue ();
						
						$('.ocd .content-page[rel=OCDHTTP] input[type="text"]').val ('');
						$('.ocd .content-page[rel=OCDHTTP] input[type="password"]').val ('');
					}
		        }
		    });
		}
		else
		{
			PrintError('Unvalid URL. Please check the address of the file ...');
		}
	});
	
	// Launch FTP download
	$('.ocd .content-page[rel=OCDFTP] div.launch').bind ('click', function ()
	{
		var URL = $('.ocd .content-page[rel=OCDFTP] input.url').val ();
		
		if (ValidURL (URL))
		{
			var OPTIONS = {
				FTPUser: $('#option-ftp-user').val (),
				FTPPasswd: $('#option-ftp-pwd').val (),
				FTPPasv: $('#option-ftp-pasv').prop ('checked')
			};
			
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/ftpdownloaderadd'),
		        method: 'POST',
				dataType: 'json',
				data: {'URL' : URL, 'OPTIONS' : OPTIONS},
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
						PrintInfo (Data.MESSAGE + ' (' + Data.GID + ')');
					}
					
					$('.ocd .content-queue > table > tbody').prepend ('<tr data-rel="' + Data.GID + '">' + 
						'<td data-rel="NAME" class="padding">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
						'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
						'<td data-rel="STATUS" class="border padding">Waiting</td>' +
						'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div></td>' +
						'</tr>'
					);
					
					SetupRemoverFromQueue ();
					
					// Reset form field
					$('.ocd .content-page[rel=OCDFTP] input[type="text"]').val ('');
					$('.ocd .content-page[rel=OCDFTP] input[type="password"]').val ('');
					$('#option-ftp-pasv').prop ('checked', true);
		        }
		    });
		}
		else
		{
			PrintError('Unvalid URL. Please check the address of the file ...');
		}
	});
	
	$('.ocd .content-page[rel=OCDYT] div.launch').bind ('click', function ()
	{
		var AddBtn = $(this);
		AddBtn.prop('disabled', true);
		AddBtn.empty();
		AddBtn.addClass('icon-loading-small');
		
		var URL = $('.ocd .content-page[rel=OCDYT] input.url').val ();
		
		if (ValidURL (URL))
		{
			/*var OPTIONS = {
			};*/
			
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/ytdownloaderadd'),
		        method: 'POST',
				dataType: 'json',
				data: {'URL' : URL/*, 'OPTIONS' : OPTIONS*/},
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
						PrintInfo (Data.MESSAGE + ' (' + Data.GID + ')');
					}
					
					$('.ocd .content-queue > table > tbody').prepend ('<tr data-rel="' + Data.GID + '">' + 
						'<td data-rel="NAME" class="padding">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
						'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
						'<td data-rel="STATUS" class="border padding">Waiting</td>' +
						'<td data-rel="ACTION" class="padding"></td>' +
						'</tr>'
					);
					
					SetupRemoverFromQueue ();
					
					// Reset form field
					$('.ocd .content-page[rel=OCDYT] input[type="text"]').val ('');
					
					// Reset add button
					AddBtn.prop('disabled', false);
					AddBtn.html('<a>Launch YouTube Download</a>');
					AddBtn.removeClass('icon-loading-small');
		        }
		    });
		}
		else
		{
			PrintError('Unvalid URL. Please check the address of the file ...');
		}
	});
	
	SetupRemoverFromQueue ();
});