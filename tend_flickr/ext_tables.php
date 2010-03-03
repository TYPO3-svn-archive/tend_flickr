<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:tend_flickr/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'film.png'
),'list_type');

// TODO: Please fix this if you know how...
// $TCA['tx_tendflickr_cache'] = array ();
t3lib_div::loadTCA('tx_tendflickr_cach');