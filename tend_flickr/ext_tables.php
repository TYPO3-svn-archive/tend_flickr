<?php

/* By Oto Brglez - <oto.brglez@tend.si> */

if (!defined ('TYPO3_MODE')) die ('Access denied.');
include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_tendflickr.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_tendflickr_pi1.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_tendflickr_pi2.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_tendflickr_displayhook.php');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';

/* Plugins */
t3lib_extMgm::addPlugin(array('tend_flickr',$_EXTKEY . '_pi1', t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'),'list_type');
t3lib_extMgm::addPlugin(array('tend_flickr_upload',$_EXTKEY . '_pi2', t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'),'list_type');

// TODO: Please fix this if you know how...
// $TCA['tx_tendflickr_cache'] = array ();
t3lib_div::loadTCA('tx_tendflickr_cache');

/* Tooltip */
if (TYPO3_MODE == 'BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_tendflickr_tooltip',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_tendflickr_tooltip.php'
	);
}

/* Flex forms */
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/pi1/flexform_ds_pi1.xml');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:'.$_EXTKEY.'/pi2/flexform_ds_pi2.xml');

/* Backend extensions */
// Whats this dude?! Gives problems on 4.3...
// if (TYPO3_MODE=="BE")
//    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_sampleflex_pi1_wizicon"]
//        =t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_sampleflex_pi1_wizicon.php';

/* Tables */
t3lib_extMgm::allowTableOnStandardPages('tx_tendflickr_photo');
t3lib_extMgm::addToInsertRecords('tx_tendflickr_photo');

/* TCA */
$TCA['tx_tendflickr_photo'] = array (
	'ctrl' => array (
		'title'     => 'tend_flickr Photo',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY upload_timestamp DESC',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
	),
);
