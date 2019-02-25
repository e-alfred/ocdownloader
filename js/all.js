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
	
	OCDLR.Utils.UpdateQueue (true, 'all');
	
	OCDLR.Utils.GetCounters ();
  
  // new UI APP
  
  $('ul#filters li').on("click", function() {
    var active = $('ul#filters').data('active'); 
    id = 'ul#filters li[data-id='+ active + ']'; 
    $(id).removeClass('active');
  
    $('ul#filters').data('active', $(this).data('id') );
    $(this).addClass('active');
    
    OCDLR.Utils.UpdateQueue (true, $(this).data('id') );
    
    // call api Queue
    
      
  });
  
  // Analisis URI
  $('#app-content-wrapper .content-page[rel=OCDURI] input.url').bind ('input', function()
  {
    var InputURL = $(this).val();
		if (OCDLR.Utils.ValidURL (InputURL))
		{
			var dHandler = $('#app-content-wrapper .content-page[rel=OCDURI] div.handler');
			var dButtom = $('#app-content-wrapper .content-page[rel=OCDURI] div.launch')
			var dOptions = $('#app-content-wrapper .content-page[rel=OCDURI] div[rel=OCDOPTIONS]')
    	var handler = OCDLR.Utils.GetHandler(dHandler, dButtom, dOptions, InputURL);
		}

  });
  // Launch URI download
	$('#app-content-wrapper .content-page[rel=OCDURI] div.launch').bind ('click', function ()
	{
		var InputURL = $('#app-content-wrapper .content-page[rel=OCDURI] input.url');

		if (OCDLR.Utils.ValidURL (InputURL.val ()))
		{
			var OPTIONS = {};
			// FIXME: get options.
			var inputs  = $('#app-content-wrapper .content-page div[rel=OCDOPTIONS]').find(':input');
			$.each(inputs, function(k,v) {
				var id = $(this).attr('id');
				var val = $(this).is(':checkbox') ? $(this).prop ('checked') : $(this).val();
				OPTIONS[id] = val;
			});


			OCDLR.Utils.AddDownload ($(this), 'http', InputURL.val (), OPTIONS);
		}
		else
		{
			OCDLR.Utils.PrintError (t ('ocdownloader', 'Invalid URL. Please check the address of the file ...'));
		}
    
    InputURL.val ('');
    $('#app-content-wrapper .content-page[rel=OCDURI] div.handler').hide();
//    $('#app-content-wrapper .content-page[rel=OCDURI] div.handler').empty();
    
  });

});