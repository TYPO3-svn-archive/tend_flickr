<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once( dirname(__file__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."class.tx_tendflickr.php");
# require_once( dirname(__file__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."pi1".DIRECTORY_SEPARATOR."class.tx_tendflickr.php");


class tx_tendflickr_pi2 extends tx_tendflickr_pi1 {
    public $prefixId      = 'tx_tendflickr_pi2';
    public $scriptRelPath = 'pi2/class.tx_tendflickr_pi2.php';
    public $extKey        = 'tend_flickr';
    public $pi_checkCHash = true;
    public $conf_ts       = array();
    public $smarty        = false; // Smarty object
    public $flickr        = false; // Flickr API

    public function init() {
        parent::init();
    }

    public function main($content,$conf){
        parent::main($content,$conf);
        $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi1/locallang.xml');

    }
    
};

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php']);
