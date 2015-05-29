<?php
namespace Bluechip\EdPixlr\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service: Pixlr
 *
 * @package Bluechip\EdPixlr\Service
 */
class PixlrService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var boolean
     */
    protected $hasAccess;

    /**
     * @var array
     */
    protected $allowedExtensions = array(
        'gif',
        'jpg',
        'jpeg',
        'png'
    );

    /**
     * Check if current backend user has access to the module
     *
     * @param boolean $exitOnError
     * @return boolean
     */
    public function accessAllowed($exitOnError = false)
    {
        if ($this->hasAccess === null) {
            $this->hasAccess = $this->getBackendUserAuthentication()->modAccess(
                $GLOBALS['TBE_MODULES']['_configuration']['file_EdPixlrEdPixlrEditor'], $exitOnError
            );
        }
        return $this->hasAccess;
    }

    /**
     * Handle Pixlr Post via direct backend call
     *
     * @return void but still sets flash messages for backend interactions
     */
    public function handlePixlrPost()
    {
        try {
            $state = GeneralUtility::_GP('state');
            if (in_array($state, array('new', 'copy', 'replace'))) {
                // Handle used variables
                $image = GeneralUtility::_GP('image');
                $original = GeneralUtility::_GP('original');
                $title = GeneralUtility::_GP('title');
                $type = GeneralUtility::_GP('type');

                $this->saveImage($state, $image, $original, $title, $type);

                $this->addMessage($this->translate('file_added_description'), $this->translate('file_added_title'),
                    FlashMessage::OK);
            }
        } catch (\Exception $e) {
            $this->addMessage($e->getMessage(), $e->getCode(), FlashMessage::ERROR);
        }

        // If ajax call, force redirect to main folder
        $ajaxCall = GeneralUtility::_GP('ajaxID');
        if (!empty($ajaxCall)) {
            $script = '<script>';
            $script .= 'if (parent) { parent.window.location = parent.jQuery(\'a.go-back-to-list:first\').attr(\'href\'); }';
            $script .= '</script>';

            echo $script;
        }
    }

    /**
     * Save external image to original file folder
     *
     * @param string $state
     * @param string $url
     * @param integer $original
     * @param string $title
     * @param string $type
     * @throws \TYPO3\CMS\Core\Resource\Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    public function saveImage($state, $url, $original, $title, $type)
    {
        $data = GeneralUtility::getUrl($url);
        if (!empty($data)) {
            $original = $this->getResourceFactory()->getFileObject($original);
            /** @var \TYPO3\CMS\Core\Resource\Folder $folder */
            $folder = $original->getParentFolder();

            $file = null;
            $filename = $this->cleanFileName($title . '.' . $type);

            switch ($state) {
                case 'new':
                case 'copy':
                    $folderPath = GeneralUtility::getFileAbsFileName($folder->getPublicUrl());
                    $absoluteFilename = $this->getBasicFileUtility()->getUniqueName($filename, $folderPath);
                    $filename = str_replace($folderPath, '', $absoluteFilename);

                    $file = $folder->createFile($filename);

                    break;

                case 'replace':
                    $file = $this->getResourceFactory()->getFileObjectFromCombinedIdentifier($folder->getCombinedIdentifier() . $filename);
                    break;
            }

            if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
                $file->setMissing(false);
                $file->setContents($data);
            }
        } else {
            throw new \TYPO3\CMS\Core\Resource\Exception('Can\'t download the given image from ' . $url, 1432815550);
        }
    }

    /**
     * Clean filename, stripped from :BasicFileUtility::cleanFileName()
     *
     * @param string $fileName
     * @return string
     */
    protected function cleanFileName($fileName)
    {
        $fileName = preg_replace('/[\\x00-\\x2C\\/\\x3A-\\x3F\\x5B-\\x60\\x7B-\\xBF]/u', '_', trim($fileName));
        header('Content-Disposition: inline; filename=' . $fileName . ';');

        return $fileName;
    }

    /**
     * Get Pixlr edit icon
     *
     * @param string $path
     * @return \TYPO3\CMS\Core\Resource\File
     */
    public function getEditableFile($path)
    {
        $file = null;
        if ($this->accessAllowed()) {
            $info = pathinfo($path);
            if (isset($info['extension']) && in_array(strtolower($info['extension']), $this->allowedExtensions)) {
                try {
                    $file = $this->getResourceFactory()->getFileObjectFromCombinedIdentifier($path);
                } catch (\Exception $e) {
                    $file = null;
                }
            }
        }
        return $file;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string
     */
    public function getPixlrEditIcon($file)
    {
        $icon = IconUtility::getSpriteIcon('extensions-ed_pixlr-pixlr_edit');
        $link = BackendUtility::getModuleUrl('file_EdPixlrEdPixlrEditor',
            array('file' => $file->getUid()));

        $onClick = 'top.content.list_frame.location.href=\'' . $link . '&returnUrl=\'+top.rawurlencode(top.content.list_frame.document.location.pathname+top.content.list_frame.document.location.search);return false;';
        $title = $this->translate('cm1_title');
        return '<a class="btn btn-default" title="' . $title . '" onclick="' . $onClick . '" href="#">' . $icon . '</a>';
    }

    /**
     * Add Generic Flash Message for backend interaction
     *
     * @param string $description
     * @param string $title
     * @param integer $severity
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addMessage($description, $title = null, $severity = FlashMessage::INFO)
    {
        $message = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
            $description,
            $title,
            $severity,
            true
        );
        $this->getFlashMessageQueue()->enqueue($message);
    }

    /**
     * Translate given key based on current extension
     *
     * @param string $key
     * @return string
     */
    public function translate($key)
    {
        $translation = LocalizationUtility::translate($key, 'ed_pixlr');
        if ($translation) {
            return $translation;
        }
        return $key;
    }

    /**
     * @return \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    protected function getFlashMessageQueue()
    {
        return $this->getObjectManager()->get('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService')->getMessageQueueByIdentifier();
    }

    /**
     * @return \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected function getResourceFactory()
    {
        return $this->getObjectManager()->get('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

    /**
     * @return \TYPO3\CMS\Core\Utility\File\BasicFileUtility
     */
    protected function getBasicFileUtility()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility');
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}