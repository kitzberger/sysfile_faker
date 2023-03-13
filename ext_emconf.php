<?php
$EM_CONF['sysfile_faker'] = [
    'title' => 'Faking missing sys_file images',
    'description' => 'Creates dummy image files for sys_file records whose files went missing',
    'category' => 'misc',
    'version' => '1.6.0',
    'dependencies' => 'cms',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => 'Philipp Kitzberger',
    'author_email' => 'typo3@kitze.net',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
