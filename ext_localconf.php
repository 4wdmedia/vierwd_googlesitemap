<?php

defined('TYPO3_MODE') || die('Access denied.');

$extensionNamespace = 'Vierwd\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $_EXTKEY)));

// ***************
// Realurl config
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration'][$_EXTKEY] = $extensionNamespace . '\\Hooks\\Realurl->addConfig';

if (TYPO3_version < '7.0.0') {
	$typoscriptFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:' . $_EXTKEY . '/ext_typoscript_setup-6.2.txt');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup', file_get_contents($typoscriptFile));
}