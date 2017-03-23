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

	public function generateAction($content, array $params) {
		$this->settings = $params['settings.'];

		$typolinkRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

		$this->urls = [];

		$page = $GLOBALS['TSFE']->sys_page->getPage($GLOBALS['TSFE']->rootLine[0]['uid']);
		$result = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$result .= $this->renderPages([$page]);

		foreach ($this->settings['additionalLinks.'] as $configuration) {
			$records = $this->cObj->getRecords($configuration['records.']['table'], $configuration['records.']['select.']);
			$typolinkConfiguration = $configuration['typolink.'];
			$typolinkConfiguration['returnLast'] = 'url';
			$typolinkConfiguration = ['typolink.' => $typolinkConfiguration];
			foreach ($records as $record) {
				$typolinkRenderer->start($record, $configuration['records.']['table']);
				$url = $typolinkRenderer->cObjGetSingle('TEXT', $typolinkConfiguration);
				if ($url && !in_array($url, $this->urls)) {
					$this->urls[] = $urls;
					$result .= '<url><loc>' . $url . '</loc>';
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

	protected function renderPages(array $pages = []) {
		$result = '';
		foreach ($pages as $page) {
			if ($GLOBALS['TSFE']->sys_language_uid && !$page['_PAGES_OVERLAY']) {
				continue;
			}

			$url = $this->cObj->typoLink_URL([
				'parameter' => $page['uid'],
				'forceAbsoluteUrl' => true,
			]);
			$this->urls[] = $url;

			$result .= '<url><loc>' . $url . '</loc>';
			if ($page['SYS_LASTCHANGED']) {
				$result .= '<lastmod>' . date('c', $page['SYS_LASTCHANGED']) . '</lastmod>';
			}
			$result .= '</url>';

			$subpages = $GLOBALS['TSFE']->sys_page->getMenu($page['uid'], '*', 'sorting', 'AND no_search=0 AND doktype IN (' . $this->settings['includeDoktypes'] . ')');
			$result .= $this->renderPages($subpages);
		}

		return $result;
	}

	public function robotsAction($content, array $params) {
		$sitemapLink = $this->cObj->getTypoLink_URL($GLOBALS['TSFE']->rootLine[0]['uid'] . ',150');
		return implode("\n", [
			'User-agent: *',
			'Disallow:',
			'Sitemap: ' . $sitemapLink,
		]);
	}
}
