<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Edermayr Ronald <ed@bluechip.at>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   45: class tx_edpixlr_cm1_editicons implements fileList_editIconHook
 *   53:     public function manipulateEditIcons(&$cells, &$parentObject)
 *
 *
 *   77: class tx_edpixlr_cm1
 *   79:     function main(&$backRef,$menuItems,$table,$uid)
 *  138:     function includeLL()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_versionhelper.php');

if (tx_edpixlr_versionhelper::getTypo3Version() >= 4003000) {
	if (tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
		require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_6.0_interface_helper.php');
	} else {
		require_once(PATH_site.'typo3/interfaces/interface.filelist_editiconshook.php');
	}

    class tx_edpixlr_cm1_editicons implements fileList_editIconHook {
    
        var $extKey = 'ed_pixlr';
    	/**
		 * modifies edit icon array
		 *
		 * @param	array		array of edit icons
		 * @param	fileList		parent object
		 * @return	void
		 */
    	public function manipulateEditIcons(&$cells, &$parentObject) {
    	   global $BE_USER;
    	   //info] => <a href="#" onclick="top.launchView( '_FILE', '1:/_migrated/pics/float.jpg');return false;" title="Info"><span class="t3-icon t3-icon-status t3-icon-status-dialog t3-icon-dialog-information">&nbsp;</span></a>
    		// If it is a valid file to edit (no empty files) ...
    		if ($BE_USER->userTS['tx_edpixlr'] == 1 && $cells['info'] && tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
				if(preg_match("/launchView\(\s*'_FILE'\s*,\s*'([^']*?)'/ms", $cells['info'], $matches)) {
					$icon = $this->getIcon();
					$file = pathinfo($matches[1]);
					$action = $this->getPixlrAction(array(
						'ext' => $file['extension'],
						'id' => $matches[1]
					));
				}
			} elseif($BE_USER->userTS['tx_edpixlr'] == 1 && $parentObject->totalbytes) {
    			$keys = array_keys($parentObject->files['sorting']);
    			$file = $parentObject->files['files'][$keys[$parentObject->eCounter-$parentObject->dirCounter]];
    			$action = $this->getPixlrAction(array(
					'ext' => $file['fileext'],
					'id' => $file['path'].$file['file']
    			));
    		}
    		if($action) {
				$cells['ed_pixlr_edit'] = $action;
			}
    	}
    	
    	protected function getPixlrAction($file) {
			$icon = $this->getIcon();
			if(in_array(strtolower($file['ext']), array('gif','jpg','jpeg','png'))) {
				$editOnClick = 'top.content.list_frame.location.href=top.TS.PATH_typo3+\''.t3lib_extMgm::extRelPath('ed_pixlr').'cm1/index.php?file='.$file['id'].'\'';
				return '<a href="#" onclick="' . $editOnClick . '">'.$icon.'</a>';
			}
			return false;
		}
    	
    	protected function getIcon() {
			if (tx_edpixlr_versionhelper::getTypo3Version() >= 4004000) {
				$icon = t3lib_iconWorks::getSpriteIcon('extensions-'.$this->extKey.'-clickmenu');
			} else {
				$icon = '<img src="' . t3lib_extMgm::extRelPath('ed_pixlr').'cm1/cm_icon.gif" width="15" height="12" title="' . $GLOBALS['LANG']->sL('LLL:EXT:ed_pixlr/locallang.xml:cm1_title') . '" alt="" />';
			}
			return $icon;
		}
    }
}


/**
 * Addition of an item to the clickmenu
 *
 * @author	Edermayr Ronald <ed@bluechip.at>
 * @package	TYPO3
 * @subpackage	tx_edpixlr
 */
class tx_edpixlr_cm1 {
    var $extKey = 'ed_pixlr';

	function main(&$backRef,$menuItems,$table,$uid)	{
		global $BE_USER,$TCA,$LANG,$userTS;
		
		if (strlen($table) > 0 && !empty($uid)) {
		  return $menuItems;
		}
		
		if (!$backRef->cmLevel)	{

			/*
			Getting Informations about the File we selected.
			*/
			if (tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
				require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_60_helper.php');
				$fileinformation = tx_edpixlr_60_helper::getFileInformation($backRef->iParts[0]);
			} else {
				$fileinformation = t3lib_basicFileFunctions::getTotalFileInfo($backRef->iParts[0]);
			}
			$folderinformation = t3lib_basicFileFunctions::getTotalFileInfo($fileinformation['path']);

			/*
			Is the clicked File a registered Filetype in $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']?
			*/
			if ( $BE_USER->userTS['tx_edpixlr'] == 1 && $folderinformation['writeable'] == false && $fileinformation['readable']== false && $fileinformation['type']=="file" && in_array($fileinformation['fileext'], t3lib_div::trimExplode(',',$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])))	{

				$notFilelist = (substr($backRef->iParts[3],0,1)=='+');

				$localItems = Array();
				$LL = $this->includeLL();
				$url = t3lib_extMgm::extRelPath('ed_pixlr').'cm1/index.php?file='.t3lib_div::rawUrlEncodeFP($backRef->iParts[0]);

				if($notFilelist) {
					$js = file_get_contents(t3lib_extMgm::extPath('ed_pixlr').'cm1/lib/pixlr.js');
					$url = $js.'pixlr.overlay.show(\''.$url.'&overlay=1\');';
				} else {
					$url = $backRef->urlRefForCM($url);
				}
				
				if (tx_edpixlr_versionhelper::getTypo3Version() >= 4004000) {
    				$icon = t3lib_iconWorks::getSpriteIcon('extensions-'.$this->extKey.'-clickmenu');
    			} else {
    				$icon = '<img src="' . t3lib_extMgm::extRelPath('ed_pixlr').'cm1/cm_icon.gif" width="15" height="12" title="' . $GLOBALS['LANG']->sL('LLL:EXT:ed_pixlr/locallang.xml:cm1_title') . '" alt="" />';
                }

				$localItems['pixlr'] = $backRef->linkItem(
					$GLOBALS['LANG']->getLLL('cm1_title',$LL),
					$backRef->excludeIcon($icon),
					$url,
					1	// Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
				);

				if($notFilelist){
					$backRef->iParts[3] = $backRef->iParts[3].',pixlr';
					$menuItems['pixlr'] = $localItems['pixlr'];
				} else {
					reset($menuItems);
					$c=0;
					while(list($k)=each($menuItems)) {
						$c++;
						if (!strcmp($k,'delete'))	break;
					}
					$c-=2;
					array_splice($menuItems, $c, 0, $localItems);
				}
			}
		}
		return $menuItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return	[type]		...
	 */
	function includeLL()	{
		return $GLOBALS['LANG']->includeLLFile('EXT:ed_pixlr/locallang.xml', false);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/class.tx_edpixlr_cm1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/class.tx_edpixlr_cm1.php']);
}
?>
