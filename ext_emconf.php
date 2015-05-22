<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "ed_pixlr".
 *
 * Auto generated 06-05-2015 08:35
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Pixlr integration',
	'description' => 'Integrates the Pixlr online image editing service into the TYPO3 backend. Powered by Bluechip Software GmbH (www.bluechip.at)',
	'category' => 'be',
	'version' => '2.3.8',
	'state' => 'beta',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Bluechip Software',
	'author_email' => 'media@bluechip.at',
	'author_company' => '',
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.0.0-0.0.0',
			'typo3' => '4.2.0-6.2.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

