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
    public $smarty        = false; // Smarty object
    public $flickr        = false; // Flickr API
    public $view_p        = false;

    public static $views = array(
            array("name"=>"photossearch","desc"=>"Flickr Photos search"),   // Photo search results
            array("name"=>"photosets","desc"=>"Flickr Photosets"),          // Photosets
            array("name"=>"viewphotoset","desc"=>"Flickr Photoset"),        // View Photoset photos
            array("name"=>"photostream","desc"=>"Flickr User Photostream"), // Display photostream
            array("name"=>"generic","desc"=>"TODO: Generic Display"),       // TODO: Finish generic display
    );

    public function init() {
        $this->pi_initPIflexForm();
        $this->ff_conf = array();
        $piFlexForm = $this->cObj->data['pi_flexform'];

        foreach ( $piFlexForm['data'] as $sheet => $data )
            foreach ( $data as $lang => $value )
                foreach ( $value as $key => $val )
                    $this->ff_conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
    }

    /* Main function */
    public function main($content, $conf) {
        $this->init();

        $this->conf_ts = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        $this->smarty = tx_smarty::smarty();
        $this->smarty->template_dir = t3lib_div::getFileAbsFileName('EXT:tend_flickr/res/templates');
        $this->smarty->setPathToLanguageFile('EXT:tend_flickr/pi1/locallang.xml');
        $this->smarty->register_function("flickr_image","tx_tendflickr::smarty_flickr_image");
        $this->smarty->register_function("flickr_photostream_image","tx_tendflickr::smarty_flickr_photostream_image");
        $this->smarty->register_block("typo3_link",array($this,'smarty_typo3_link'));
        $this->smarty->register_block("flickr_link",array($this,'smarty_flickr_link'));

        if(empty($this->conf_ts["flickr."]["api_key"])) {
            $this->conf_ts["flickr."] = $this->ff_conf;
            $this->conf_ts["show."] = $this->ff_conf;

            $tmp_display = $this->ff_conf["display"];
            foreach($this->ff_conf as $key=>$val)
                if(strpos($key,$tmp_display)!==false) if(trim($val)!="")
                        $this->conf_ts["show."]["params."][substr($key,strlen($tmp_display)+1)] = $val;

            //$pom = array();
            foreach($this->ff_conf as $key=>$val)
                if(strpos($key,"smarty_")!==false)
                    $this->conf_ts["smarty."][substr($key,strlen("smarty_"))] = $val;

            if(isset($this->conf_ts["smarty."]))
                foreach($this->conf_ts["smarty."] as $key=>$val)
                    $this->smarty->setSmartyVar($key,$val);
        }

        /* Missing API key */
        if(empty($this->conf_ts["flickr."]["api_key"]))
            return $this->smarty->display("flickr_tserror.xhtml");

        /* News tx_tendflickr singlton instance */
        $this->flickr = tx_tendflickr::getInstance();

        $this->flickr->setConfig($this->conf_ts["flickr."]["api_key"]
                /*, $this->conf_ts["flickr."]["api_username"], $this->conf_ts["flickr."]["api_password"]*/);

        // Cache time
        $this->flickr->setCacheTime(empty($this->conf_ts["flickr."]["api_cache"])?0:intval($this->conf_ts["flickr."]["api_cache"]));

        $display = trim($this->conf_ts["show."]["display"]);

        $views = tx_tendflickr_pi1::$views;

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
        $this->view_p = trim($display["name"]);
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

    public function addCSS($file_name,$ntmp="") {
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.$ntmp."_pp_css"] =
                '<link href="typo3conf/ext/'.$this->extKey.'/res/css/'.$file_name.'" type="text/css" rel="stylesheet""></link>';
    }

    public function addJS($js,$js_tmp="") {
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
        if($params)
            foreach($params as $key=>$val) {
                $params_n[$key] = trim($val);
                if(strpos($val,"restFlickr")!==false) {
                    $ts_call = tx_tendflickr_pi1::ParseTSFlickrCall($val);
                    $ts_call_data = call_user_func(array($flickr,$ts_call["name"]),$ts_call["args"]);

                    if($ts_call["post_array_str"]!=false) eval('$ts_call_data = $ts_call_data'.$ts_call["post_array_str"].';');

                    $params_n[$key] = $ts_call_data;
                }
            }

        return $params_n;
    }

    //TODO: Test and modify etc...
    private function displayGeneric($method=false) {
        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr,$this->conf_ts["show."]["params."]);
        $photos = call_user_func(array($this->flickr,$this->conf_ts["show."]["call"]),$params);
        if(!$photos) $this->callFlickrError();

        $this->smarty->assign("out",var_export($photos,true));

        return $this->smarty_display("flickr_generic.xhtml");
    }

    /* Display photos from photoset */
    private function displayViewphotoset() {
        $this->addCSS("flickr_viewphotoset.css");

        /* Display images */
        $photoset_id = null;
        if(isset($_GET['url_photoset'])) {
            $photoset_id = trim($_GET["url_photoset"]);
        } elseif( isset($this->conf_ts["show."]["params."]["photoset_id"])) {
            $photoset_id = $this->conf_ts["show."]["params."]["photoset_id"];
        } elseif( isset($this->conf_ts["show."]["vps_default"]) ) {
            $photoset_id = $this->conf_ts["show."]["vps_default"];
        }

        $params = array("photoset_id"=>$photoset_id);
        $params = array_merge($params,array("extras"=>"owner_name,icon_server,original_format,
            last_update,geo,tags,machine_tags,o_dims,views,media,path_aliasurl_sq,url_t,url_s,url_m,url_o"));
        $photos = $this->flickr->restFlickr_Photosets_getPhotos($params);
        if(!$photos) $this->callFlickrError();

        $photosets = false;
        $photosets = $this->flickr->restFlickr_Photosets_getList(array("user_id"=>$photos["photoset"]["owner"]));
        if(!$photosets) $this->callFlickrError();

        $ps = array();
        foreach($photosets["photosets"]["photoset"] as $key=>$val) {
            $val["typo3_uri"] = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array("url_photoset"=>$val["id"]));
            $ps[$key] = $val;
        };
        $photosets["photosets"]["photoset"] = $ps;
        unset($ps);

        $photoset = $this->flickr->restFlickr_Photosets_getInfo(array("photoset_id"=>$photoset_id));
        if(!$photoset) $this->callFlickrError();

        $this->smarty->assign("vps_show_picker",$this->conf_ts["show."]["vps_show_picker"]);
        $this->smarty->assign("vps_show_photoset_list",$this->conf_ts["show."]["vps_show_photoset_list"]);
        $this->smarty->assign("vps_show_desc",$this->conf_ts["show."]["vps_show_desc"]);
        $this->smarty->assign("photoset",$photoset["photoset"]);
        $this->smarty->assign("photos",$photos["photoset"]["photo"]);
        $this->smarty->assign("photosets",$photosets["photosets"]["photoset"]);
        $this->smarty->assign("photoset_id",$photoset_id);

        if($photoset_id != null) return $this->smarty_display("flickr_viewphotoset.xhtml");
        return "";
    }

    /* Display photosets */
    private function displayPhotosets() {
        $this->addCSS("flickr_photosets.css");

        var_dump($this->conf_ts["show."]["params."]);

        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr,$this->conf_ts["show."]["params."]);
        $params = array_merge($params,array("extras"=>"owner_name,icon_server,original_format,
            last_update,geo,tags,machine_tags,o_dims,views,media,path_aliasurl_sq,url_t,url_s,url_m,url_o"));

        $photosets = $this->flickr->restFlickr_Photosets_getList($params);
        if(!$photosets) return $this->callFlickrError();

        if(isset($this->conf_ts["show."]["params."]["goto_pid"]))
            $this->smarty->assign("pid",$this->conf_ts["show."]["params."]["goto_pid"]);

        $sets_keys = explode(",",$this->conf_ts["show."]["params."]["to_see"]);
   
        if(!in_array("ALL",$sets_keys)) {

            $photosets_list = array();
            foreach($sets_keys as $photoset_id) {
                foreach($photosets["photosets"]["photoset"] as $photoset) {
                    if( trim($photoset["id"]) == trim($photoset_id)) {
                        $photosets_list[] = $photoset;
                        continue;
                    }
                }
            }

            $this->smarty->assign("photosets",$photosets_list);
        } else {
            $this->smarty->assign("photosets",$photosets["photosets"]["photoset"]);
        }

        return $this->smarty_display("flickr_photosets.xhtml");
    }

    /* Display photostream */
    private function displayPhotostream() {
        $this->addCSS("flickr_photostream.css");

        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr,$this->conf_ts["show."]["params."]);
        $params = array_merge($params,array("extras"=>"owner_name,icon_server,original_format,
            last_update,geo,tags,machine_tags,o_dims,views,media,path_aliasurl_sq,url_t,url_s,url_m,url_o"));
        $photos = $this->flickr->restFlickr_People_getPublicPhotos($params);
        if(!$photos) return $this->callFlickrError();

        $this->smarty->assign("photos",$photos["photos"]["photo"]);

        return $this->smarty_display("flickr_photostream.xhtml");
    }

    /* Display search results */
    private function displayPhotossearch() {
        $this->addCSS("flickr_simplelist.css");

        $params = tx_tendflickr_pi1::ParseTSFlickrParams($this->flickr, $this->conf_ts["show."]["params."]);
        $params = array_merge($params,array("extras"=>"owner_name,icon_server,original_format,
            last_update,geo,tags,machine_tags,o_dims,views,media,path_aliasurl_sq,url_t,url_s,url_m,url_o"));

        $photos = $this->flickr->restFlickr_Photos_Search($params);
        if(!$photos) return $this->callFlickrError();

        $this->smarty->assign("photos", $photos["photos"]["photo"]);
        $this->smarty->assign("cache_till", $photos["cache_till"]);
        $this->smarty->assign("cache_time_diff", date("i\m s\s",strtotime($photos["cache_till"])-strtotime("now") ) );

        return $this->smarty_display("flickr_simplelist.xhtml");
    }

    /* Changes before display */
    public function smarty_display($template) {

        // Generic hooks
        $hook_name = "preDisplay".ucfirst($this->view_p)."Hook";

        // echo $hook_name.PHP_EOL."<br/>";

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tend_flickr'][$hook_name]))
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tend_flickr'][$hook_name] as $cRef)
                $obj = & t3lib_div::callUserFunction($cRef,$this->smarty,$this);

        return $this->smarty->display($template);
    }

    /* Perhapse it should be extended some more... ;) */
    public function smarty_typo3_link($params, $content, &$smarty, &$repeat) {
        /* if param has url_ then its appended to params of t3 page link call */
        $url_args= array_filter(array_flip($params),create_function('&$val','if(strpos($val,"url_")!==false) return $val;'));
        $url_args= array_flip(array_map(create_function('&$v','return $v;'), $url_args));

        if(!$repeat)
            return '<a href="'.$this->pi_getPageLink(intval($params["pid"]),null,$url_args).'"
                    title="'.(isset($params["title"])?$params["title"]:"").'">'.$content.'</a>';
    }

    /* Linkg to flickr image */
    public function smarty_flickr_link($params,$content, &$smarty, &$repeat) {
        if($repeat) return;

        $url = "";
        switch(strtolower($params["targetType"])) {
            default:
                $url = "http://www.flickr.com/photos/".$params["photo"]["owner"]."/".$params["photo"]["id"];
                break;
            case "image":
                $url = $params["photo"]["url_m"];
                break;
        }

        $css_class = isset($params["class"])?" class=\"".$params["class"]."\" ":"";
        $target = isset($params["target"])?" target=\"".$params["target"]."\" ":"";
        $rel = isset($params["rel"])?" rel=\"".$params["rel"]."\" ":"";
        $title = " title=\"". (isset($params["title"])?$params["title"]:$params["photo"]["title"]). "\" ";

        return "<a href=\"".$url."\" ".$css_class." ".$target." ".$rel." ".$title.">".$content."</a>";
    }

    public function getViewsListForFlexForm(&$config, &$item) {
        $optionList = array();
        foreach(tx_tendflickr_pi1::$views as $view)
            $optionList[] = array(1=>$view["name"],0=>sprintf("%s (%s)",$view["desc"],$view["name"]));
        return $config['items'] = array_merge($config['items'],$optionList);
    }

    public function getPhotosetsForFlexFormLite(&$config,&$item) {
        $data = $this->getPhotosetsForFlexForm($config, $item);
        $optionList[] = array("### All photosets ###","ALL","[icon]");
        return $config['items'] = array_merge($optionList,$config['items']);
    }

    public function getPhotosetsForFlexForm(&$config, &$item) {
        $api_key = false;
        $flex = new SimpleXMLElement($config["row"]["pi_flexform"]);
        $api_key = @ (array)$flex->xpath("/T3FlexForms/data/sheet[@index=\"display\"]//field[@index=\"api_key\"]/value");
        $api_key = $api_key[0]; //photosets_user_id
        $user_id = @ (array)$flex->xpath("/T3FlexForms/data/sheet[@index=\"sDEF\"]//field[@index=\"viewphotoset_user_id\"]/value");
        $user_id = $user_id[0];

        if(!$user_id) {
            $user_id = @ (array)$flex->xpath("/T3FlexForms/data/sheet[@index=\"sDEF\"]//field[@index=\"photosets_user_id\"]/value");
            $user_id = $user_id[0];
        }

        if(trim($api_key)!="" && trim($user_id)!="") {
            $flickr = tx_tendflickr::getInstance();
            $flickr->setConfig($api_key);
            $flickr->setCacheTime(10);

            $params = tx_tendflickr_pi1::ParseTSFlickrParams($flickr,
                    array("user_id"=>$user_id));

            $photos = $flickr->restFlickr_Photosets_getList($params);

            if($photos) {
                $optionList = array();
                foreach($photos["photosets"]["photoset"] as $photoset)
                    $optionList[] = array(1=>$photoset["id"], 0=>$photoset["title"]["_content"]);

                return $config['items'] = array_merge($config['items'],$optionList);
            }
        }
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/pi1/class.tx_tendflickr_pi1.php']);
