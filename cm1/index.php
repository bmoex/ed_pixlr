<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Edermayr Ronald <ed@bluechip.at>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   59: class tx_edpixlr_cm1 extends t3lib_SCbase
 *   66:     private function init()
 *   88:     function main()
 *  130:     function save()
 *  146:     function checkVals()
 *  161:     function renderForm()
 *  183:     function printContent()
 *  196:     private function executePost($app, $lang, $file)
 *  240:     private function buildRequest($file, $pixlr_vars, $options)
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');

require_once(t3lib_extMgm::extPath('ed_pixlr').'lib/class.tx_edpixlr_versionhelper.php');
$version = tx_edpixlr_versionhelper::getTypo3Version();
if ($version >= 6000000) {
	require_once t3lib_extMgm::extPath('backend') . 'Classes/Template/MediumDocumentTemplate.php';
	require_once t3lib_extMgm::extPath('backend') . 'Classes/Module/BaseScriptClass.php';
} else {
	require_once($BACK_PATH.'template.php');
	require_once(PATH_t3lib.'class.t3lib_scbase.php');
}
$LANG->includeLLFile('EXT:ed_pixlr/cm1/locallang.xml');
require_once('lib/class.tx_edpixlr_pixlrTools.php');

/**
 * ed_pixlr module cm1
 *
 * @author	Edermayr Ronald <ed@bluechip.at>
 * @package	TYPO3
 * @subpackage	tx_edpixlr
 */
class tx_edpixlr_cm1 extends t3lib_SCbase {

	/**
	 * Init Function: initialize all needed vars
	 *
	 * @return	void
	 */
	function init() {
		$this->state = t3lib_div::_GP('state') ? t3lib_div::_GP('state') : t3lib_div::_GP('ed_pixlr_state');
		$this->overlay = t3lib_div::_GP('overlay');
		$this->submitted = t3lib_div::_GP('ed_pixlr_submit');

		$filename = t3lib_div::_GP('ed_pixlr_filename');
		$origfile = t3lib_div::_GP('ed_pixlr_origpath');
		$this->origFile = $origfile;
		$origpath = dirname($origfile);
		$origfile = basename($origfile);
		$this->overwrite = t3lib_div::_GP('ed_pixlr_overwrite');
		if(empty($filename)) $filename = t3lib_div::_GP('title');
		$ext = pathinfo($filename,PATHINFO_EXTENSION);
		if(empty($ext) || $ext!=t3lib_div::_GP('type')) $filename .= '.'.t3lib_div::_GP('type');

		$this->filename = $filename;
		$this->filenameWithPath = $origpath.'/'.$filename;
		
		if(tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
			$this->initFal();
		}
	}
	
	protected function initFal() {
		$origFileObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject($this->origFile);
		$origFolderObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject(dirname($this->origFile));
		$storage = $origFileObject->getStorage();
		$input = $this->filenameWithPath;
		list($prefix, $fileIdentifier) = explode(':', $input);
		$this->origFile = $origFileObject;
		$this->origFolder = $origFolderObject;
		$this->storage = $storage;
		$this->filenameWithPath = $fileIdentifier;
	}

