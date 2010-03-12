<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

require_once (t3lib_extMgm::extPath ("tend_flickr")."class.tx_tendflickr.php");

class tx_tendflickr_displayhook extends tslib_pibase{

    public function preDisplayPhotostreamHookProcessor($smarty,$obj){
        // Implement here...
    }
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_flickr/class.tx_tendflickr_displayhook.php"])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tend_flickr/class.tx_tendflickr_displayhook.php"]);