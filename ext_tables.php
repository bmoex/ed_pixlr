<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_edpixlr_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'lib/class.tx_edpixlr_cm1.php'
	);
	require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_versionhelper.php');
	$version = tx_edpixlr_versionhelper::getTypo3Version();
	if ($version >= 4004000) {
    	t3lib_SpriteManager::addSingleIcons(
    	    array(
                'clickmenu' => t3lib_extMgm::extRelPath($_EXTKEY).'cm1/cm_icon.gif',
            ),
            $_EXTKEY
        );
    }
}
?>
