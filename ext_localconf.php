<?php
if (!defined('TYPO3_MODE')) {
   die('Access denied.');
}

if (TYPO3_MODE === 'BE') {
   $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['fileList']['editIconsHook'][$_EXTKEY] = 'Bluechip\\EdPixlr\\FileList\\PixlrEditIcon';
}