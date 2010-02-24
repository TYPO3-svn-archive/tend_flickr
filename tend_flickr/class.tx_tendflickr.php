<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

$dir = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR,dirname(__file__)));
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

class tx_tendflickr {
    //put your code here
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php'])
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php']);
