<?php
namespace Bluechip\EdPixlr\Controller;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PixlrController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Bluechip\EdPixlr\Service\PixlrService
     * @inject
     */
    protected $pixlrService;

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    public function initializeAction()
    {
        parent::initializeAction();
        $this->getPixlrService()->accessAllowed(true);
    }

    /**
     * Action: Edit image with Pixlr integration
     *
     * @return void
     */
    public function editAction()
    {
        $file = (int) GeneralUtility::_GET('file');
        if ($file > 0) {
            $service = ($this->settings['render'] ? trim($this->settings['render']) : 'express');

            // get file object..
            $file = $this->getResourceFactory()->getFileObject($file);
            if ($file instanceof File) {
                $targetUrl = BackendUtility::getAjaxUrl(
                    'ed_pixlr::handle',
                    array('original' => $file->getUid()),
                    false,
                    true
                );

                $this->view->assignMultiple(array(
                    'file' => $file,
                    'fileUri' => $this->getFileLocation($file),
                    'target' => $targetUrl,
                    'service' => $service
                ));
            }
        }

        $this->view->assign('returnUrl', GeneralUtility::_GP('returnUrl'));
    }

    /**
     * Get absolute file location from given file
     *
     * @param \TYPO3\CMS\Core\Resource\File $file
     * @return string
     */
    protected function getFileLocation(File $file)
    {
        $imageUrl = trim($file->getPublicUrl());

        // no prefix in case of an already fully qualified URL (having a schema)
        if (strpos($imageUrl, '://')) {
            $uriPrefix = '';
        } else {
            $uriPrefix = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        return $uriPrefix . ltrim($imageUrl, '/');
    }

    /**
     * @return \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected function getResourceFactory()
    {
        return $this->getObjectManager()->get('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
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
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }
        return $this->objectManager;
    }
}