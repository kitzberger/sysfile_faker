<?php

(defined('TYPO3_MODE') || defined('TYPO3')) || exit('Access denied.');

if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sysfile_faker'])) {
    // TYPO3 10
    $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sysfile_faker'];
} elseif (isset($_EXTCONF)) {
    // TYPO3 8+9
    $configuration = unserialize($_EXTCONF);
}

if (!empty($configuration) && is_array($configuration)) {
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
