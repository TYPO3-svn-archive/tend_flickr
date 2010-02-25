<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

$dir = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR,dirname(__file__)));
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

class tx_tendflickr {
    /* Singlton instance */
    protected static $instance;

    /* Flickr API key */
    protected $api_key;

    /* Flickr username */
    protected $api_username = false;

    /* Flickr password */
    protected $api_password = false;

    /* Flickr userid*/
    protected $flickr_user_id = false;
    protected $flickr_method = false;
    protected $flickr_url = "http://api.flickr.com/services/rest/?";
    protected $last_response = false;

    /* Clonning and constructor is disabled */
    public function  __construct() {

    }
    public function __clone() {

    }

    /* Set configuration */
    public function setConfig($api_key,$username=false,$password=false) {
        $this->api_key = $this->api_key==false?trim($api_key):$this->api_key;
        $this->api_username = $this->api_username==false?trim($username):$this->api_username;
        $this->api_password = $this->api_password==false?trim($password):$this->api_password;
    }

    /* Reset configuration */
    public function resetConfig() {
        $this->api_key = $this->api_username = $this->api_password = false;
    }

    /* Get Instance for... */
    public static function getInstance() {
        if(self::$instance === NULL) self::$instance = new tx_tendflickr();
        return self::$instance;
    }

    /* call debug */
    public function debugGetLastRestCall() {
        return array("dbg_rest_callstr"=>$this->dbg_rest_callstr,
                "dbg_rest_params"=>$this->dbg_rest_params,
                "dbg_rest_method"=>$this->dbg_rest_method);
    }

    /* Call of method */
    public function __call($method,$params=false) {

        if(strpos($method, "rest",0) !== false) {
            
            $this->last_response = false;
            /* Only methods that start with rest are sent to flickr*/

            if(isset($params[0]) || $params!=false) $params = $params[0];
            
            $method = strtolower(str_replace("_",".",substr($method,4)));
            $this->dbg_rest_method = $method;
            $params["method"] = $method;
            $params["format"] = "php_serial";
            $params["api_key"] = $this->api_key;

            $params_p = "";
            foreach($params as $k=>$v) $params_p[] = urlencode($k)."=".urlencode($v);

            $params_p = implode("&",$params_p);
            $call_str = sprintf("%s%s",$this->flickr_url,$params_p);

            $this->dbg_rest_callstr = $call_str;
            $this->dbg_rest_params = $params_p;

            $this->last_response = @ file_get_contents($call_str);
            $this->last_response = unserialize($this->last_response);
            return $this->last_response['stat']=='ok'?$this->last_response:false;
        }
    }

    public function getLastResponseErrorMessage() {
        $resp = $this->getLastResponse();
        if($resp==false) return false;
        return $resp["message"];
    }

    public function getLastResponse() {
        if($this->last_response == false) return false;
        $p = $this->last_response;
        return $p;
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php'])
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php']);
