<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Pixlr integration',
    'description' => 'Integrates the Pixlr online image editing service into the TYPO3 backend.',
    'category' => 'be',
    'version' => '3.0.3',
    'state' => 'beta',
    'uploadFolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'author' => 'Bluechip Software',
    'author_email' => 'media@bluechip.at',
    'author_company' => 'Bluechip Software',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.2.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);

