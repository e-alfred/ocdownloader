/**
 * ownCloud - OCDLR
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */

OCDLR = {};

(function ()
{
	OCDLR.Utils =
	{
		BaseURL: '/apps/ocdownloader/',
		BaseID: '#app-content-wrapper ',
		MenuID: '#app-navigation ',
		Queue: '',
		QueueHeader: '',
		QueueElt: '',
		DownloadsFolder: '',
		TorrentsFolder: '',
		WhichDownloader: '',
		BadgerStatus: [],

		ValidURL: function (URLString)
		{
			try {
				new URL(URLString);
				return true;
			} catch (_) {
				return false;
			}
		},

		PrintError: function (Msg)
		{
			$(OCDLRSelf.BaseID + 'span.muted').removeClass ('info alert');
			$(OCDLRSelf.BaseID + 'span.muted').addClass ('alert').text (Msg);
		},

		PrintInfo: function (Msg)
		{
			$(OCDLRSelf.BaseID + 'span.muted').removeClass ('info alert');
			$(OCDLRSelf.BaseID + 'span.muted').addClass ('info').text (Msg);
		},

		UpdateQueue: function (DynMode, View)
		{
			var IntervalHandle = setInterval (function ()
			{
				clearInterval (IntervalHandle);

				if ($(OCDLRSelf.QueueElt).length > 0)
				{
					$(OCDLRSelf.BaseID + '.loadingtext').show ();
				    $.ajax ({
				        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/get'),
				        method: 'POST',
						data: {'VIEW' : View},
						dataType: 'json',
				        async: true,
				        cache: false,
				        timeout: 30000,
				        success: function (Data)
						{
				            if (Data.ERROR)
							{
								OCDLRSelf.PrintError (Data.MESSAGE);
							}
							else
							{
								if ($(OCDLRSelf.QueueElt + '[data-rel="LOADER"]').length != 0)
								{
									$(OCDLRSelf.QueueElt + '[data-rel="LOADER"]').remove ();
								}

								$('#ball').Badger (Data.COUNTER.ALL);
								$('#bcompletes').Badger (Data.COUNTER.COMPLETES);
								$('#bactives').Badger (Data.COUNTER.ACTIVES);
								$('#bwaitings').Badger (Data.COUNTER.WAITINGS);
								$('#bstopped').Badger (Data.COUNTER.STOPPED);
								$('#bremoved').Badger (Data.COUNTER.REMOVED);

								var DBGIDS = [];
								$.each (Data.QUEUE, function (Index, Value)
								{
									var QueueElt = OCDLRSelf.QueueElt + '[data-rel="' + Value.GID + '"]';
									DBGIDS.push (Value.GID);

									if (DynMode && $(QueueElt).length == 0)
									{
										OCDLRSelf.PrependToQueue (Value, View);
									}

									if ($(QueueElt + ' > td[data-rel="ACTION"] > div.icon-pause').length == 0 && Value.STATUSID == 1)
									{
										OCDLRSelf.ActionPause ($(QueueElt + ' > td[data-rel="ACTION"]'), View);
									}
									if ($(QueueElt + ' > td[data-rel="ACTION"] > div.icon-play').length == 0 && Value.STATUSID == 3)
									{
										OCDLRSelf.ActionUnPause ($(QueueElt + ' > td[data-rel="ACTION"]'), View);
									}

									if (Value.PROGRESSVAL == '100%')
									{
										$(QueueElt + ' > td[data-rel="FILENAME"]').html ('<a title="'+Value.FILENAME+'" href="' + OC.linkTo ('files', 'index.php') + '?dir=' + encodeURIComponent (OCDLRSelf.DownloadsFolder).replace(/%2F/g, '/') + '">' + Value.FILENAME_SHORT + '</a>');

										if (Value.ISTORRENT && Value.SPEED != '--')
										{
											$(QueueElt + ' > td[data-rel="SPEED"]').html ('<div class="icon-upload svg"></div>' + Value.SPEED);
										}
										else
										{
											$(QueueElt + ' > td[data-rel="SPEED"]').html (Value.SPEED);

											if (!Value.ISTORRENT)
											{
												$(QueueElt + ' > td[data-rel="ACTION"] > div.icon-pause').remove ();
												$(QueueElt + ' > td[data-rel="ACTION"] > div.icon-play').remove ();
											}
										}
									}
									else
									{
										if (Value.SPEED != '--')
										{
											$(QueueElt + ' > td[data-rel="SPEED"]').html ('<div class="icon-download svg"></div>' + Value.SPEED);
										}
										else
										{
											$(QueueElt + ' > td[data-rel="SPEED"]').html (Value.SPEED);
										}
									}

									$(QueueElt + ' > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value > div.pb-text').html (Value.PROGRESS);
									$(QueueElt + ' > td[data-rel="MESSAGE"] > div.pb-wrap > div.pb-value').css ('width', Value.PROGRESSVAL);
									$(QueueElt + ' > td[data-rel="STATUS"]').text (Value.STATUS);
								});

								OCDLRSelf.RemoveQueueItems (DBGIDS);
							}

							$(OCDLRSelf.BaseID + '.loadingtext').hide ();
				        }
				    });
				}
				OCDLRSelf.UpdateQueue ((View == 'add' ? false : true), View);
			}, 3000);
		},

		RemoveQueueItem: function (Item)
		{
			if (Item.parent ().children ().length == 1)
			{
				$(OCDLRSelf.Queue + ' th[data-rel="ACTION"] > div').remove ();
			}

			Item.remove ();
		},

		RemoveQueueItems: function (Items)
		{
			$(OCDLRSelf.QueueElt).each (function ()
			{
				if (Items.indexOf ($(this).attr ('data-rel')) == -1)
				{
					$(this).remove ();
				}
			});
			if ($(OCDLRSelf.QueueElt).length == 0)
			{
				$(OCDLRSelf.Queue + '> thead th[data-rel="ACTION"] > div').remove ();
			}
		},

		HideFromQueue: function (Button)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-close');

			var TR = Button.parent ().parent ();
			var GID = TR.attr ('data-rel');
			if (GID)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/hide'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
							Button.addClass ('icon-close');
							Button.removeClass ('icon-loading-small');
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE + ' (' + GID + ')');
							OCDLRSelf.RemoveQueueItem (TR);
						}
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'Unable to find the GID for this download…'));
			}
		},

		HideAllFromQueue: function (Button)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-close');

			var GIDS = [];
			$(OCDLRSelf.QueueElt + ' td[data-rel="ACTION"] > div.icon-close').each (function ()
			{
				$(this).addClass ('icon-loading-small');
				$(this).removeClass ('icon-close');

				GIDS.push ($(this).parent ().parent ().attr ('data-rel'));
			});

			if (GIDS.length > 0)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/hideall'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE);
							$.each (Data.QUEUE, function (Index, Value)
							{
								$(OCDLRSelf.QueueElt + '[data-rel="' + Value.GID + '"]').remove ();
							});
							$(OCDLRSelf.Queue + ' th[data-rel="ACTION"] > div.icon-loading-small').remove ();
						}
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'No downloads in the queue…'));
			}
		},

		PauseDownload: function (Button, View)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-pause');

			$('#bactives').Badger ('-1');
			$('#bstopped').Badger ('+1');

			var TR = Button.parent ().parent ();
			var GID = TR.attr ('data-rel');
			if (GID)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/pause'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
							Button.addClass ('icon-pause');
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE + ' (' + GID + ')');

							if (['add', 'all'].indexOf (View) > -1)
							{
								Button.addClass ('icon-play');

								Button.parent ().parent ().children ('td[data-rel="STATUS"]').attr ('data-statusid', 3);
								Button.parent ().parent ().children ('td[data-rel="STATUS"]').text (t ('ocdownloader', 'Paused'));
								Button.unbind ('click');
								Button.bind ('click', function ()
								{
									OCDLRSelf.UnPauseDownload ($(this), View);
								});
							}
							else
							{
								OCDLRSelf.RemoveQueueItem (TR);
							}
						}
						Button.removeClass ('icon-loading-small');
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'Unable to find the GID for this download…'));
			}
		},

		UnPauseDownload: function (Button, View)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-play');

			$('#bactives').Badger ('+1');
			$('#bstopped').Badger ('-1');

			var TR = Button.parent ().parent ();
			var GID = TR.attr ('data-rel');
			if (GID)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/unpause'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
							Button.addClass ('icon-play');
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE + ' (' + GID + ')');

							if (['add', 'all'].indexOf (View) > -1)
							{
								Button.addClass ('icon-pause');

								Button.parent ().parent ().children ('td[data-rel="STATUS"]').attr ('data-statusid', 1);
								Button.parent ().parent ().children ('td[data-rel="STATUS"]').text (t ('ocdownloader', 'Active'));
								Button.unbind ('click');
								Button.bind ('click', function ()
								{
									OCDLRSelf.PauseDownload ($(this), View);
								});
							}
							else
							{
								OCDLRSelf.RemoveQueueItem (TR);
							}
						}
						Button.removeClass ('icon-loading-small');
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'Unable to find the GID for this download…'));
			}
		},

		RemoveFromQueue: function (Button, Completely, View)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-delete');

			var TR = Button.parent ().parent ();
			var GID = TR.attr ('data-rel');
			if (GID)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/' + Completely + 'remove'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
							Button.addClass ('icon-delete');
							Button.removeClass ('icon-loading-small');
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE + ' (' + GID + ')');
							if (View != 'all' || Completely.length > 0)
							{
								OCDLRSelf.RemoveQueueItem (TR);
							}
							else if (TR.children ('td[data-rel="STATUS"]').attr ('data-statusid') == '3')
							{
								$('#bremoved').Badger ('-1');
								$('#ball').Badger ('-1');
								OCDLRSelf.RemoveFromQueue (Button, 'completely', View);
							}
							else
							{
								var TD = Button.parent ();
								TD.children ('div').remove ();

								TD.parent ().children ('td[data-rel="STATUS"]').text (t ('ocdownloader', 'Removed'));
								OCDLRSelf.ActionDeleteCompletely (TD);
							}
						}
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'Unable to find the GID for this download…'))
			}
		},

		RemoveAllFromQueue: function (Button, Completely, View)
		{
			Button.addClass ('icon-loading-small');
			Button.removeClass ('icon-delete');

			var GIDS = [];
			$(OCDLRSelf.QueueElt + ' td[data-rel="ACTION"] > div.icon-delete').each (function ()
			{
				$(this).addClass ('icon-loading-small');
				$(this).removeClass ('icon-delete');

				GIDS.push ($(this).parent ().parent ().attr ('data-rel'));
			});

			if (GIDS.length > 0)
			{
				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/' + Completely + 'removeall'),
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
							OCDLRSelf.PrintError (Data.MESSAGE);
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE);
							OCDLRSelf.RemoveQueueItems (Data.GIDS);

							if (Completely.length > 0)
							{
								$('#bremoved').Badger ('-' + GIDS.length);
								$('#ball').Badger ('-' + GIDS.length);
							}
							else
							{
								$('#bremoved').Badger ('+' + GIDS.length);
								$('#b' + View).Badger ('-' + GIDS.length);
							}
						}
			        }
			    });
			}
			else
			{
				OCDLRSelf.PrintError (t ('ocdownloader', 'Unable to find the GID for this download…'));
			}
		},

		GetPersonalSettings: function ()
		{
			$.ajax ({
		        url: OC.generateUrl (OCDLRSelf.BaseURL + 'personalsettings/get'),
		        method: 'GET',
				dataType: 'json',
		        async: false,
		        cache: false,
		        timeout: 30000,
		        success: function (Data)
				{
		            if (!Data.ERROR)
					{
						$.each (Data.VALS, function (Key, Value)
						{
                            OCDLRSelf[Key] = Value;
						});
					}
		        }
		    });
		},

		GetAdminSettings: function (KEYS)
		{
			$.ajax ({
		        url: OC.generateUrl (OCDLRSelf.BaseURL + 'adminsettings/get'),
		        method: 'POST',
				dataType: 'json',
				data: {'KEYS' : KEYS},
		        async: false,
		        cache: false,
		        timeout: 30000,
		        success: function (Data)
				{
		            if (!Data.ERROR)
					{
						$.each (Data.VALS, function (Key, Value)
						{
                            OCDLRSelf[Key] = Value;
						});
					}
		        }
		    });
		},

		GetTorrentsList: function (LIST)
		{
			if (LIST.is (':visible'))
			{
				LIST.hide ();
			}
			else
			{
				LIST.empty ();
				LIST.append ('<li><p class="loader"><span class="icon-loading-small"></span></p></li>');
				LIST.show ();

				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + 'btdownloader/listtorrentfiles'),
			        method: 'POST',
					dataType: 'json',
			        async: true,
			        cache: false,
			        timeout: 30000,
			        success: function (Data)
					{
			            if (Data.ERROR)
						{
							OCDLRSelf.PrintError (Data.MESSAGE);
						}
						else
						{
							LIST.empty ();

							if (Data.FILES.length == 0)
							{
								LIST.append ('<li><p>' + t ('ocdownloader', 'No Torrent Files') + ', <a href="' + OC.linkTo ('files', 'index.php') + '?dir=' + encodeURIComponent (OCDLRSelf.TorrentsFolder).replace(/%2F/g, '/') + '">' + t ('ocdownloader', 'Upload') + '</a></p></li>');
							}
							else
							{
								for (var I = 0; I < Data.FILES.length; I++)
								{
									if (Data.FILES[I].name.match(/\.torrent$/))
									{
										LIST.append ('<li><p class="clickable">' + Data.FILES[I].name + '</p></li>');
									}
								}

								// Select a torrent in the proposed list
								LIST.children ('li').children ('p.clickable').bind ('click', function ()
								{
									LIST.parent ().children ('a').prop ('data-rel', 'File');
									LIST.parent ().children ('a').html ($(this).text () + '<div class="icon-caret-dark svg"></div>');
								});
							}
						}
			        }
			    });
			}
		},

		GetCounters: function ()
		{
			$.ajax ({
		        url: OC.generateUrl (OCDLRSelf.BaseURL + 'queue/count'),
		        method: 'POST',
				dataType: 'json',
		        async: true,
		        cache: false,
		        timeout: 30000,
		        success: function (Data)
				{
		            if (Data.ERROR)
					{
						OCDLRSelf.PrintError (Data.MESSAGE);
					}
					else
					{
						$('#ball').Badger (Data.COUNTER.ALL);
						$('#bcompletes').Badger (Data.COUNTER.COMPLETES);
						$('#bactives').Badger (Data.COUNTER.ACTIVES);
						$('#bwaitings').Badger (Data.COUNTER.WAITINGS);
						$('#bstopped').Badger (Data.COUNTER.STOPPED);
						$('#bremoved').Badger (Data.COUNTER.REMOVED);
					}
		        }
		    });
		},

		PrependToQueue: function (Data, View)
		{
			$(OCDLRSelf.Queue + '> tbody').prepend ('<tr data-rel="' + Data.GID + '">' +
				'<td data-rel="FILENAME" class="padding">' + Data.FILENAME_SHORT + '</td>' +
				'<td data-rel="PROTO" class="border padding">' + t ('ocdownloader', Data.PROTO) + '</td>' +
				'<td data-rel="MESSAGE" class="border"><div class="pb-wrap"><div class="pb-value" style="width: ' + Data.PROGRESSVAL + ';"><div class="pb-text">' + Data.PROGRESS + '</div></div></div></td>' +
				(['add', 'actives', 'all'].indexOf (View) > -1 ? '<td data-rel="SPEED" class="border padding">' + Data.SPEED + '</td>' : '') +
				(['add', 'all'].indexOf (View) > -1 ? '<td data-rel="STATUS" data-statusid="' + Data.STATUSID + '" class="border padding">' + Data.STATUS + '</td>' : '') +
				'<td data-rel="ACTION" class="padding"></td>' +
				'</tr>'
			);

			var ActionTD = $(OCDLRSelf.QueueElt + '[data-rel="' + Data.GID + '"] > td[data-rel="ACTION"]');

			if (['add'].indexOf (View) > -1)
			{
				OCDLRSelf.ActionHide (ActionTD);
				if (Data.STATUSID == 1)
				{
					OCDLRSelf.ActionPause (ActionTD, View);
				}
				else if (Data.STATUSID == 3)
				{
					OCDLRSelf.ActionPlay (ActionTD, View);
				}

				var HideAllButton = OCDLRSelf.Queue + '> thead th[data-rel="ACTION"] > div.icon-close';
				if ($(HideAllButton).length == 0)
				{
					$(OCDLRSelf.Queue + '> thead th[data-rel="ACTION"]').append ('<div class="icon-close svg"></div>');
					$(HideAllButton).bind ('click', function ()
					{
						OCDLRSelf.HideAllFromQueue ($(this));
					});
				}
			}
			if (['completes', 'waitings', 'stopped', 'actives'].indexOf (View) > -1)
			{
				OCDLRSelf.ActionDelete (ActionTD, OCDLRSelf.BadgerStatus[Data.STATUSID], View);

				var RemoveAllButton = OCDLRSelf.Queue + '> thead th[data-rel="ACTION"] > div.icon-delete';
				if ($(RemoveAllButton).length == 0)
				{
					$(OCDLRSelf.Queue + '> thead th[data-rel="ACTION"]').append ('<div class="icon-delete svg"></div>');
					$(RemoveAllButton).bind ('click', function ()
					{
						OCDLRSelf.RemoveAllFromQueue ($(this), '', View);
					});
				}
			}
			if (View == 'all')
			{
				if (Data.STATUSID == 4)
				{
					OCDLRSelf.ActionDeleteCompletely (ActionTD);
				}
				else
				{
					OCDLRSelf.ActionDelete (ActionTD, OCDLRSelf.BadgerStatus[Data.STATUSID], View);

					if (Data.STATUSID == 1)
					{
						OCDLRSelf.ActionPause (ActionTD, View);
					}
					else if (Data.STATUSID == 3)
					{
						OCDLRSelf.ActionPlay (ActionTD, View);
					}
				}
			}
			if (View == 'actives')
			{
				OCDLRSelf.ActionPause (ActionTD, View);
			}
			if (View == 'stopped')
			{
				OCDLRSelf.ActionPlay (ActionTD, View);
			}
			if (View == 'removed')
			{
				OCDLRSelf.ActionDeleteCompletely (ActionTD);

				var CompletelyRemoveAllButton = OCDLRSelf.Queue + '> thead th[data-rel="ACTION"] > div.icon-delete';
				if ($(CompletelyRemoveAllButton).length == 0)
				{
					$(OCDLRSelf.Queue + '> thead th[data-rel="ACTION"]').append ('<div class="icon-delete svg"></div>');
					$(CompletelyRemoveAllButton).bind ('click', function ()
					{
						OCDLRSelf.RemoveAllFromQueue ($(this), 'completely', View);
					});
				}
			}
		},

		AddDownload: function (Button, TYPE, URL, OPTIONS)
		{
			var RESULT = false;

			if (!Button.hasClass ('icon-loading-small'))
			{
				Button.children ('a').css ('display', 'none');
				Button.addClass ('icon-loading-small');

				$.ajax ({
			        url: OC.generateUrl (OCDLRSelf.BaseURL + TYPE + 'downloader/add'),
			        method: 'POST',
					dataType: 'json',
					data: {'FILE' : URL, 'OPTIONS' : OPTIONS},
			        async: true,
			        cache: false,
			        timeout: 30000,
			        success: function (Data)
					{
			            if (Data.ERROR)
						{
							OCDLRSelf.PrintError (Data.MESSAGE);
						}
						else
						{
							OCDLRSelf.PrintInfo (Data.MESSAGE + ' (' + Data.GID + ')');
							OCDLRSelf.PrependToQueue (Data, 'add');

							$('#ball').Badger ('+1');
							if (Data.STATUSID == 1)
							{
								$('#bactives').Badger ('+1');
							}
							if (Data.STATUSID == 3)
							{
								$('#bwaitings').Badger ('+1');
							}

							RESULT = true;
						}

						// Reset add button
						Button.children ('a').css ('display', 'block');
						Button.removeClass ('icon-loading-small');
			        }
			    });
			}

			return RESULT;
		},

		ActionHide: function (TD)
		{
			TD.append ('<div class="icon-close svg"></div>');
			TD.children ('div.icon-close').bind ('click', function ()
			{
				OCDLRSelf.HideFromQueue ($(this));
			});
		},

		ActionPause: function (TD, View)
		{
			if (OCDLRSelf.WhichDownloader == 'ARIA2')
			{
				TD.append ('<div class="icon-pause svg"></div>');
				TD.children ('div.icon-pause').bind ('click', function ()
				{
					OCDLRSelf.PauseDownload ($(this), View);
				});
			}
		},

		ActionPlay: function (TD, View)
		{
			if (OCDLRSelf.WhichDownloader == 'ARIA2')
			{
				TD.append ('<div class="icon-play svg"></div>');
				TD.children ('div.icon-play').bind ('click', function ()
				{
					OCDLRSelf.UnPauseDownload ($(this), View);
				});
			}
		},

		ActionDelete: function (TD, Status, View)
		{
			TD.append ('<div class="icon-delete svg"></div>');
			TD.children ('div.icon-delete').bind ('click', function ()
			{
				$('#bremoved').Badger ('+1');
				$('#b' + Status).Badger ('-1');
				OCDLRSelf.RemoveFromQueue ($(this), '', View);
			});
		},

		ActionDeleteCompletely: function (TD)
		{
			TD.append ('<div class="icon-delete svg"></div>');
			TD.children ('div.icon-delete').bind ('click', function ()
			{
				$('#bremoved').Badger ('-1');
				$('#ball').Badger ('-1');
				OCDLRSelf.RemoveFromQueue ($(this), 'completely', 'removed');
			});
		}
	};

	var OCDLRSelf = OCDLR.Utils;

	OCDLRSelf.GetPersonalSettings ();
	OCDLRSelf.GetAdminSettings (['WhichDownloader']);
	OCDLRSelf.Queue = OCDLRSelf.BaseID + '#Queue ';
	OCDLRSelf.QueueHeader = OCDLRSelf.Queue + 'thead tr';
	OCDLRSelf.QueueElt = OCDLRSelf.Queue + 'tbody tr';
	OCDLRSelf.BadgerStatus = ['completes', 'actives', 'waitings', 'stopped'];
})();