	/**
	 * Main function of the module.
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$TYPO3_CONF_VARS;
		$this->init();
		if(!empty($this->state)) {
			if($this->overlay) {
				$this->overwrite = 1;
				$this->convert = 1;
				$this->submitted = 1;
			}
			$error = $this->checkVals();
			if(empty($this->submitted)) $error = '';

			if((empty($this->submitted) || $error)) {
				$this->showSaveForm($error);
			} else {
				$this->save();
			}
		} else {
			$confArr = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['ed_pixlr']);
			$app = $BE_USER->userTS['tx_edpixlr.']['rendertype'] ? $BE_USER->userTS['tx_edpixlr.']['rendertype'] : $confArr['defaultEditor'];
			if(!tx_edpixlr_pixlrTools::isPixlrApp($app)) $app = $confArr['defaultEditor'];
			$this->executePost($app, $LANG->lang, $this->getFile(t3lib_div::_GP('file')));
		}
	}
	
	function showSaveForm($error) {
		global $BE_USER, $LANG, $BACK_PATH;
		
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form = '<form action="" method="post">';

		$this->content .= $this->doc->startPage($LANG->getLL('title'));
		$this->content .= $this->doc->header($LANG->getLL('title'));
		$this->content .= $this->doc->spacer(5);
		$this->content .= $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
		$this->content .= $this->doc->divider(5);

		$this->content .= '<div id="typo3-inner-docbody">';
		if($error) {
			if (tx_edpixlr_versionhelper::getTypo3Version() >= 4003000) {
				$this->content .= '<div id="typo3-messages"><div class="typo3-message message-error"><div class="message-body">'.$error.'</div></div></div>';
			} else {
				$this->content .= '<p style="color:red;padding-bottom: 0.5em;"><strong>'.$error.'</strong></p>';
			}
		}
		
		if(!$this->overlay) {
			$this->content .= $this->renderForm();
		} else {
			$this->content .= '<p><br /><input type="submit" name="ed_pixlr_submit" value="'.$LANG->getLL('label_close').'" onClick="parent.pixlr.overlay.hide();" /></p>';
		}
		$this->content .= $this->doc->spacer(10);
		$this->content .= '</div>';
	}

	/**
	 * Save the returned file
	 *
	 * @return	void
	 */
	 
