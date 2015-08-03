/**
 * script.js for Plugin hidden
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Guillaume Turri <guillaume.turri@gmail.com>
 *
 */
jQuery(function(){
	jQuery(".hiddenSinceBeginning").parent().addClass('hiddenHidden');
	jQuery(".hiddenHead").on('click', function(){
			jQuery(this).parent().toggleClass('hiddenHidden');
		});
});
