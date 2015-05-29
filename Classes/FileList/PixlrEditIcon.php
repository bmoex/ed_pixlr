<?php
namespace Bluechip\EdPixlr\FileList;

use Bluechip\EdPixlr\Utility\ArrayUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Filelist Edit Icon: Pixlr
 *
 * @package Bluechip\EdPixlr\FileList
 */
class PixlrEditIcon implements \TYPO3\CMS\Filelist\FileListEditIconHookInterface, \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \Bluechip\EdPixlr\Service\PixlrService
     * @inject
     */
    protected $pixlrService;

    /**
     * Add Pixlr edit icon when user is allowed to use Pixlr
     *
     * @param array $cells Array of edit icons
     * @param \TYPO3\CMS\Filelist\FileList $parentObject Parent object
     * @return void
     */
    public function manipulateEditIcons(&$cells, &$parentObject)
    {
        if ($this->getPixlrService()->accessAllowed()) {
            $editIcon = '<span class="btn btn-default disabled">' . IconUtility::getSpriteIcon('empty-empty') . '</span>';
            if (preg_match('/launchView\(\s*\'_FILE\'\s*,\s*\'([^\']*?)\'/ms', $cells['info'], $matches)) {
                $file = $this->getPixlrService()->getEditableFile($matches[1]);
                if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
                    $editIcon = $this->getPixlrService()->getPixlrEditIcon($file);
                }
            }

            ArrayUtility::insertAfter('info', $cells, 'ed_pixlr_edit', $editIcon);
        }
    }

    /**
     * @return \Bluechip\EdPixlr\Service\PixlrService
     */
    protected function getPixlrService()
    {
        if ($this->pixlrService === null) {
            $this->pixlrService = $this->getObjectManager()->get('Bluechip\\EdPixlr\\Service\\PixlrService');
        }
        return $this->pixlrService;
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
}