<?php
namespace Bluechip\EdPixlr\Clickmenu;

use Bluechip\EdPixlr\Utility\ArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Clickmenu: Adding EdPixlr clickmenu item
 *
 * @package Bluechip\EdPixlr\Clickmenu
 */
class Clickmenu implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \Bluechip\EdPixlr\Service\PixlrService
     * @inject
     */
    protected $pixlrService;

    /**
     * Processing of ClickMenu items
     *
     * @param \TYPO3\CMS\Backend\ClickMenu\ClickMenu $clickMenu Reference to parent
     * @param array $menuItems Menu items array to modify
     * @param string $table Table name
     * @param integer $uid Uid of the record
     * @return array Menu item array, returned after modification
     */
    public function main(&$clickMenu, $menuItems, $table, $uid)
    {
        if (strlen($table) > 0 && !empty($uid)) {
            return $menuItems;
        }

        if ($clickMenu->cmLevel === 0 && (bool) $clickMenu->isDBmenu === false) {

            $file = $this->getPixlrService()->getEditableFile($table);
            if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
                $link = BackendUtility::getModuleUrl('file_EdPixlrEdPixlrEditor', array('file' => $file->getUid()));
                $icon = IconUtility::getSpriteIcon('extensions-ed_pixlr-pixlr_edit');

                $menuItem = $clickMenu->linkItem(
                    $this->getPixlrService()->translate('cm1_title'),
                    $icon,
                    $clickMenu->urlRefForCM($link, 'returnUrl'),
                    1
                );

                ArrayUtility::insertAfter('info', $menuItems, 'ed_pixlr_edit', $menuItem);
            }
        }

        return $menuItems;
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
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }
}
