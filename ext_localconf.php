<?php
defined('TYPO3_MODE') || exit('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissing'] = (int)$_EXTCONF['enable'];

if ($_EXTCONF['fontFile']) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'] = $_EXTCONF['fontFile'];
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['fakeFilesIfMissingFont'] = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\FileReference'] = array(
 'className' => 'Kitzberger\\SysfileFaker\\FileReference'
);
