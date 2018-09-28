<?php

namespace Vierwd\VierwdGooglesitemap\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class SitemapController {

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public $cObj;

	/**
	 * @var array
	 */
	public $settings = [];

	/**
	 * @var array
	 */
	public $languages = [];

	public function generateAction($content, array $params) {
		$this->settings = $params['settings.'];
		$this->settings['includeDoktypes'] = GeneralUtility::intExplode(',', $this->settings['includeDoktypes']);

		if ($this->settings['showAlternateLanguages']) {
			$this->languages = $this->loadLanguages();
		}

		$typolinkRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

		$this->urls = [];

		$GLOBALS['TSFE']->gr_list = '0,-1';

		$page = $GLOBALS['TSFE']->sys_page->getPage($GLOBALS['TSFE']->rootLine[0]['uid']);
		$result = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$result .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		$result .= $this->renderPages([$page]);

		foreach ($this->settings['additionalLinks.'] as $configuration) {
			$records = $this->getRecords($configuration['records.']['table'], $configuration['records.']['select.']);

			$typolinkConfiguration = $configuration['typolink.'];
			$typolinkConfiguration['returnLast'] = 'url';
			$typolinkConfiguration = ['typolink.' => $typolinkConfiguration];
			foreach ($records as $record) {
				$typolinkRenderer->start($record, $configuration['records.']['table']);
				$url = $typolinkRenderer->cObjGetSingle('TEXT', $typolinkConfiguration);
				if ($url && !in_array($url, $this->urls)) {
					$this->urls[] = $url;
					$result .= '<url><loc>' . htmlspecialchars($url) . '</loc>';
					if ($record['tstamp']) {
						$result .= '<lastmod>' . date('c', $record['tstamp']) . '</lastmod>';
					}
					$result .= '</url>';
				}
			}
		}

		$result .= '</urlset>';

		return $result;
	}

	protected function loadLanguages() {
		$languages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_language',
			'1' . $GLOBALS['TSFE']->sys_page->enableFields('sys_language')
		);
		$languages = array_column($languages, null, 'uid');
		return $languages;
	}

	protected function getRecords($tableName, array $queryConfiguration) {
		$records = [];

		$res = $this->cObj->exec_getQuery($tableName, $queryConfiguration);

		while (($row = $this->fetchRow($res)) !== false) {

			// Versioning preview:
			$GLOBALS['TSFE']->sys_page->versionOL($tableName, $row, true);

			// Language overlay:
			if (is_array($row) && $GLOBALS['TSFE']->sys_language_contentOL) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
					$tableName,
					$row,
					$GLOBALS['TSFE']->sys_language_content,
					$GLOBALS['TSFE']->sys_language_contentOL
				);
			}

			// Might be unset in the sys_language_contentOL
			if (is_array($row)) {
				$records[] = $row;
			}
		}
		if (TYPO3_version <= '8.5.0') {
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		} else {
			$res->closeCursor();
		}

		return $records;
	}

	/**
	 * @param \mysqli_result|\Doctrine\DBAL\Driver\ResultStatement
	 */
	private function fetchRow($res) {
		if (TYPO3_version <= '8.5.0') {
			return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		} else {
			return $res->fetch(\PDO::FETCH_ASSOC);
		}
	}

	protected function renderPages(array $pages = []) {
		$result = '';

		if ($this->settings['showAlternateLanguages']) {
			$pageUids = array_column($pages, 'uid');
			$pageTranslations = $this->getPageTranslations($pageUids);
		}

		foreach ($pages as $page) {
			if (in_array($page['doktype'], $this->settings['includeDoktypes']) && !$page['no_search']) {
				if (!$GLOBALS['TSFE']->sys_language_uid || $page['_PAGES_OVERLAY']) {
					$url = $this->cObj->typoLink_URL([
						'parameter' => $page['uid'],
						'forceAbsoluteUrl' => true,
					]);
					if ($url && !in_array($url, $this->urls)) {
						$this->urls[] = $url;

						$result .= '<url><loc>' . htmlspecialchars($url) . '</loc>';

						if ($this->settings['showAlternateLanguages'] && $pageTranslations && $pageTranslations[$page['uid']]) {
							foreach ($pageTranslations[$page['uid']] as $translation) {
								$language = $this->languages[$translation];
								$hreflang = $language['language_isocode'];
								if ($hreflang !== $language['flag']) {
									$hreflang .= '-' . $language['flag'];
								}
								$alternateUrl = $this->cObj->typoLink_URL([
									'parameter' => $page['uid'],
									'additionalParams' => '&L=' . $translation,
									'forceAbsoluteUrl' => true,
								]);
								$result .= '<xhtml:link rel="alternate" hreflang="' . $hreflang . '" href="' . htmlspecialchars($alternateUrl) . '"/>';
							}
						}
						if ($page['SYS_LASTCHANGED']) {
							$result .= '<lastmod>' . date('c', $page['SYS_LASTCHANGED']) . '</lastmod>';
						}
						$result .= '</url>';
					}
				}
			}

			if (!$page['no_search'] || !$page['extendToSubpages']) {
				// add subpages
				$subpages = $this->getSubpages($page['uid']);
				$result .= $this->renderPages($subpages);
			}
		}

		return $result;
	}

	protected function getPageTranslations(array $pageUids) {
		if (!$pageUids) {
			return [];
		}

		$pageTranslations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'pages_language_overlay',
			'pid IN (' . implode(',', $pageUids) . ')' . $GLOBALS['TSFE']->sys_page->enableFields('pages_language_overlay')
		);
		$result = [];
		foreach ($pageTranslations as $translation) {
			$result[$translation['pid']][] = $translation['sys_language_uid'];
		}
		return $result;
	}

	protected function getSubpages($pageUid) {
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'pages',
			'pid=' . (int)$pageUid . $GLOBALS['TSFE']->sys_page->enableFields('pages'),
			'',
			'sorting'
		);
		foreach ($pages as &$page) {
			$GLOBALS['TSFE']->sys_page->versionOL('pages', $page);
			if (is_array($page)) {
				$page = $GLOBALS['TSFE']->sys_page->getPageOverlay($page);
			}
		}

		return $pages;
	}

	public function robotsAction($content, array $params) {
		$sitemapLink = $this->cObj->typoLink_URL([
			'parameter' => $GLOBALS['TSFE']->rootLine[0]['uid'] . ',150',
			'forceAbsoluteUrl' => true,
		]);
		return implode("\n", [
			'User-agent: *',
			'Disallow:',
			'Sitemap: ' . $sitemapLink,
		]);
	}
}
