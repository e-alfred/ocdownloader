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

function GetDownloaderQueue ()
{
	var GIDS = [];
	$('.ocd .content-queue > table > tbody > tr').each (function ()
	{
		GIDS.push ($(this).attr ('data-rel'));
	});
	
	if (GIDS.length > 0)
	{
		$('div#loadtext').show ();
	    $.ajax ({
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
						if (Value.STATUS == 'Complete')
						{
							$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="FILENAME"]').html ('<a href="' + OC.linkTo ('files', 'index.php') + '?dir=' + encodeURIComponent (GetPersonalSetting ('DownloadsFolder')).replace(/%2F/g, '/') + '">' + Value.FILENAME + '</a>');
						}
						
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value > div.pb-text').text (Value.PROGRESS);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value').css ('width', Value.PROGRESSVAL);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="SPEED"]').text (Value.SPEED);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="STATUS"]').text (Value.STATUS);
					});
				}
				
				$('div#loadtext').hide ();
	        }
	    });
	}
}

function SetupActionButtons ()
{
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.svg').unbind ('click');
	
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-delete').bind ('click', function ()
	{
		var BTN = $(this);
		BTN.addClass ('icon-loading-small');
		BTN.removeClass ('icon-delete');
		
		var TR = BTN.parent ().parent ();
		var GID = TR.attr ('data-rel');
		if (GID)
		{
			$.ajax ({
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
						PrintError (Data.MESSAGE);
						BTN.addClass ('icon-delete');
						BTN.removeClass ('icon-loading-small');
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
			PrintError (t ('ocdownloader', 'Unable to find the GID for this download ...'));
		}
	});
	
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-pause').bind ('click', function ()
	{
		var BTN = $(this);
		BTN.addClass ('icon-loading-small');
		BTN.removeClass ('icon-pause');
		
		var TR = BTN.parent ().parent ();
		var GID = TR.attr ('data-rel');
		if (GID)
		{
			$.ajax ({
		        url: OC.generateUrl ('/apps/ocdownloader/downloadersetpause'),
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
						BTN.addClass ('icon-pause');
					}
					else
					{
						PrintInfo (Data.MESSAGE + ' (' + GID + ')');
						BTN.addClass ('icon-play');
						SetupActionButtons ();
					}
					BTN.removeClass ('icon-loading-small');
		        }
		    });
		}
		else
		{
			PrintError (t ('ocdownloader', 'Unable to find the GID for this download ...'));
		}
	});
	
	$('.ocd .content-queue > table > tbody > tr > td[data-rel="ACTION"] > div.icon-play').bind ('click', function ()
	{
		var BTN = $(this);
		BTN.addClass ('icon-loading-small');
		BTN.removeClass ('icon-play');
		
		var TR = BTN.parent ().parent ();
		var GID = TR.attr ('data-rel');
		if (GID)
		{
			$.ajax ({
		        url: OC.generateUrl ('/apps/ocdownloader/downloadersetunpause'),
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
						BTN.addClass ('icon-play');
					}
					else
					{
						PrintInfo (Data.MESSAGE + ' (' + GID + ')');
						BTN.addClass ('icon-pause');
						SetupActionButtons ();
					}
					BTN.removeClass ('icon-loading-small');
		        }
		    });
		}
		else
		{
			PrintError (t ('ocdownloader', 'Unable to find the GID for this download ...'));
		}
	});
}

function GetPersonalSetting (KEY)
{
	var VAL = 'Downloads/Files/Torrents';
	
	$.ajax ({
        url: OC.generateUrl ('/apps/ocdownloader/getpersonalsetting'),
        method: 'POST',
		dataType: 'json',
		data: {'KEY' : KEY},
        async: false,
        cache: false,
        timeout: 30000,
        success: function (Data)
		{
            if (!Data.ERROR)
			{
				VAL = Data.VAL;
			}
        }
    });
	
	return VAL;
}

