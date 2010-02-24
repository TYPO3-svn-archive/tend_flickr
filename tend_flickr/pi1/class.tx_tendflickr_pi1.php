<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once( dirname(__file__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."class.tx_tendflickr.php");

class tx_tendflickr_pi1 extends tslib_pibase {
    var $prefixId      = 'tx_tendflickr_pi1';
    var $scriptRelPath = 'pi1/class.tx_tendflickr_pi1.php';
    var $extKey        = 'tend_flickr';
    var $pi_checkCHash = true;
    var $conf_ts       = array();

    function init($content, $conf) {
        $this->conf_ts = $conf;
    }

    /* main */
    function main($content, $conf) {
        $this->conf_ts = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();


        var_dump("x");

        return "xxx";
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php']);

