<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$confArr = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);
t3lib_extMgm::addUserTSConfig('
	tx_edpixlr = '.intval($confArr['userEnabled']).'
');
require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_versionhelper.php');
$version = tx_edpixlr_versionhelper::getTypo3Version();
if ($version >= 4003000) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['fileList']['editIconsHook'][] = 'EXT:ed_pixlr/lib/class.tx_edpixlr_cm1.php:tx_edpixlr_cm1_editicons';
}
?>