$(document).ready (function ()
{
	// Get current downloader queue every 5 seconds
    setInterval (function (){ GetDownloaderQueue(); }, 5000);
    GetDownloaderQueue();
	
	// Display or hide the "New Download" menu
	$('div#new').bind ('click', function ()
	{
		if ($(this).children ('ul').is (':visible'))
		{
			$(this).children ('ul').hide ();
		}
		else
		{
			$(this).children ('ul').show ();
		}
	});
	
	// Load torrent files when opening the available torrents list
	$('div#torrentlist').bind ('click', function ()
	{
		if ($(this).children ('ul').is (':visible'))
		{
			$(this).children ('ul').hide ();
		}
		else
		{
			$(this).children ('ul').show ();
			$('div#torrentlist > ul').empty ();
			$('div#torrentlist > ul').append ('<li><p class="loader"><span class="icon-loading-small"></span></p></li>');
			
			$.ajax ({
		        url: OC.generateUrl ('/apps/ocdownloader/listtorrentfiles'),
		        method: 'POST',
				dataType: 'json',
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
						$('div#torrentlist > ul').empty ();
						
						if (Data.FILES.length == 0)
						{
							$('div#torrentlist > ul').append ('<li><p>' + t ('ocdownloader', 'No Torrent Files') + ', <a href="' + OC.linkTo ('files', 'index.php') + '?dir=' + encodeURIComponent (GetPersonalSetting ('TorrentsFolder')).replace(/%2F/g, '/') + '">' + t ('ocdownloader', 'Upload') + '</a></p></li>');
						}
						
						for (var I = 0; I < Data.FILES.length; I++)
						{
							if (Data.FILES[I].name.match(/\.torrent$/))
							{
								$('div#torrentlist > ul').append ('<li><p class="clickable">' + Data.FILES[I].name + '</p></li>');
							}
						}
						
						// Select a torrent in the proposed list 
						$('div#torrentlist > ul > li > p.clickable').bind ('click', function ()
						{
							$(this).parent ().parent ().parent ().children ('a').prop ('data-rel', 'File');
							$(this).parent ().parent ().parent ().children ('a').html ($(this).text () + '<div class="icon-caret-dark svg"></div>');
						});
					}
		        }
		    });
		}
	});
	
	// Display or hide content pages depending on the "New Download" menu
	$('div#new > ul > li > p').bind ('click', function ()
	{
		$('.ocd .content-page').hide ();
		$('.ocd .content-page[rel=' + $(this).attr ('data-rel') + ']').show ();
	});
	
	// Reset YT options
	$('#option-yt-extractaudio').prop ('checked', false);
	$('#option-yt-ea-format option[value="best"]').prop ('selected', true);
	$('#option-yt-ea-qual option[value="0"]').prop ('selected', true);
	
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
			
			$.ajax ({
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
							'<td data-rel="STATUS" class="border padding">' + t ('ocdownloader', 'Waiting') + '</td>' +
							'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div><div class="icon-pause svg"></div></td>' +
							'</tr>'
						);
						
						SetupActionButtons ();
						
						$('.ocd .content-page[rel=OCDHTTP] input[type="text"]').val ('');
						$('.ocd .content-page[rel=OCDHTTP] input[type="password"]').val ('');
					}
		        }
		    });
		}
		else
		{
			PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
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
			
			$.ajax ({
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
						'<td data-rel="FILENAME" class="padding">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
						'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
						'<td data-rel="STATUS" class="border padding">' + t ('ocdownloader', 'Waiting') + '</td>' +
						'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div><div class="icon-pause svg"></div></td>' +
						'</tr>'
					);
					
					SetupActionButtons ();
					
					// Reset form field
					$('.ocd .content-page[rel=OCDFTP] input[type="text"]').val ('');
					$('.ocd .content-page[rel=OCDFTP] input[type="password"]').val ('');
					$('#option-ftp-pasv').prop ('checked', true);
		        }
		    });
		}
		else
		{
			PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}
	});
	
	// Launch YT download
	$('.ocd .content-page[rel=OCDYT] div.launch').bind ('click', function ()
	{
		var AddBtn = $(this);
		AddBtn.prop ('disabled', 'disabled');
		AddBtn.empty ();
		AddBtn.addClass ('icon-loading-small');
		
		var URL = $('.ocd .content-page[rel=OCDYT] input.url').val ();
		
		if (ValidURL (URL))
		{
			var OPTIONS = {
				YTExtractAudio: $('#option-yt-extractaudio').prop ('checked')
			};
			
			$.ajax ({
		        url: OC.generateUrl ('/apps/ocdownloader/ytdownloaderadd'),
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
						'<td data-rel="FILENAME" class="padding">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
						'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
						'<td data-rel="STATUS" class="border padding">' + t ('ocdownloader', 'Waiting') + '</td>' +
						'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div><div class="icon-pause svg"></div></td>' +
						'</tr>'
					);
					
					SetupActionButtons ();
					
					// Reset form field
					$('.ocd .content-page[rel=OCDYT] input[type="text"]').val ('');
					$('#option-yt-extractaudio').prop ('checked', false);
					
					// Reset add button
					AddBtn.prop('disabled', false);
					AddBtn.html('<a>' + t ('ocdownloader', 'Launch YouTube Download') + '</a>');
					AddBtn.removeClass('icon-loading-small');
		        }
		    });
		}
		else
		{
			PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}
	});
	
	// Launch BT download
	$('.ocd .content-page[rel=OCDBT] div.launch').bind ('click', function ()
	{
		var PATH = $('.ocd .content-page[rel=OCDBT] .actions > div#torrentlist a').text ();
		var DATAREL = $('.ocd .content-page[rel=OCDBT] .actions > div#torrentlist a').prop ('data-rel');
		
		if (DATAREL == 'File')
		{
			/*var OPTIONS = {
				YTExtractAudio: $('#option-yt-extractaudio').prop ('checked')
			};*/
			
			$.ajax ({
		        url: OC.generateUrl ('/apps/ocdownloader/btdownloaderadd'),
		        method: 'POST',
				dataType: 'json',
				data: {'PATH' : PATH/*, 'OPTIONS' : OPTIONS*/},
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
						'<td data-rel="FILENAME" class="padding">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border padding">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: 0%;"><div class="pb-text">' + Data.MESSAGE + '</div></div></div></td>' +
						'<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' +
						'<td data-rel="STATUS" class="border padding">' + t ('ocdownloader', 'Waiting') + '</td>' +
						'<td data-rel="ACTION" class="padding"><div class="icon-delete svg"></div><div class="icon-pause svg"></div></td>' +
						'</tr>'
					);
					
					SetupActionButtons ();
		        }
		    });
		}
	});
	
	SetupActionButtons ();
});