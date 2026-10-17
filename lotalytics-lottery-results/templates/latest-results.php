<?php
/**
 * Latest results of one game.
 *
 * Override by copying this file to yourtheme/lotalytics/latest-results.php.
 *
 * Available: $data (game, drawings, results_url, next_draw), $args (game, draws,
 * compact, show_title, class), $wrapper (block wrapper attributes, already escaped).
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lotalytics_game  = $data['game'];
$lotalytics_class = trim( 'ltc-results' . ( $args['compact'] ? ' ltc-results--compact' : '' ) . ' ' . $args['class'] );
$lotalytics_bonus = ! empty( $lotalytics_game['bonus_label'] ) ? $lotalytics_game['bonus_label'] : __( 'Bonus', 'lotalytics-lottery-results' );
?>
<div <?php echo $wrapper ? $wrapper : 'class="' . esc_attr( $lotalytics_class ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block wrapper attributes are escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( $args['show_title'] ) : ?>
		<p class="ltc-results__title">
			<?php
			/* translators: %s: lottery game name, e.g. "Powerball". */
			echo esc_html( sprintf( __( '%s results', 'lotalytics-lottery-results' ), $lotalytics_game['name'] ) );
			?>
		</p>
	<?php endif; ?>
	<ol class="ltc-draws">
		<?php foreach ( $data['drawings'] as $lotalytics_draw ) : ?>
			<li class="ltc-draw">
				<span class="ltc-draw__date"><?php echo esc_html( Renderer::date( $lotalytics_draw['date'] ) ); ?></span>
				<ol class="ltc-balls" aria-label="<?php esc_attr_e( 'Winning numbers', 'lotalytics-lottery-results' ); ?>">
					<?php
					foreach ( (array) $lotalytics_draw['numbers'] as $lotalytics_n ) {
						echo Renderer::ball( $lotalytics_n ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::ball().
					}
					?>
				</ol>
				<?php if ( ! empty( $lotalytics_draw['bonus_numbers'] ) ) : ?>
					<ol class="ltc-balls ltc-balls--bonus" aria-label="<?php echo esc_attr( $lotalytics_bonus ); ?>">
						<?php
						foreach ( (array) $lotalytics_draw['bonus_numbers'] as $lotalytics_n ) {
							echo Renderer::ball( $lotalytics_n, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::ball().
						}
						?>
					</ol>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php if ( ! empty( $data['next_draw'] ) && ! $args['compact'] ) : ?>
		<p class="ltc-next">
			<?php
			/* translators: %s: date of the next draw. */
			echo esc_html( sprintf( __( 'Next draw: %s', 'lotalytics-lottery-results' ), Renderer::date( $data['next_draw'] ) ) );
			?>
		</p>
	<?php endif; ?>
	<?php echo Renderer::credit( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::credit(). ?>
</div>
