<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Faking missing sys_file images',
    'description' => 'Creates dummy image files for sys_file records whose files went missing',
    'category' => 'misc',
    'shy' => 0,
    'version' => '1.7.0',
    'dependencies' => 'cms',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 1,
    'lockType' => '',
    'author' => 'Philipp Kitzberger',
    'author_email' => 'typo3@kitze.net',
    'author_company' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'suggests' => [
    ],
];
