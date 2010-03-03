<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once( dirname(__file__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."class.tx_tendflickr.php");

class tx_tendflickr_pi1 extends tslib_pibase {
    public $prefixId      = 'tx_tendflickr_pi1';
    public $scriptRelPath = 'pi1/class.tx_tendflickr_pi1.php';
    public $extKey        = 'tend_flickr';
    public $pi_checkCHash = true;
    public $conf_ts       = array();
    private $smarty        = false; // Smarty object
    private $flickr        = false; // Flickr API

    /* Main function */
    public function main($content, $conf) {
        $this->conf_ts = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        $this->smarty = tx_smarty::smarty();
        $this->smarty->template_dir = t3lib_div::getFileAbsFileName('EXT:tend_flickr/res/templates');
        $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi1/locallang.xml');
        $this->smarty->register_function("flickr_image","tx_tendflickr::smarty_flickr_image");

        /* Missing API key */
        if(empty($this->conf_ts["flickr."]["api_key"]))
            return $this->smarty->display("flickr_tserror.xhtml");

        /* News tx_tendflickr singlton instance */
        $this->flickr = tx_tendflickr::getInstance();

        $this->flickr->setConfig($this->conf_ts["flickr."]["api_key"],
                $this->conf_ts["flickr."]["api_username"],
                $this->conf_ts["flickr."]["api_password"]);

        // Cache time
        $this->flickr->setCacheTime(empty($this->conf_ts["flickr."]["api_cache"])?0:intval($this->conf_ts["flickr."]["api_cache"]));

        $display = trim($this->conf_ts["show."]["display"]);

        $views = array(
                array("name"=>"photostream"),  // Display user photo stream
                array("name"=>"photossearch"), // Search by keyword
                array("name"=>"generic"),      // Generic display
        );

        $display_s = false;
        foreach($views as $view) if($view["name"] == $display) {
                $display_s = $view;
                break;
            }
        $display = $display_s!=false?$display_s:$display;

        if(!$display_s) { // If no method is set
            $this->smarty->assign("display",$display);
            return $this->smarty->display("flickr_nodisplay.xhtml");
        }

        /* Method invoke test*/
        $d = call_user_func(array($this, sprintf("%s%s","view",$display["name"])));
        return $d;
    }

    /* Overload of functions */
    public function __call($method,$params) {
        if(strpos($method, "view", 0)!==false) {
            $method = substr($method,strlen("view"));
            $method = "display".ucfirst($method);
            return call_user_func(array($this, $method),$params);
        }
    }

    /* call error  */
    private function callFlickrError() {
        $this->smarty->assign("error",$this->flickr->getLastResponse());
        $this->smarty->assign("method",$this->flickr->debugGetLastRestCall());
        return $this->smarty->display("flickr_apierror.xhtml");
    }

    private function addCSS($file_name,$ntmp="") {
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.$ntmp."_pp_css"] =
                '<link href="typo3conf/ext/'.$this->extKey.'/res/css/'.$file_name.'" type="text/css" rel="stylesheet""></link>';
    }

    private function addJS($js,$js_tmp="") {
        $tmp_js = file_get_contents(t3lib_extMgm::siteRelPath($this->extKey)."res/js/".$js);
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.$js_tmp."_js"]
                = TSpagegen::inline2TempFile($tmp_js, 'js');
    }

    /* This function parses rest call from TS for past processing*/
    public static function ParseTSFlickrCall($val) {
        /* Name parsing */
        $name = trim(substr($val,0,strpos($val,"(")));

        /* Argument parsing */
        $fargs = substr($val,strpos($val,"(")+1,strpos($val,")")-strpos($val,"(")-1);

        $fargs = explode(",",trim($fargs));
        array_walk($fargs,create_function('&$v,&$k',' $p = explode("=",$v); $v = array(trim($p[0])=>trim($p[1])); '));
        $fargs_p = array();
        foreach($fargs as $argp) $fargs_p = array_merge($fargs_p,$argp);
        $fargs = array_merge($fargs_p);
        unset($fargs_p);

        /* Post array */
        $post_array_str = false;
        if(strpos($val, "[")!==false) $post_array_str = substr($val,strpos($val,"["));

        return array("name"=>$name,"args"=>$fargs,"post_array_str"=>$post_array_str);
    }

    /* Parse arguments */
    public static function ParseTSFlickrParams(tx_tendflickr &$flickr,$params) {
        $params_n = array();
        foreach($params as $key=>$val) {
            $params_n[$key] = trim($val);
            if(strpos($val,"restFlickr")!==false) {
                $ts_call = tx_tendflickr_pi1::ParseTSFlickrCall($val);
                $ts_call_data = call_user_func(array($flickr,$ts_call["name"]),$ts_call["args"]);

                //TODO: Check if error from API was made
                //$flickr->...

                if($ts_call["post_array_str"]!=false)
                    eval('$ts_call_data = $ts_call_data'.$ts_call["post_array_str"].';');

                $params_n[$key] = $ts_call_data;
            }
        }

        return $params_n;
    }

    private function displayGeneric($method=false){
        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr,$this->conf_ts["show."]["params."]);

        $photos = call_user_func(array($this->flickr,$this->conf_ts["show."]["call"]),$params);

        //var_dump($photos);
        $this->smarty->assign("out",var_export($photos,true));
        
        return $this->smarty->display("flickr_generic.xhtml");
    }

    /* Display photostream */
    private function displayPhotostream() {

        //TODO: Implement...

        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr,$this->conf_ts["show."]["params."]);

        $photos = $this->flickr->restFlickr_Photosets_getList($params);

        if(!$photos) return $this->callFlickrError();

        

        return $this->smarty->display("flickr_photostream.xhtml");
    }

    /* Display search results */
    private function displayPhotossearch() {
        $this->addCSS("simplelist.css");

        $par = tx_tendflickr_pi1::ParseTSFlickrParams( $this->conf_ts["show."]["params."]);

        $photos = $this->flickr->restFlickr_Photos_Search($par);
        if(!$photos) return $this->callFlickrError();

        $this->smarty->assign("photos",$photos["photos"]["photo"]);
        $this->smarty->assign("cache_till",$photos["cache_till"]);

        $this->smarty->assign("cache_time_diff", date("i\m s\s",strtotime($photos["cache_till"])-  strtotime("now") ) );

        return $this->smarty->display("flickr_simplelist.xhtml");
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php']);

