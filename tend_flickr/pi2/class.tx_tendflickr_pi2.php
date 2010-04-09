<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once( dirname(__file__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."class.tx_tendflickr.php");

class tx_tendflickr_pi2 extends tx_tendflickr_pi1 {
    public $prefixId      = 'tx_tendflickr_pi2';
    public $scriptRelPath = 'pi2/class.tx_tendflickr_pi2.php';
    public $extKey        = 'tend_flickr';
    public $pi_checkCHash = true;
    public $conf_ts       = array();
    public $smarty        = false; // Smarty object
    public $flickr        = false; // Flickr API

    private function preSetDefault() {
        $this->prefixId      = 'tx_tendflickr_pi2';
        $this->scriptRelPath = 'pi2/class.tx_tendflickr_pi2.php';
    }

    public function main($content,$conf) {
        $this->preSetDefault();

        parent::main($content,$conf);

        $this->preSetDefault();
        $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi2/locallang.xml');

        return $this->displayUploadForm();
    }

    /* Display upload form */
    private function displayUploadForm() {

        $this->smarty->assign("typo3_form",
                array("action"=>$this->pi_getPageLink($GLOBALS['TSFE']->id), "name"=>$this->prefixId));

        $error = false;
        $field_req = explode(",","title,description,author,pid");
        $data = array();
        foreach($field_req as $key) {
            if(trim($this->piVars[$key])!="") {
                $data[$key] = trim($this->piVars[$key]);
            } else
                $error = true;
        }

        $file_name = "";
        if($error==false){
            $ff = t3lib_div::makeInstance('t3lib_basicFileFunctions');
            $name = $_FILES["tx_tendflickr_pi2"]["name"]["photo"];

            $name = $ff->getUniqueName($name,"uploads/tx_tendflickr");
            $file_name = basename($name);
            if(!move_uploaded_file($_FILES["tx_tendflickr_pi2"]["tmp_name"]["photo"], $name))
                $error = true;
        };

        if($error==false) {
             $data = array_merge($data,array("crdate"=>time(),"tstamp"=>time(),"photo"=>$file_name,
                 "flickr_mail"=>$this->conf_ts["flickr."]["email"],
                 "from_mail"=>$this->conf_ts["flickr."]["from_mail"],
                 ));
             $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_tendflickr_photo',$data);
        }

        if($error==false)   $this->smarty->assign("done",true);

        $this->smarty->assign("upl_use_css","1"); // For TS
        $this->smarty->assign("pid",($this->conf_ts["flickr."]["goto_pid"]));
        $this->smarty->assign("data",$this->piVars);

    //    $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi2/locallang.xml');

        return $this->smarty->display("flickr_upload.xhtml");
    }

};

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi2/class.tx_tendflickr_pi2.php']);