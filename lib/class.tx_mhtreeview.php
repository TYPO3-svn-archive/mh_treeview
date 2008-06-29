<?PHP
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Martin Hesse <mail@martin-hesse.info>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_mhtreeview extends tslib_pibase {
  
  var $prefixId        = 'tx_mhtreeview';		// Same as class name
	var $scriptRelPath   = 'lib/class.tx_mhtreeview.php';	// Path to this script relative to the extension dir.
	var $extKey          = 'mh_treeview';	// The extension key.
	
  var $expandAll       = 0;
  var $expandFirst     = 1;
  var $content         = Array('uid','title');  // Database fields for uid and title
  var $conf            = FALSE;  // Conf-Array, see manual for more
  var $table; // Define the database table
  var $parent_field;  // Name of the field which contain the parent id
  var $title; // Title of the tree
  var $template;
  
  
  
  /**
	 * Init
	 *
	 * @param	string   $table: database table
	 * @param	string   $parent_field: name of the field which contain the parent id
	 * @param string   $title: title of the tree
	 * @param array    $content: database fields for uid and title   
	 * @param array    $conf: configuration array	 
	 * @param boolean  $expandAll
	 * @param boolean  $expandFirst    
	 *   	 
	 * @return	nothing
	 */ 
  function init($table,$parent_field,$title,$content = array('uid','title'), $conf = FALSE, $expandAll = 0,$expandFirst = 1) {
    $this->table = $table;
    $this->parent_field = $parent_field;
    $this->title = $title;
    $this->content = $content;
    $this->expandAll = $expandAll;
    $this->expandFirst = $expandFirst;
    $this->conf = $conf;
    
    #$this->template = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey). 'res/template.html');
  } // End function: init();
  
  
  
  /**
	 * Calls the method getElements() and create the tree
	 * 
	 *   	 
	 * @return	nothing
	 */ 
  function getTree() {
  
    #$template   = $this->cObj->getSubpart($this->template,"###TREEVIEW###");
    
    $headerData = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey). 'res/stylesheet.css" />';
    $headerData .= '<script type="text/javascript" language="JavaScript" src="' . t3lib_extMgm::siteRelPath($this->extKey). 'res/tx_mhtreeview_functions.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $headerData;
    
    $content = $this->getElements();
    
    return $this->pi_wrapInBaseClass($content);
  } // End function: getTree();
  
  
  
	/**
	 * Get the elements of a tree
	 *
	 * @param	int  $parent_id: parent id
	 * @param	int  $parent_count: level count 
	 *   	 
	 * @return The elements of a tree
	 */ 
  function getElements($parent_id = 0, $parent_count = 0) {
   
    $content  = ''; #init
    $icon     = FALSE;
    
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*',
      $this->table,
      $this->parent_field . ' = ' . intval($parent_id) . ' AND deleted = 0 AND hidden = 0 ' . $this->conf['select_where'],
      '',
      '',
      ''
    );
    
    $block = ''; #init
    
    if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {

      $contentTitle = ''; #init
      if($parent_count == 0) {
        if($this->expandFirst == 1) {
          $content  .= '<ul id="tx_mhtreeview-lvl' . $parent_count . '" class="tx_mhtreeview-node">';
          $icon     = 'minus';
        } else {
          $content  .= '<ul style="display:none;" id="tx_mhtreeview-lvl' . $parent_count . '" class="tx_mhtreeview-node">';
          $icon     = 'plus';
        }
        $contentTitle .= '<p class="tx_mhtreeview_title"><img class="tx_mhtreeview_toggleImg" id="tx_mhtreeview_toggleImg' . $parent_count . '" src="' . t3lib_extMgm::siteRelPath('mh_treeview') . 'res/' . $icon . '.gif" onclick="tx_mhtreeview_toggle(' . $parent_count . ');" />&nbsp;';
        $contentTitle .= '<a href="javascript:tx_mhtreeview_toggle(' . $parent_count . ');" class="tx_mhtreeview_title">';
        $contentTitle .= $this->title;
        $contentTitle .= '</a></p>';
      } else {
        if($this->expandAll > 0) {
          $content  .= '<ul id="tx_mhtreeview-lvl' . $parent_count . '" class="tx_mhtreeview-node">';
        } else {
          $content  .= '<ul style="display:none;" id="tx_mhtreeview-lvl' . $parent_count . '" class="tx_mhtreeview-node">';
        }
      }

      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

        if($this->conf['JS_Func'] != '') {
          $temp_js_input = explode(',',$this->conf['JS_Input']);
          if($this->conf['JS_Event'] == 'href') {
            $temp_js  = $this->conf['JS_Event'] . '="javascript:'; 
          } else {
            $temp_js  = $this->conf['JS_Event'] . '="';
          }
          $tempJsInput = array();
          foreach($temp_js_input AS $jsInput) {
            if($row[$jsInput]) {
              $tempJsInput[] = '\'' . $row[$jsInput] . '\'';
            } else {
              $tempJsInput[] = '\'' . $jsInput . '\'';
            }
          }
          $temp_js  .= $this->conf['JS_Func'] . '(' . implode(',',$tempJsInput) . ');"';
        } else {
          $temp_js  = '';
        }
        
        if($this->conf['active_id'] && in_array($row['uid'], $this->conf['active_id'])) {
          $className = 'class="tx_mhtreeview_act"';
        } else {
          $className = 'class="tx_mhtreeview_no"';
        }
                 
        if($this->isSub($row[$this->content[0]])) { 
          $icon     = $this->expandAll == 1 ? 'minus' : 'plus';
          $parent_count++;

          if($this->conf['dontLinkMainNode'] == 1) {
            $temp_js2 = 'href="javascript:tx_mhtreeview_toggle(' . $parent_count . ');"';
          } else {
            $temp_js2 = $temp_js;
          }
          
          $content  .= '<li id="tx_mhtreeview_' . $row[$this->content[0]] . '">';
          $content  .= '<img class="tx_mhtreeview_toggleImg" id="tx_mhtreeview_toggleImg' . $parent_count . '" src="' . t3lib_extMgm::siteRelPath('mh_treeview') . 'res/' . $icon . '.gif" onclick="tx_mhtreeview_toggle(' . $parent_count . ');" />&nbsp;';
          $content  .= '<a ' . $className . ' id="tx_mhtreeview-node_' . $row[$this->content[0]] . '" ' . $temp_js2 . '>';
          $content  .= $row[$this->content[1]];
          $content  .= '</a>';
          $content  .= $this->getElements($row[$this->content[0]], $parent_count);
          $content  .= '</li>';
        } else {
          $content  .= '<li id="tx_mhtreeview_' . $row[$this->content[0]] . '">';
          $content  .= '<img class="tx_mhtreeview_PageImg" src="' . t3lib_extMgm::siteRelPath('mh_treeview') . 'res/page.gif" />&nbsp;';
          $content  .= '<a ' . $className . '  id="tx_mhtreeview-node_' . $row[$this->content[0]] . '" ' . $temp_js . '>';
          $content  .= $row[$this->content[1]];
          $content  .= '</a></li>';
        }
      }
      $content .= '</ul>';
      return $contentTitle.$content;
    }
    return false;
  } // End function: getElements();
  
  
  
  /**
	 * Check if a parent element is available
	 *
	 * @param	string   $parent_id: parent id         
	 *   	 
	 * @return	true false
	 */ 
  function isSub($parent_id) {
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      'uid',
      $this->table,
      $this->parent_field . ' = ' . intval($parent_id) . ' AND deleted = 0 AND hidden = 0',
      '',
      '',
      ''
    );
    
    return $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0 ? TRUE : FALSE;
  } // End function: isSub();
  
  
  
} // End class: tx_mhtreeview

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_treeview/lib/class.tx_mhtreeview.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mh_treeview/lib/class.tx_mhtreeview.php']);
}
?>
