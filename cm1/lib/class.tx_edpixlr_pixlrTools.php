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
/*
 * Some pieces of this script came from the sfPixlrPlugin for Symphony (http://www.symfony-project.org/plugins/sfPixlrPlugin) 
 */
 
class tx_edpixlr_pixlrTools
{
  const PIXLR_HOST = 'apps.pixlr.com';
  const PIXLR_URL = 'http://www.pixlr.com/';

  static protected $pixlr_apps = array('editor', 'express');

  /**
   * Returns array with Pixlr application names.
   */
  static public function getPixlrApps()
  {
    return tx_edpixlr_pixlrTools::$pixlr_apps;
  }

  /**
   * If $app is valid Pixlr application, returns $app,
   * Otherwise returns default Pixlr application.
   */
  static public function getPixlrApp($app)
  {
    if(tx_edpixlr_pixlrTools::isPixlrApp($app))
    {
      return $app;
    }
    else
    {
      return 'express';
    }
  }

  /**
   * Check if $app is valid Pixlr application.
   */
  static public function isPixlrApp($app)
  {
    return in_array($app, tx_edpixlr_pixlrTools::getPixlrApps());
  }

  /**
   * Returns URL for Pixlr application $app, or if $app is
   * not valid application, URL to default Pixlr application.
   */
  static public function getPixlrAppUrl($app)
  {
    return '/'.tx_edpixlr_pixlrTools::getPixlrApp($app).'/';
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/cm1/class.tx_edpixlr_pixlrTools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ed_pixlr/cm1/class.tx_edpixlr_pixlrTools.php']);
}

?>