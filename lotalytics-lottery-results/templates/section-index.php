<?php
/**
 * The results section's front page (/lottery/): the games.
 *
 * Override by copying this file to yourtheme/lotalytics/section-index.php.
 *
 * Available: $data['games'] (list of ['slug', 'name']).
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Results_Section;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ltc-results ltc-section-index">
	<h1 class="ltc-results__title"><?php esc_html_e( 'Lottery results', 'lotalytics-lottery-results' ); ?></h1>
	<ul class="ltc-games">
		<?php foreach ( $data['games'] as $lotalytics_g ) : ?>
			<li><a href="<?php echo esc_url( Results_Section::url( $lotalytics_g['slug'] ) ); ?>"><?php echo esc_html( $lotalytics_g['name'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
