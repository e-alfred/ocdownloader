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
	$('#ball').Badger ('...');
	$('#bcompletes').Badger ('...');
	$('#bactives').Badger ('...');
	$('#bwaitings').Badger ('...');
	$('#bstopped').Badger ('...');
	$('#bremoved').Badger ('...');

	OCDLR.Utils.UpdateQueue (true, 'add');

	// Display or hide the "New Download" menu
	$('#NewDL').bind ('click', function ()
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

	// Display or hide content pages depending on the "New Download" menu
	$('#NewDL').children ('div').children ('p').bind ('click', function ()
	{
		$('#app-content-wrapper .content-page').hide ();
		$('#app-content-wrapper .content-page[rel=' + $(this).attr ('data-rel') + ']').show ();
	});
	$('#NewDL').children ('div').bind ('click', function ()
	{
		$('#app-content-wrapper .content-page').hide ();
		$('#app-content-wrapper .content-page[rel=' + $(this).attr ('data-rel') + ']').show ();
	});

	// Default options
	$('#option-ftp-pasv').prop ('checked', true);
	$('#option-yt-extractaudio').prop ('checked', false);
	$('#option-yt-forceipv4').prop ('checked', true);
	$('#option-bt-rmtorrent').prop ('checked', true);

	// Load torrent files when opening the available torrents list
	$('#TorrentsList').bind ('click', function ()
	{
		OCDLR.Utils.GetTorrentsList ($(this).children ('ul'));
	});

	// Launch HTTP download
	$('#app-content-wrapper .content-page[rel=OCDHTTP] div.launch').bind ('click', function ()
	{
		var InputURL = $(this).parent ().parent ().children ('input.url');

		if (OCDLR.Utils.ValidURL (InputURL.val ()))
		{
			OCDLR.Utils.AddDownload ($(this), 'http', InputURL.val (),
			{
				HTTPUser: $('#option-http-user').val (),
				HTTPPasswd: $('#option-http-pwd').val ()
        HTTPReferrer: $('#option-http-referrer').val ()
        HTTPUseragent: $('#option-http-useragent').val ()
			});
		}
		else
		{
			OCDLR.Utils.PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}

		InputURL.val ('');
		$('#option-http-user').val ('');
		$('#option-http-pwd').val ('');
	});

	// Launch FTP download
	$('#app-content-wrapper .content-page[rel=OCDFTP] div.launch').bind ('click', function ()
	{
		var InputURL = $(this).parent ().parent ().children ('input.url');

		if (OCDLR.Utils.ValidURL (InputURL.val ()))
		{
			OCDLR.Utils.AddDownload ($(this), 'ftp', InputURL.val (),
			{
				FTPUser: $('#option-ftp-user').val (),
				FTPPasswd: $('#option-ftp-pwd').val (),
				FTPPasv: $('#option-ftp-pasv').prop ('checked')
			});
		}
		else
		{
			OCDLR.Utils.PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}

		InputURL.val ('');
		$('#option-ftp-user').val ('');
		$('#option-ftp-pwd').val ('');
		$('#option-ftp-pasv').prop ('checked', true);
	});

	// Launch YT download
	$('#app-content-wrapper .content-page[rel=OCDYT] div.launch').bind ('click', function ()
	{
		var InputURL = $(this).parent ().parent ().children ('input.url');

		if (OCDLR.Utils.ValidURL (InputURL.val ()))
		{
			OCDLR.Utils.AddDownload ($(this), 'yt', InputURL.val (),
			{
				YTExtractAudio: $('#option-yt-extractaudio').prop ('checked'),
				YTForceIPv4: $('#option-yt-forceipv4').prop ('checked')
			});
		}
		else
		{
			OCDLR.Utils.PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}

		InputURL.val ('');
		$('#option-yt-extractaudio').prop ('checked', false);
		$('#option-yt-forceipv4').prop ('checked', true);
	});

	// Launch BT download
	$('#app-content-wrapper .content-page[rel=OCDBT] div.launch').bind ('click', function ()
	{
		var InputFile = $('#TorrentsList').children ('a');
		var SELECTTEXT = t ('ocdownloader', 'Select a file.torrent');

		if (InputFile.text ().trim () != SELECTTEXT.trim () && InputFile.prop ('data-rel') == 'File')
		{
			OCDLR.Utils.AddDownload ($(this), 'bt', InputFile.text (),
			{
				BTRMTorrent: $('#option-bt-rmtorrent').prop ('checked')
			});
		}

		InputFile.text (SELECTTEXT);
		$('#option-bt-rmtorrent').prop ('checked', true);
	});

	var FileUpload = $('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile #uploadfile').fileupload (
	{
		autoUpload: true,
		sequentialUploads: true,
		type: 'POST',
		dataType: 'json'
	});
	FileUpload.on ('fileuploadstart', function (E, Data)
	{
		$('#TorrentsList').children ('ul').hide ();

		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile label').removeClass ('icon-upload');
		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile label').addClass ('icon-loading-small');
		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile input').prop('disabled', true);
	});
	FileUpload.on ('fileuploaddone', function (E, Data)
	{
		if (!Data.result.ERROR)
		{
			OCDLR.Utils.PrintInfo (Data.result.MESSAGE);
		}
		else
		{
			OCDLR.Utils.PrintError (Data.result.MESSAGE);
		}

		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile input').prop('disabled', false);
		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile label').removeClass ('icon-loading-small');
		$('#app-content-wrapper .content-page[rel=OCDBT] div.uploadfile label').addClass ('icon-upload');
	});
	FileUpload.on ('fileuploadfail', function (E, Data)
	{
		OCDLR.Utils.PrintError (t ('ocdownloader', 'Error while uploading torrent file'));
	});

	OCDLR.Utils.GetCounters ();
});
