<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once("class.tx_tendflickr.php");


class tx_tendflickr_eid extends tslib_pibase {
    var $prefixId      = 'tx_tendflickr_eid';
    var $scriptRelPath = 'class.tx_tendflickr_eid.php';
    var $extKey        = 'tend_flickr';

    function eid_main() {
        $GLOBALS['TSFE']->fe_user = tslib_eidtools::initFeUser();
        tslib_eidtools::connectDB();

        switch($_GET["action"]){
          case "":
              

              break;
        };
    }
}

$tend_flickr_eid = t3lib_div::makeInstance('tx_tendflickr_eid');
$tend_flickr_eid->eid_main();

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr_eid.php'])
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr_eid.php']);
