<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Faking missing sys_file images',
    'description' => 'Creates dummy image files for sys_file records whose files went missing',
    'category' => 'misc',
    'shy' => 0,
    'version' => '2.0.1',
    'dependencies' => 'cms',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => 'Philipp Kitzberger',
    'author_email' => 'typo3@kitze.net',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
