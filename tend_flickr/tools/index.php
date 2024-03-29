<?php

/* By Oto Brglez - <oto.brglez@tend.si> */


unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once("../OLD/phpFlickr/phpFlickr.php");

class tend_flickr_upload extends t3lib_SCbase {

    function menuConfig() {
        global $LANG;
        $this->MOD_MENU = Array ('function' => array ('1' => "Upload to Flickr...",));
        parent::menuConfig();
    }

    function main() {
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

        // Draw the header.
        $this->doc = t3lib_div::makeInstance('mediumDoc');
        $this->doc->backPath = $BACK_PATH;
        $this->doc->form = '<form action="" method="post">';

        // JavaScript
        $this->doc->JScode = '
            <script language="javascript" type="text/javascript">
            script_ended = 0;
            function jumpToUrl(URL)	{
            document.location = URL;
            }
            </script>
	';

        $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
        //  $this->pageinfo = "";
        $access = is_array($this->pageinfo) ? 1 : 0;
        if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {
            if ($BE_USER->user['admin'] && !$this->id) {
                $this->pageinfo = array(
                        'title' => '[root-level]','uid'   => 0, 'pid'   => 0
                );
            }

            $headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'
                    .$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'], 50);

            $this->content.=$this->doc->startPage($LANG->getLL('title'));
            $this->content.=$this->doc->header($LANG->getLL('title'));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,
                    t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',
                    $this->MOD_SETTINGS['function'],
                    $this->MOD_MENU['function'])));
            $this->content.=$this->doc->divider(5);

            // Render content:
            $this->moduleContent();

            // ShortCut
            if ($BE_USER->mayMakeShortcut()) {
                $this->content.=$this->doc->spacer(20).$this->doc->section('',
                        $this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
            }
        }
        $this->content.=$this->doc->spacer(10);
    }

    function printContent() {

        $this->content.=$this->doc->endPage();
        echo $this->content;
    }

    function moduleContent() {
        switch((string) $this->MOD_SETTINGS['function']) {
            default:
            case 1:
                $id = $_GET["id"];

                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('
                    uid,title,description,photo,author,
                    upload_timestamp,
                    flickr_mail,from_mail',
                    'tx_tendflickr_photo','uid='.intval($id));
                
                $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

                if(!empty($_GET["send"]) && $_GET["send"]=="flickr"){

                      // $to = "otobrglez@gmail.com";
                      $to = $data["flickr_mail"];
                      $from = $data["from_mail"];
                      $file = "../../../../uploads/tx_tendflickr/".$data["photo"];
                      $subject = $data["title"];
                      $random_hash = md5(date('r', time()));

                      $headers = "From: ".$from."\r\nReply-To: ".$from;
                      $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";

                      $attachment = chunk_split(base64_encode(file_get_contents($file)));
//echo "<pre>";
$output = "
--PHP-mixed-$random_hash;
Content-Type: multipart/alternative; boundary='PHP-alt-$random_hash'
--PHP-alt-$random_hash
Content-Type: text/plain; charset='iso-8859-1'
Content-Transfer-Encoding: 7bit

".$data["description"]."

--PHP-alt-$random_hash
Content-Type: text/html; charset='iso-8859-1'
Content-Transfer-Encoding: 7bit

".nl2br($data["description"])."

--PHP-alt-$random_hash--

--PHP-mixed-$random_hash
Content-Type: application/zip; name=".basename($file)."
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$attachment
--PHP-mixed-$random_hash--";


                @ mail($to, $subject, $output, $headers);

                   $content = "<font style=\"color:red; font-weight:bold; font-size:20px;\">Sent to Flickr...</font><br/>";
                }; // MAIL


                $content .= '
                    <h3>Tools</h3>
                <a href="../../../../typo3/alt_doc.php?edit[tx_tendflickr_photo]['.$id.']=edit">Edit Photo</a> |
                <a href="index.php?id='.$id.'&send=flickr">Send to Flickr</a> <br/>
                ';

                $content .= "<h3>Photo data</h3>

                <style>

                .data, .data td, .data tr, .data .th{
                    border-collapse:collapse;
                }

                .data{
                    border-left:1px solid #CCC;
                    border-top:1px solid #CCC;
                }

                .data td,
                .data th{
                    border-bottom:1px solid #CCC;
                    border-right:1px solid #CCC;
                    padding:5px;
                }

                </style>

                <table class=\"data\">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead><tbody>
                ";
                foreach($data as $key=>$val) {

                                $content .= "
                    <tr>
                        <td>".trim($key)."</td>
                        <td>".trim($val)."</td>
                    </tr>
                ";
                }

                $content .="</tbody></table>

                <br/>
                <a href=\"../../../../typo3/alt_doc.php?edit[tx_tendflickr_photo][$id]=edit\">Edit Photo</a> |
                <a href=\"index.php?id=$id&send=flickr\">Send to Flickr</a> <br/>

                <h3>".$data["photo"]."</h3>
                    <style>

                    #photo{ display: block; width:90%; height:auto;}
                    #photo img{ width: 100%; }
                    </style>
                    <div id=\"photo\">
                        <img src=\"../../../../uploads/tx_tendflickr/".$data["photo"]."\"/>
                    </div>
                ";



                $this->content.=$this->doc->section('Upload to Flickr... ',$content,0,1);
                break;
            case 2:
                break;
        }
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/tools/index.php'])
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tend_flickr/tools/index.php']);

$SOBE = t3lib_div::makeInstance('tend_flickr_upload');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

