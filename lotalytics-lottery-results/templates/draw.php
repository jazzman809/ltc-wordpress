<?php
/**
 * One draw (/lottery/<game>/<date>/).
 *
 * Override by copying this file to yourtheme/lotalytics/draw.php.
 *
 * Available: $data (/v1 single draw: game, name, bonus_label, drawing,
 * previous, next, next_draw).
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Renderer;
use Lotalytics\Results\Results_Section;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lotalytics_d     = $data['drawing'];
$lotalytics_bonus = ! empty( $data['bonus_label'] ) ? $data['bonus_label'] : __( 'Bonus', 'lotalytics-lottery-results' );
?>
<div class="ltc-results ltc-draw-page">
	<h1 class="ltc-results__title">
		<?php
		/* translators: 1: lottery game name, 2: draw date. */
		echo esc_html( sprintf( __( '%1$s numbers for %2$s', 'lotalytics-lottery-results' ), $data['name'], Renderer::date( $lotalytics_d['date'] ) ) );
		?>
	</h1>
	<div class="ltc-draw">
		<ol class="ltc-balls" aria-label="<?php esc_attr_e( 'Winning numbers', 'lotalytics-lottery-results' ); ?>">
			<?php
			foreach ( (array) $lotalytics_d['numbers'] as $lotalytics_n ) {
				echo Renderer::ball( $lotalytics_n ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::ball().
			}
			?>
		</ol>
		<?php if ( ! empty( $lotalytics_d['bonus_numbers'] ) ) : ?>
			<ol class="ltc-balls ltc-balls--bonus" aria-label="<?php echo esc_attr( $lotalytics_bonus ); ?>">
				<?php
				foreach ( (array) $lotalytics_d['bonus_numbers'] as $lotalytics_n ) {
					echo Renderer::ball( $lotalytics_n, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::ball().
				}
				?>
			</ol>
		<?php endif; ?>
	</div>
	<nav class="ltc-pager" aria-label="<?php esc_attr_e( 'Other draws', 'lotalytics-lottery-results' ); ?>">
		<?php if ( ! empty( $data['previous'] ) ) : ?>
			<a class="ltc-pager__prev" href="<?php echo esc_url( Results_Section::url( $data['game'], $data['previous'] ) ); ?>"><?php echo esc_html( Renderer::date( $data['previous'] ) ); ?></a>
		<?php endif; ?>
		<a class="ltc-pager__count" href="<?php echo esc_url( Results_Section::url( $data['game'] ) ); ?>">
			<?php
			/* translators: %s: lottery game name. */
			echo esc_html( sprintf( __( 'All %s results', 'lotalytics-lottery-results' ), $data['name'] ) );
			?>
		</a>
		<?php if ( ! empty( $data['next'] ) ) : ?>
			<a class="ltc-pager__next" href="<?php echo esc_url( Results_Section::url( $data['game'], $data['next'] ) ); ?>"><?php echo esc_html( Renderer::date( $data['next'] ) ); ?></a>
		<?php endif; ?>
	</nav>
	<?php if ( ! empty( $data['next_draw'] ) ) : ?>
		<p class="ltc-next">
			<?php
			/* translators: %s: date of the next draw. */
			echo esc_html( sprintf( __( 'Next draw: %s', 'lotalytics-lottery-results' ), Renderer::date( $data['next_draw'] ) ) );
			?>
		</p>
	<?php endif; ?>
	<?php echo Renderer::credit( array( 'game' => array( 'name' => $data['name'] ), 'results_url' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::credit(). ?>
</div>
