// Check URL
function ValidURL (URLString)
{
	var Pattern = new RegExp ("^(?:(?:https?|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?!(?:10|127)(?:\\.\\d{1,3}){3})(?!(?:169\\.254|192\\.168)(?:\\.\\d{1,3}){2})(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]-*)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]-*)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))(?::\\d{2,5})?(?:/\\S*)?$", "i");
	return Pattern.test (URLString);
}

// Print Error message
function PrintError (Message)
{
	$('.ocd .content-page[rel=OCDHTTP] span.add-msg').attr ('class', 'muted add-msg alert');
	$('.ocd .content-page[rel=OCDHTTP] span.add-msg').text (Message);
}
// Print Info message
function PrintInfo (Message)
{
	$('.ocd .content-page[rel=OCDHTTP] span.add-msg').attr ('class', 'muted add-msg info');
	$('.ocd .content-page[rel=OCDHTTP] span.add-msg').text (Message);
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
	        success: function (Data){
	            if (Data.ERROR)
				{
					PrintError(Data.MESSAGE);
				}
				else
				{
					$.each(Data.QUEUE, function (Index, Value)
					{
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="MESSAGE"]').text('Progress: ' + Value.PROGRESS);
						$('.ocd .content-queue > table > tbody > tr[data-rel="' + Value.GID + '"] > td[data-rel="STATUS"]').text(Value.STATUS);
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
		        success: function (Data){
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
		$('.ocd .content-page[rel=OCDHTTP] input.url').val ('');
		
		if (ValidURL (URL))
		{
			$.ajax({
		        url: OC.generateUrl ('/apps/ocdownloader/httpdownloaderadd'),
		        method: 'POST',
				dataType: 'json',
				data: {'URL' : URL},
		        async: true,
		        cache: false,
		        timeout: 30000,
		        success: function (Data){
		            if (Data.ERROR)
					{
						PrintError(Data.MESSAGE);
					}
					else
					{
						PrintInfo(Data.MESSAGE + ' (' + Data.GID + ')');
					}
					
					$('.ocd .content-queue > table > tbody').prepend('<tr data-rel="' + Data.GID + '">' + 
						'<td data-rel="NAME">' + Data.NAME + '</td>' +
						'<td data-rel="PROTO" class="border">' + Data.PROTO + '</td>' +
						'<td data-rel="MESSAGE" class="border">' + Data.MESSAGE + '</td>' +
						'<td data-rel="STATUS" class="border">' + Data.PROTO + '</td>' +
						'<td data-rel="ACTION"><div class="icon-delete svg"></div></td>' +
						'</tr>'
					);
					
					SetupRemoverFromQueue ();
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