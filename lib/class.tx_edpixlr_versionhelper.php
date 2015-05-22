<?php
class tx_edpixlr_versionhelper {
	private static $version = false;
	static function getTypo3Version() {
		if(!self::$version) {
			if(class_exists('\TYPO3\CMS\Core\Utility\VersionNumberUtility')) {
				require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_60_helper.php');
				self::$version = tx_edpixlr_60_helper::getTypo3Version();
			} elseif(class_exists('t3lib_utility_VersionNumber')) {
				self::$version = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
			} else {
				self::$version = t3lib_div::int_from_ver(TYPO3_version);
			}
		}
		return self::$version;
	}
}
?>
