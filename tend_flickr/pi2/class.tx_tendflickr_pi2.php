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

    private function preSetDefault(){
        $this->prefixId      = 'tx_tendflickr_pi2';
        $this->scriptRelPath = 'pi2/class.tx_tendflickr_pi2.php';
    }

    public function main($content,$conf) {
        $this->preSetDefault();
        parent::main($content,$conf);
        $this->preSetDefault();

        $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi1/locallang.xml');

        return $this->displayUploadForm();
    }

    /* Display upload form */
    private function displayUploadForm(){
        $this->smarty->assign("typo3_form",
                array("action"=>$this->pi_getPageLink($GLOBALS['TSFE']->id), "name"=>$this->prefixId));
        $this->smarty->assign("upl_use_css","1"); // For TS
        
        return $this->smarty->display("flickr_upload.xhtml");
    }

};

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php']);