/**
 * script.js for Plugin hidden
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Guillaume Turri <guillaume.turri@gmail.com>
 *
 */
jQuery(function(){
	jQuery("div.hiddenSinceBeginning").parent().addClass('hiddenHidden');
	jQuery('div.hiddenActive>div.hiddenHead').on('click', function(){
			jQuery(this).parent().toggleClass('hiddenHidden');
		});
	jQuery(".hiddenSwitch").on('click', function() {
			var weShallHide = (jQuery('div.hiddenGlobal.hiddenHidden').length == 0);
			if(weShallHide) {
				jQuery('.hiddenActive').addClass('hiddenHidden');
			} else {
				jQuery('.hiddenActive').removeClass('hiddenHidden');
			}
		});
});
