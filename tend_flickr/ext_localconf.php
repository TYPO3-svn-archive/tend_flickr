<?php

/* By Oto Brglez - <otobrglez@tend.si> */

if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_tendflickr_pi1.php', '_pi1', 'list_type', 0);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_tendflickr_pi2.php', '_pi2', 'list_type', 0);

$TYPO3_CONF_VARS['FE']['eID_include'][$_EXTKEY] = sprintf('EXT:%s/%s',$_EXTKEY,"class.tx_tendflickr_eid.php");

/* Hooks */
$TYPO3_CONF_VARS['EXTCONF']['tend_flickr']['preDisplayPhotostreamHook'][] =
    'EXT:tend_flickr/class.tx_tendflickr_displayhook.php:&tx_tendflickr_displayhook->preDisplayPhotostreamHookProcessor';
