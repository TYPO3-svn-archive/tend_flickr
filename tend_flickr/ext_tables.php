<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

// Extension is added to plugin list
t3lib_extMgm::addPlugin(array(
	'LLL:EXT:tend_flickr/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');

// TODO: Please fix this if you know how...
// $TCA['tx_tendflickr_cache'] = array ();
t3lib_div::loadTCA('tx_tendflickr_cach');

/* Flex form */
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","tend_flickr pi1 Flex Form");
if (TYPO3_MODE=="BE")
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_tendflickr_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_tendflickr_pi1_wizicon.php';