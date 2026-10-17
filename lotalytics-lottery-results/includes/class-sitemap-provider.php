<?php
/**
 * The results section in wp-sitemap.xml: the section, each game, and each
 * game's latest draws (one cached API call per game).
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap provider "lotalytics".
 */
class Sitemap_Provider extends \WP_Sitemaps_Provider {

	const DRAWS_PER_GAME = 30;

	/**
	 * Set up.
	 */
	public function __construct() {
		$this->name        = 'lotalytics';
		$this->object_type = 'lotalytics';
	}

	/**
	 * URLs on one sitemap page.
	 *
	 * @param int    $page_num       Page.
	 * @param string $object_subtype Unused.
	 * @return array
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		if ( 1 !== (int) $page_num || ! Results_Section::enabled() ) {
			return array();
		}
		$urls = array( array( 'loc' => Results_Section::url() ) );
		foreach ( Results_Section::games() as $g ) {
			$data = Api_Client::drawings( $g['slug'], 1, self::DRAWS_PER_GAME );
			if ( ! $data || empty( $data['drawings'] ) ) {
				continue;
			}
			$urls[] = array( 'loc' => Results_Section::url( $g['slug'] ) );
			foreach ( $data['drawings'] as $d ) {
				$urls[] = array(
					'loc'     => Results_Section::url( $g['slug'], $d['date'] ),
					'lastmod' => $d['date'] . 'T00:00:00+00:00',
				);
			}
		}
		return $urls;
	}

	/**
	 * Pages in this sitemap.
	 *
	 * @param string $object_subtype Unused.
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		return Results_Section::enabled() ? 1 : 0;
	}
}
