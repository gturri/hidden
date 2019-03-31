<?php
/**
 * Plugin hidden: Enable to hide details
 * v2.4
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Guillaume Turri <guillaume.turri@gmail.com>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/parserutils.php');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_hidden extends DokuWiki_Syntax_Plugin {

  function getType(){ return 'container'; }
  function getPType(){ return 'stack'; }
  function getAllowedTypes() { 
    return array('container', 'baseonly', 'substition','protected','disabled','formatting','paragraphs');
  }
  function getSort(){
    //make sure it's greater than hiddenSwitch plugin's one in order to avoid a confusion between "<hidden.*" and "<hiddenSwitch.*"
    return 189;
     }

  // override default accepts() method to allow nesting
  // - ie, to get the plugin accepts its own entry syntax
  function accepts($mode) {
    if ($mode == substr(get_class($this), 7)) return true;
    return parent::accepts($mode);
    }

  function connectTo($mode) {
    $this->Lexer->addEntryPattern('<hidden\b.*?>(?=.*?</hidden>)', $mode,'plugin_hidden');
    $this->Lexer->addSpecialPattern('<hiddenSwitch[^>]*>', $mode,'plugin_hidden');
     }
  function postConnect() {
    $this->Lexer->addExitPattern('</hidden>','plugin_hidden');
  }

  function handle($match, $state, $pos, Doku_Handler $handler) {
    switch ($state) {
      case DOKU_LEXER_SPECIAL:
        //hiddenSwitch
        $return = array('text' => $this->getLang('switch.default'), 'type' => 'switch');
        $match = trim(utf8_substr($match, 14, -1)); //14 = strlen("<hiddenSwitch ")
        if ( $match !== '' ){
          $return['text'] = $match;
        }
        $return['text'] = htmlspecialchars($return['text']);
        return $return;

      case DOKU_LEXER_ENTER :
          $return = array(
                'active' => 'true',
                'element'=>Array(),
                'onHidden'=>'',
                'onVisible'=>'',
                'initialState'=>'hidden',
                'state'=>$state,
                'printHead' => true,
                'bytepos_start' => $pos,
                'edit' => false,
                'editText' => $this->getLang('edit'),
                'onExportPdf' => ''
              );
           $match = substr($match, 7, -1); //7 = strlen("<hidden")

        //Looking for the initial state
        preg_match("/initialState *= *\"([^\"]*)\" ?/i", $match, $initialState);
        if ( count($initialState) != 0) {
          $match = str_replace($initialState[0], '', $match);
          $initialState = strtolower(trim($initialState[1]));
          if ( $initialState == 'visible'
            || $initialState == 'true'
            || $initialState == 'expand' ) {
            $return['initialState'] = 'visible';
          }
        }

        //Looking for the -noPrint option
        if ( preg_match('/-noprint/i', $match, $found) ){
            $return['printHead'] = false;
            $match = str_replace($found[0], '', $match);
        }

        //Looking for the -editable option
        if ( preg_match('/-edit(able)?( *= *"([^"]*)")?/i', $match, $found) ){
            if ( count($found) > 1 ){
                $return['editText'] = end($found);
            }
            $return['edit'] = true;
            $match = str_replace($found[0], '', $match);
        }

           //Looking if this block is active
           preg_match("/active *= *\"([^\"]*)\" ?/i", $match, $active);
           if( count($active) != 0 ){
             $match = str_replace($active[0], '', $match);
             $active = strtolower(trim($active[1]));
             if($active=='false' || $active=='f' || $active=='0' || $active=='n'){
               $return['active'] = false;
             }
           }

           //Looking for the element(s) of the block (ie: which switches may activate this element)
           preg_match("/element *= *\"([^\"]*)\" ?/i", $match, $element);
           if( count($element) != 0 ){
             $match = str_replace($element[0], '', $match);
             $element[1] = htmlspecialchars($element[1]);
             $return['element'] = explode(' ', $element[1]);
           }

        //Looking for the texts to display
        $this->_grepOption($return, 'onHidden', $match);
        $this->_grepOption($return, 'onVisible', $match);
        $this->_grepOption($return, 'onExportPdf', $match);

        //If there were neither onHidden nor onVisible, take what's left
        if( $return['onHidden']=='' && $return['onVisible']=='' ){
             $text = trim($match);
             if($text != ''){
               $return['onHidden'] = $text;
               $return['onVisible'] = $text;
             } else { //if there's nothing left, take the default texts
               $return['onHidden'] = $this->getConf('default_text_when_hidden');
               $return['onVisible'] = $this->getConf('default_text_when_displayed');
             }
        } else { //if one string is specified but not the other, take the defaul text
          $return['onHidden'] = ($return['onHidden']!='') ? $return['onHidden'] : $this->getConf('default_text_when_hidden');
          $return['onVisible'] = ($return['onVisible']!='') ? $return['onVisible'] : $this->getConf('default_text_when_displayed');
        }

        //If we don't have an exportPdf text, take the onVisible one, since the block will be exported unfolded
        if ( $return['onExportPdf'] == '' ){
          $return['onExportPdf'] = $return['onVisible'];
        }

        return $return;

      case DOKU_LEXER_UNMATCHED :
        return array('state'=>$state, 'text'=>$match);

      default:
        return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
      }
  } // handle()

  private function _grepOption(&$options, $tag, &$match){
    preg_match("/$tag *= *\"([^\"]*)\" ?/i", $match, $text);
    if ( count($text) != 0 ){
      $match = str_replace($text[0], '', $match);
      $options[$tag] = $text[1];
    }
  }

  function render($mode, Doku_Renderer $renderer, $data) {
    if ( $this->_exportingPDF() ){
      $data['onVisible'] = $data['onExportPdf'];
    }

    if($mode == 'xhtml' && $data['type'] == 'switch') {
      $renderer->doc .= '<button class="button hiddenSwitch">'.$data['text'].'</button>';
      return true;
    }
    if($mode == 'xhtml'){
      switch ($data['state']) {
        case DOKU_LEXER_ENTER :
           $this->editableBlocks[] = $data['edit'];
           $classEdit = ($data['edit'] ? $renderer->startSectionEdit($data['bytepos_start'], 'section', $data['editText']) : '');
           $tab = array();
           $onVisible = p_render('xhtml', p_get_instructions($data['onVisible']), $tab);
           $onHidden = p_render('xhtml', p_get_instructions($data['onHidden']), $tab);

          // "\n" are inside tags to avoid whitespaces in the DOM with FF
          $renderer->doc .= '<div class="hiddenGlobal '.$classEdit;
          $renderer->doc .= $data['active'] ? ' hiddenActive' : '';
          $renderer->doc .= '">';



          $renderer->doc .= '<div class="hiddenElements">';
          foreach($data['element'] as $element){
            $renderer->doc .= ' '.$element;
          }
          $renderer->doc .= "</div>";

          $renderer->doc .= '<div class="hiddenHead ';
          $renderer->doc .= $data['printHead'] ? '' : 'hiddenNoPrint';
          $renderer->doc .= ($data['initialState'] == 'hidden') ? ' hiddenSinceBeginning' : '';
          $renderer->doc .= '">';
          $renderer->doc .=   '<div class="hiddenOnHidden">'.$onHidden."</div>"; //text displayed when hidden
          $renderer->doc .=   '<div class="hiddenOnVisible">'.$onVisible."</div>"; //text displayed when expanded
          $renderer->doc .= '</div> <!-- .hiddenHead -->';

          $renderer->doc .= '<div class="hiddenBody">';
          break;

        case DOKU_LEXER_UNMATCHED :
          $text = $renderer->_xmlEntities($data['text']);
          if (  preg_match("/^[ \t]*={2,}[^\n]+={2,}[ \t]*$/", $text, $match) ){
            $title = trim($match[0]);
             $level = 7 - strspn($title,'=');
             if($level < 1) $level = 1;
             $title = trim($title,'=');
             $title = trim($title);
            $renderer->header($title, $level, 0);
          } else {
            $renderer->doc .= $text;
          }
          break;

        case DOKU_LEXER_EXIT :
          $renderer->doc .= "</div></div>"; //close hiddenBody and hiddenGlobal
          if ( array_pop($this->editableBlocks) ){
              $renderer->finishSectionEdit($data['bytepos_end']);
          }
          break;
      }
      return true;
    }
    
    if ($mode == 'odt') {
      if ($data['state'] == DOKU_LEXER_UNMATCHED && $data['type'] != 'switch') {
        $renderer->doc .= $renderer->_xmlEntities($data['text']);
      }
      return true;
    }

    return false;
  } // render()

  private function _exportingPDF(){
    global $ACT;
    return ($ACT == 'export_pdf' || $ACT == 'export_pdfbook' || $ACT == 'export_odt');
  }

  var $editableBlocks = array();

} // class syntax_plugin_nspages
