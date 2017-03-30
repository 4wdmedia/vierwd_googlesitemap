<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "vierwd_googlesitemap".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'Google Sitemap.xml',
	'description' => 'Google sitemap implementation that also outputs urls based on database entries',
	'category' => 'fe',
	'author' => 'Robert Vock',
	'author_email' => 'robert.vock@4wdmedia.de',
	'author_company' => 'FORWARD MEDIA',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => true,
	'lockType' => '',
	'version' => '1.1.0',
	'constraints' => [
		'depends' => [
			'php' => '5.5',
			'typo3' => '6.2.0-8.9.99',
		],
		'conflicts' => [],
		'suggests' => [],
	],
];
