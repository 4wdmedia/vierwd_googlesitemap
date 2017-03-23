<?php

defined('TYPO3_MODE') || die('Access denied.');

$extensionNamespace = 'Vierwd\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $_EXTKEY)));

// ***************
// Realurl config
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration'][$_EXTKEY] = $extensionNamespace . '\\Hooks\\Realurl->addConfig';
