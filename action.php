<?php
/**
 * action.php for Plugin hidden
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Guillaume Turri <guillaume.turri@gmail.com>
 */

if(!defined('DOKU_INC')) die();
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_hidden extends Dokuwiki_Action_Plugin {
  function register(&$controller) {
    $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
  }

  /**
   * Inserts a toolbar button
   */
  function insert_button(&$event, $param) {
    $event->data[] = array (
        'type' => 'format',
        'title' => $this->getLang('button'),
        'icon' => '../../plugins/hidden/images/hidden.png',
        'open' => '<hidden>',
        'close' => '</hidden>',
        );
  }
}

