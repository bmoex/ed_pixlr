<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

if ('BE' === TYPO3_MODE) {
    \TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
        array(
            'pixlr_edit' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/pixlr_edit.gif',
        ),
        $_EXTKEY
    );

    $GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
        'name' => 'Bluechip\\EdPixlr\\Clickmenu\\Clickmenu',
    );

    // Add hidden module for Pixlr integration
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Bluechip.' . $_EXTKEY,
        'file',
        'display',
        '',
        array(
            // Allowed controller action combinations
            'Pixlr' => 'edit',
        ),
        array(
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module.gif',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
        )
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.hideModules.file := addToList(EdPixlrDisplay)');
}
