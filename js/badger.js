/**
 * ownCloud - ocDownloader
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE file.
 *
 * @author Xavier Beurois <www.sgc-univ.net>
 * @copyright Xavier Beurois 2015
 */
(function ($)
{
	$.fn.Badger = function (Badge, Callback)
	{
  		var BadgerExists = this.find ('.badger-outter').html ();
  		Badge = Badge.toString();
		
  		// Clear the badge
  		if (!Badge)
  		{
  			if (BadgerExists)
  			{
				this.find ('.badger-outter').remove ();
			}
  		}
  		else
  		{
			// Figuring out badge data
			var OldBadge = this.find ('.badger-badge').text ();
			if (Badge.charAt (0) == '+')
			{
				if (isNaN (Badge.substr (1)))
				{
					Badge = OldBadge + Badge.substr (1);
				}
				else
				{
					Badge = Math.round (Number (OldBadge) + Number (Badge.substr (1)));
				}
			}
			else if (Badge.charAt (0) == '-')
			{ 
				if (isNaN (Badge.substr (1)))
				{
					Badge = OldBadge - Badge.substr (1);
				}
				else
				{
					Badge = Math.round (Number (OldBadge) - Number (Badge.substr (1)));
				}
			}
				
			// Don't add duplicates
			if (BadgerExists)
			{
				this.find ('.badger-badge').html (Badge);
			}
			else
			{
				this.append ('<div class="badger-outter"><div class="badger-inner"><p class="badger-badge badger-number">' + Badge + '</p></div></div>');
			}
				
			// Badger text or number class
			if (isNaN (Badge))
			{
				this.find ('.badger-badge').removeClass ('badger-number').addClass ('badger-text');
			}
			else
			{
				this.find ('.badger-badge').removeClass ('badger-text').addClass ('badger-number');
			}
			
			// Send back badge
			if (Callback)
			{
				callback (Badge);
			}
		}
	};
})(jQuery);