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
    protected $cache_time = 0;
    protected $last_response = false;

    /* Clonning and constructor is disabled */
    public function  __construct() { }
    public function __clone() { }

    /* Set configuration */
    public function setConfig($api_key,$username=false,$password=false) {
        $this->api_key = $this->api_key==false?trim($api_key):$this->api_key;
        $this->api_username = $this->api_username==false?trim($username):$this->api_username;
        $this->api_password = $this->api_password==false?trim($password):$this->api_password;
    }

    public function setCacheTime($time) {
        $this->cache_time = $time;
    }

    public function getCacheTime() {
        return $this->cache_time;
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
                "dbg_rest_method"=>$this->dbg_rest_method,
                "dbg_last_method"=>$this->dbg_last_method,
                "dbg_params_raw"=>$this->dbg_params_raw,
                );
    }

    /* Call of method */
    public function __call($method,$params=false) {
        if(strpos($method, "rest",0) !== false) {
            $this->last_response = false;
            if(isset($params[0]) || $params!=false) $params = $params[0];
            $this->dbg_params_raw = $params;

            $this->dbg_last_method = $method;
            $method = strtolower(str_replace("_",".",substr($method,4)));
            $this->dbg_rest_method = $method;

            $params_m = array();
            $params_m["method"] = $method;
            $params_m["api_key"] = $this->api_key;
            $params_m["format"] = "php_serial";
            $params = array_merge($params_m,$params);
            ksort($params);

            $params_p = "";
            $params_hash = "";
            $all_hash = "";
            foreach($params as $k=>$v){ $params_p[] = urlencode($k)."=".urlencode($v);  $params_hash.= $v; };
            $params_p = implode("&",$params_p);

            $params_hash = md5("irocksecret".$params_hash);
            $all_hash = md5($params_p);
            $call_str = sprintf("%s%s",$this->flickr_url,$params_p);

            $this->dbg_rest_callstr = $call_str;
            $this->dbg_rest_params = $params_p;

            $cache_time = $this->getCacheTime();
            $cache_till = false; // until when the cache is valid
            
            $this->last_response = false;

            if($cache_time != 0) {
                /* Remove records older than 1 week */
                $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_tendflickr_cache',' cache_time < DATE_SUB(NOW(), INTERVAL 7 DAY)' );

                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_tendflickr_cache",
                        "cache_fingertip = '".$all_hash."' AND
                        cache_time > NOW()
                        LIMIT 1");

                $data = mysql_fetch_assoc($res);

                if(mysql_num_rows($res) != 0) {
                    $this->last_response =  $data["response"];
                    $cache_till = $data["cache_time"];
                } else {
                    $this->last_response = @ file_get_contents($call_str);
                    $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_tendflickr_cache',' cache_fingertip=\''.$all_hash.'\'' );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_tendflickr_cache',
                            array("cache_fingertip"=>$all_hash,
                            "cache_time"=> date("c",strtotime("+".$cache_time." second")),
                            "response"=>$this->last_response));
                }
            } else {
                $this->last_response = @ file_get_contents($call_str);
            };

            $this->last_response = unserialize($this->last_response);
            if($cache_till != false) $this->last_response["cache_till"] = $cache_till;
            
            return $this->last_response['stat']=='ok'?$this->last_response:false;
        }
    }

    public static function strToHex($string) {
        $hex='';
        for ($i=0; $i < strlen($string); $i++)
            $hex .= dechex(ord($string[$i]));
        return $hex;
    }

    public static function hexToStr($hex) {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        return $string;
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

    public static function smarty_flickr_image_url($params){
        return tx_tendflickr::smarty_flickr_image($params);

    }

    /* This function handles smarty flickr_image */
    public static function smarty_flickr_image($params, /*&*/ $smarty=false) {

        $photo = $params["photo"];
        $size = empty($params["size"])?"small":$params["size"];
        $title = !empty($params["title"])?trim($params["title"]): $photo["title"];
        $alt = !empty($params["alt"])?trim($params["alt"]): $photo["title"];
        $css_class = !empty($params["class"])?trim($params["class"]): "flickr_image";
        $photo["id"] = isset($photo["primary"])?$photo["primary"]:$photo["id"];
        
        //TODO: Width and height...
        /*
        $width = !empty($params["width"])?trim($params["width"]):"";
        $height = !empty($params["height"])?trim($params["height"]):"";
        */

        switch(strtolower($size)) {
            case "square": case "box": case "q": $size = "_s";
                break;
            default: case "small": case "s": $size = "_m";
                break;
            case "medium": case "m": $size =  "";
                break;
            case "large": case "b": $size = "_b";
                break;
            case "original": case "o": $size = "_o";
                break;
            case "thumbnail":  case "t": $size = "_t";
                break;
        }

        if($smarty!=false){
            return sprintf('<img src="http://farm%1$s.static.flickr.com/%2$s/%3$s_%4$s%5$s.jpg"
                title="%6$s" alt="%7$s" class="%8$s" />',
                    $photo["farm"], $photo["server"], $photo["id"],
                    $photo["secret"], $size, $title,
                    $alt, $css_class
                    /*, $width, $height */
            );
        } else {
           return sprintf('http://farm%1$s.static.flickr.com/%2$s/%3$s_%4$s%5$s.jpg',
                    $photo["farm"], $photo["server"], $photo["id"],
                    $photo["secret"], $size, $title,
                    $alt, $css_class
                    /*, $width, $height */
            );
        }
    }
} // eof class

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php'])
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/class.tx_tendflickr.php']);
