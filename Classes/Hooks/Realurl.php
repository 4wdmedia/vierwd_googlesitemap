<?php

namespace Vierwd\VierwdGooglesitemap\Hooks;

class Realurl {

	/**
	 * Generates additional RealURL configuration and merges it with provided configuration
	 *
	 * @param array $params Default configuration
	 * @param tx_realurl_autoconfgen $pObjParent object
	 * @return array Updated configuration
	 */
	public function addConfig($params, &$pObj) {
		return array_merge_recursive($params['config'], [
			'fileName' => [
				'index' => [
					'sitemap.xml' => [
						'keyValues' => [
							'type' => 150,
						],
					],
					'robots.txt' => [
						'keyValues' => [
							'type' => 151,
						],
					],
				],
			],
		]);
	}
}
