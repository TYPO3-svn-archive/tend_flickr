<?php
    /* Last updated with phpFlickr 1.4
     *
     * If you need your app to always login with the same user (to see your private
     * photos or photosets, for example), you can use this file to login and get a
     * token assigned so that you can hard code the token to be used.  To use this
     * use the phpFlickr::setToken() function whenever you create an instance of 
     * the class.
     */

$api_key                 = "2460a66b65f2d13340c9b0f1b975c550";
$api_secret              = "c85004276e00ba4e";


var_dump($_SERVER['REQUEST_URI']);
exit;

    require_once("phpFlickr.php");
    $f = new phpFlickr($api_key,$api_secret);
    
    //change this to the permissions you will need
    if (empty($_GET['frob'])) {
        $f->auth("read",false);
    } else {
        $token = $f->auth_getToken($_GET['frob']);
        echo "use this token to authenticate: ".$token['token'];
    }
    
   // echo "Copy this token into your code: " . $_SESSION['phpFlickr_auth_token'];
    
?>