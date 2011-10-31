/**
 * script.js for Plugin hidden
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Guillaume Turri <guillaume.turri@gmail.com>
 *
 */


/**
 * @param elem The structure to display/hide
 * @param forceHide FALSE to flip, TRUE to hide unconditionnaly
 */
function plugin_hidden_flip(elem, forceHide){
  var parent = elem.parentNode;
  var onHiddenText = parent.childNodes[0].innerHTML;
  var onVisibleText = parent.childNodes[1].innerHTML;
  var head = parent.childNodes[3];
  var body = parent.childNodes[4];

  if (body.style.display !== "none" || forceHide){
    jQuery(body).hide();
    head.innerHTML = onHiddenText;
  } else {
    jQuery(body).show();
    head.innerHTML = onVisibleText;
  }
}

/**
 * The plugin will hide text only if javascript is enabled (else, it wouldn't be possible to display it)
 */
function installPluginHiddenJS(){
  jQuery(".hiddenActive").each(function(){
    jQuery(this).click(function(){plugin_hidden_flip(this, false)});
  });

  jQuery(".hiddenSinceBeginning").each(function(){
    plugin_hidden_flip(this, true);
  });

  jQuery(".hiddenSwitch").each(function(){
      jQuery(this).click(function(){plugin_hidden_flipAll()});
  });
}

/**
 * Check if at least one element is currently hidden
 */
function plugin_hidden_atLeastOneHidden(){
    var result = false;
    jQuery(".hiddenActive").each(function(){
        if ( plugin_hidden_isHidden(this) ){
            result = true;
        }
    });

    return result;
}

/**
 * Check if a given element is hidden
 */
function plugin_hidden_isHidden(elem){
    return elem.parentNode.childNodes[4].style.display === "none";
}

/**
 * Swap the state of every element
 */
function plugin_hidden_flipAll(){
    var weShallDisplay = plugin_hidden_atLeastOneHidden();
    jQuery(".hiddenActive").each(function(){
        if ( weShallDisplay == plugin_hidden_isHidden(this) ){
            plugin_hidden_flip(this, false);
        }
    });
}

jQuery(function(){installPluginHiddenJS();});
