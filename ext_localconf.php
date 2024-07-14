<?php

defined('TYPO3') || exit('Access denied.');

if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sysfile_faker'])) {
    $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sysfile_faker'];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing'] = (int)$configuration['enable'];

    if ($configuration['fontFile']) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'] = $configuration['fontFile'];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'] = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    }

    if ($configuration['remoteHost']) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['loadFilesFromRemoteIfMissing'] = [
            'remoteHost' => $configuration['remoteHost'],
            'remoteHostBasicAuth' => $configuration['remoteHostBasicAuth'],
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\FileReference::class] = [
        'className' => \Kitzberger\SysfileFaker\FileReference::class
    ];
}
