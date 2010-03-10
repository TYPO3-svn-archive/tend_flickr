<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

class tx_tendflickr_tooltip {
	function main(&$backRef,$menuItems,$table,$uid)	{
		global $BE_USER,$TCA,$LANG;

		$localItems = Array();
		if(!$backRef->cmLevel){
			if($backRef->editOK){
                                $url = t3lib_extMgm::extRelPath('tend_flickr')."tools/index.php?id=".$uid;
				$localItems[] = $backRef->linkItem(
					"Upload to Flickr",
                                        $backRef->excludeIcon('
                                            <img src="'.t3lib_extMgm::extRelPath("tend_flickr").'ext_icon.gif" width="16" height="18" border="0" align="top" />'),
					$backRef->urlRefForCM($url),
					0
				);
				$menuItems=array_merge($localItems,$menuItems);
			}
		}
		return $menuItems;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr_tooltip.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tx_tendflickr_tooltip.php']);