	function save() {
		if(tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
			$this->saveFal();
		} else {
			$this->saveLegacy();
		}
	}
	function saveLegacy() {
		global $LANG, $BACK_PATH;
		$download = t3lib_div::_GP('image');
		if(file_exists($this->filenameWithPath) && !$this->overwrite) {
			$this->showSaveForm($LANG->getLL('error_fileexists'));
			return;
		}
		$file_content = t3lib_div::getURL($download);
		if($file_content === false) {
			$this->showSaveForm($LANG->getLL('error_download'));
			return;
		}
		$newExt = t3lib_div::_GP('type');
		$ext = strtolower(substr($this->origFile,-3,3));
		$wasWritten = false;
		if($this->convert && $ext !== $newExt) {
			$tempFile = PATH_site.'typo3temp/ed_pixlr_'.md5($this->origFile.'|'.filemtime($this->origFile)).".$newExt";
			$wasWritten = t3lib_div::writeFile($tempFile, $file_content);
			if($wasWritten) {
				$wasWritten = false;
				$cmd = t3lib_div::imageMagickCommand('convert', '"'.$tempFile.'" "'.$this->origFile.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path']);
				exec($cmd);
				unlink($tempFile);
				if (@is_file($this->origFile)) {
					t3lib_div::fixPermissions($this->origFile);
					$wasWritten = true;
				}
			}
		} else {
			$wasWritten = t3lib_div::writeFile($this->filenameWithPath, $file_content);
		}
		
		if($wasWritten === false) {
			$this->showSaveForm($LANG->getLL('error_write'));
			return;
		}
		if($this->overlay) {
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post">';

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content = '<script type="text/javascript">
				/*<![CDATA[*/
				if(parent){
					parent.pixlr.overlay.hide();
				}
				/*]]>*/
			</script>';
		} else {
			header('Location: '.t3lib_div::_GP('ed_pixlr_referer'));
		}
	}
	
	function saveFal() {
		global $LANG, $BACK_PATH;
		$download = t3lib_div::_GP('image');
		if($this->storage->hasFile($this->filenameWithPath) && !$this->overwrite) {
			$this->showSaveForm($LANG->getLL('error_fileexists'));
			return;
		} elseif(!$this->storage->hasFile($this->filenameWithPath)) {
			$newFile = $this->storage->createFile($this->filename, $this->origFolder);
		} else {
			$newFile = $this->storage->getFile($this->filenameWithPath);
		}
		$file_content = t3lib_div::getURL($download);
		if($file_content === false) {
			$this->showSaveForm($LANG->getLL('error_download'));
			return;
		}
		$newExt = t3lib_div::_GP('type');
		$ext = strtolower($this->origFile->getExtension());
		$wasWritten = true;
		/*
		if($this->convert && $ext !== $newExt) {
			$tempFile = PATH_site.'typo3temp/ed_pixlr_'.md5($this->origFile->getIdentifier().'|'.$this->origFile->getModificationTime()).".$newExt";
			$wasWritten = t3lib_div::writeFile($tempFile, $file_content);
			if($wasWritten) {
				$wasWritten = false;
				$cmd = t3lib_div::imageMagickCommand('convert', '"'.$tempFile.'" "'.$this->origFile.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path']);
				exec($cmd);
				unlink($tempFile);
				if (@is_file($this->origFile)) {
					t3lib_div::fixPermissions($this->origFile);
					$wasWritten = true;
				}
			}
		} else {
			$wasWritten = t3lib_div::writeFile($this->filenameWithPath, $file_content);
		}*/
		$newFile->setContents($file_content);
		
		if($wasWritten === false) {
			$this->showSaveForm($LANG->getLL('error_write'));
			return;
		}
		if($this->overlay) {
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post">';

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content = '<script type="text/javascript">
				/*<![CDATA[*/
				if(parent){
					parent.pixlr.overlay.hide();
				}
				/*]]>*/
			</script>';
		} else {
			header('Location: '.t3lib_div::_GP('ed_pixlr_referer'));
		}
	}

	/**
	 * Check if all conditions are met
	 *
	 * @return	string		If an error is found a error message is returned, otherwise an empty string
	 */
	function checkVals() {
		global $LANG;
		$error = '';

		if(file_exists($this->filenameWithPath) && !$this->overwrite) {
			$error = $LANG->getLL('error_fileexists');
		}
		return $error;
	}

	/**
	 * Creates the code for the form for saving
	 *
	 * @return	string		content of the form
	 */
	function renderForm() {
		global $LANG, $_GET;
		$output = array();
		$output[] = '<p>';
		$output[] = '<label for="ed_pixlr_filename">'.$LANG->getLL('label_filename').':</label> <input type="text" name="ed_pixlr_filename" id="ed_pixlr_filename" value="'.($this->filename ? $this->filename : '').'" /><br /><br />'.chr(10);
		$output[] = '</p>';
		$output[] = '<p>';
		$output[] = '<input type="checkbox" name="ed_pixlr_overwrite" id="ed_pixlr_overwrite" value="1"'.($this->overwrite ? ' checked="checked"' : '').' /><label for="ed_pixlr_overwrite"> '.$LANG->getLL('label_overwrite').'</label><br />'.chr(10);
		$output[] = '</p>';
		$output[] = '<p><br /><input type="submit" name="ed_pixlr_submit" value="'.$LANG->getLL('label_submit').'" /></p>';

		foreach($_GET as $key=>$value) {
			$output[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
		return implode("\n",$output);
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		if($this->doc) $this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	private function getFile($file) {
		if(tx_edpixlr_versionhelper::getTypo3Version() >= 6000000) {
			$fileOrFolderObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject($file);
			if ($fileOrFolderObject instanceof \TYPO3\CMS\Core\Resource\File && $fileOrFolderObject->exists()) {
				$file = $fileOrFolderObject;
			} else {
				throw new exception("File \"{$file}\" not found");
			}
		} elseif(!file_exists($file) || !is_file($file)) {
			throw new exception("File \"{$file}\" not found");
		}
		return $file;
	}
	
	private function getFilename($file) {
		return is_a($file, '\TYPO3\CMS\Core\Resource\File') ? $file->getName() : basename($file);
	}
	private function getFileExtension($file) {
		return strtolower(is_a($file, '\TYPO3\CMS\Core\Resource\File') ? $file->getExtension() : pathinfo($file, PATHINFO_EXTENSION));
	}
	private function getFileMimeType($file) {
		if(is_a($file, '\TYPO3\CMS\Core\Resource\File')) {
			return $file->getMimeType();
		} else {
			if (function_exists('finfo_file')) {
				$fileInfo = new finfo();
				return $fileInfo->file($file, FILEINFO_MIME_TYPE);
			} elseif (function_exists('mime_content_type')) {
				return mime_content_type($file);
			} else {
				return 'image/'.strtolower(pathinfo($file, PATHINFO_EXTENSION));
			}
		}
	}
	private function getFilePath($file) {
		return is_a($file, '\TYPO3\CMS\Core\Resource\File') ? $file->getCombinedIdentifier() : $file;
	}
	private function getFileContents($file) {
		return is_a($file, '\TYPO3\CMS\Core\Resource\File') ? $file->getContents() : file_get_contents($file);
	}

	/**
	 * Executes a HTTP-POST to pixlr
	 *
	 * @param	string		$app: the type of app to use (express/editor)
	 * @param	string		$lang: the language to use
	 * @param	string		$file: the path to the file for editing in pixlr
	 * @return	void
	 */
	private function executePost($app, $lang, $file) {
		$pixlr_vars = array();
		$pixlr_vars['title'] = $this->getFilename($file);
		$pixlr_vars['referrer'] = 'ed_pixlr on '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		$pixlr_vars['exit'] = urlencode(t3lib_div::getIndpEnv('HTTP_REFERER'));
		$pixlr_vars['loc'] = $lang;
		$pixlr_vars['target'] = urlencode(t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT').'?'.($this->overlay?'overlay=1&':'').'ed_pixlr_state=1&ed_pixlr_origpath='.urlencode($this->getFilePath($file)).'&ed_pixlr_referer='.urlencode(t3lib_div::getIndpEnv('HTTP_REFERER')));
		$pixlr_vars['method'] = 'get';
		$pixlr_vars['locktarget'] = 'false';
		$pixlr_vars['locktitle'] = 'true';
		$pixlr_vars['locktype'] = 'source';

		$options = array();

		$options['app'] = tx_edpixlr_pixlrTools::getPixlrApp($app);

		$post = $this->buildRequest($file, $pixlr_vars, $options);
		file_put_contents('test.txt', $post);

		$f = fsockopen(tx_edpixlr_pixlrTools::PIXLR_HOST, 80);
		fputs($f, $post);
		$response = '';
		while (!feof($f))
		{
		  $response .= fread($f, 1024);
		}

		$location = array();
		
		// the pixlr-image-adress in the response is wrong?! ... so replace it
		$response = preg_replace('/http:\/\/ip-(.*?).pixlr.com/', 'http://apps.pixlr.com', $response);
		//print_r('<pre>'.$response.'</pre>');
		//exit;

		if( 1 != preg_match('/Location: ([^\\r\\n]+)/i', $response, $location) )
		{
		  throw new exception('Unexpected response headers.');
		}
		if( strpos($location[1], '/') === 0 ) {
			$location[0] = str_replace($location[1], tx_edpixlr_pixlrTools::PIXLR_URL . $location[1], $location[0]);
		}
		header($location[0]);
	}

	/**
	 * Builds the header and data-section for the HTTP_POST
	 *
	 * @param	string		$file: the path to the file for editing in pixlr
	 * @param	array		$pixlr_vars: variables to pass to pixlr
	 * @param	array		$options: some options for pixlr
	 * @return	string		header and data for the HTTP-POST
	 */
	private function buildRequest($file, $pixlr_vars, $options)	{

		$file_data = $this->getFileContents($file);
		$basename = $this->getFilename($file);
		$content_type = $this->getFileMimeType($file);

		$boundary = '---------------------------'.md5(uniqid());

		// Build the header
		$header = 'POST '.tx_edpixlr_pixlrTools::getPixlrAppUrl($options['app'])." HTTP/1.0\r\n";
		$header .= 'Host: '.tx_edpixlr_pixlrTools::PIXLR_HOST."\r\n";
		$header .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";

		$data = '';

		foreach($pixlr_vars AS $index => $value){
		    $data .="--$boundary\r\n";
		    $data .= "Content-Disposition: form-data; name=\"".$index."\"\r\n";
		    $data .= "\r\n".$value."\r\n";
		    //$data .="--$boundary\r\n";
		}

		$data .= "--$boundary\r\n";
		
		$data.="Content-Disposition: form-data; name=\"image\"; filename=\"{$basename}\"\r\n";
		$data .= "Content-Type: $content_type\r\n\r\n";
		$data .= ''.$file_data."\r\n";

		$data .="--$boundary--\r\n";
		$header .= 'Content-length: ' . strlen($data) . "\r\n\r\n";
		return $header.$data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/cm1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_edpixlr_cm1');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>
