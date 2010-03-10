<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

if (!defined ('TYPO3_MODE')) die ('Access denied.');
include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_tendflickr.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_tendflickr_pi1.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_tendflickr_pi2.php');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';

//$TCA['tt_content']['types'][$_EXTKEY.'_pi1']['showitem']='CType;;4;button;1-1-1, header;;3;;2-2-2,pi_flexform;;;;1-1-1'; // new!
//$TCA['tt_content']['columns']['pi_flexform']['config']['ds'][','.$_EXTKEY.'_pi1'] = 'FILE:EXT:'.$_EXTKEY.'/flexform_ds_pi1.xml'; // new!

// Extension is added to plugin list
t3lib_extMgm::addPlugin(array(
	'tend_flickr',$_EXTKEY . '_pi1', t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');
t3lib_extMgm::addPlugin(array(
	'tend_flickr_upload',$_EXTKEY . '_pi2', t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');

// TODO: Please fix this if you know how...
// $TCA['tx_tendflickr_cache'] = array ();
t3lib_div::loadTCA('tx_tendflickr_cache');

/* Flex form */
// t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","tend_flickr pi1 Flex Form");

//if (TYPO3_MODE=="BE")
//    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_tendflickr_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_tendflickr_pi1_wizicon.php';


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/pi1/flexform_ds_pi1.xml');



if (TYPO3_MODE=="BE")
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_sampleflex_pi1_wizicon"]
        =t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_sampleflex_pi1_wizicon.php';