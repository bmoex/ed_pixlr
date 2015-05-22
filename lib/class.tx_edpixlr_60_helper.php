<?php
class tx_edpixlr_60_helper {
	static function getTypo3Version() {
		return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
	}
	
	static function getFileInformation($file) {
		$origFileObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject($file);
		if ($origFileObject instanceof TYPO3\CMS\Core\Resource\AbstractFile) {
			return t3lib_basicFileFunctions::getTotalFileInfo($origFileObject->getForLocalProcessing(FALSE));
		}
		return false;
	}
}
?>
