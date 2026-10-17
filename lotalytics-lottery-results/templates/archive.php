<?php
/**
 * A page of a game's history (the archive block, and /lottery/<game>/).
 *
 * Override by copying this file to yourtheme/lotalytics/archive.php.
 *
 * Available: $data (/v1 drawings: game, name, bonus_label, drawings, page,
 * total, next_draw), $args (class, in_section, draw_links), $wrapper,
 * $nav (prev, next, page, of).
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Renderer;
use Lotalytics\Results\Results_Section;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lotalytics_class = trim( 'ltc-results ltc-archive ' . $args['class'] );
$lotalytics_bonus = ! empty( $data['bonus_label'] ) ? $data['bonus_label'] : __( 'Bonus', 'lotalytics-lottery-results' );
$lotalytics_tag   = $args['in_section'] ? 'h1' : 'p';
?>
<div <?php echo $wrapper ? $wrapper : 'class="' . esc_attr( $lotalytics_class ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block wrapper attributes are escaped by get_block_wrapper_attributes(). ?>>
	<<?php echo esc_attr( $lotalytics_tag ); ?> class="ltc-results__title">
		<?php
		/* translators: %s: lottery game name, e.g. "Powerball". */
		echo esc_html( sprintf( __( '%s results', 'lotalytics-lottery-results' ), $data['name'] ) );
		?>
	</<?php echo esc_attr( $lotalytics_tag ); ?>>
	<ol class="ltc-draws">
		<?php foreach ( $data['drawings'] as $lotalytics_draw ) : ?>
			<li class="ltc-draw">
				<span class="ltc-draw__date">
					<?php if ( $args['draw_links'] ) : ?>
						<a href="<?php echo esc_url( Results_Section::url( $data['game'], $lotalytics_draw['date'] ) ); ?>"><?php echo esc_html( Renderer::date( $lotalytics_draw['date'] ) ); ?></a>
					<?php else : ?>
						<?php echo esc_html( Renderer::date( $lotalytics_draw['date'] ) ); ?>
					<?php endif; ?>
				</span>
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
	<?php if ( $nav['of'] > 1 ) : ?>
		<nav class="ltc-pager" aria-label="<?php esc_attr_e( 'Results pages', 'lotalytics-lottery-results' ); ?>">
			<?php if ( $nav['prev'] ) : ?>
				<a class="ltc-pager__prev" href="<?php echo esc_url( $nav['prev'] ); ?>"><?php esc_html_e( 'Newer draws', 'lotalytics-lottery-results' ); ?></a>
			<?php endif; ?>
			<span class="ltc-pager__count">
				<?php
				/* translators: 1: current page, 2: number of pages. */
				echo esc_html( sprintf( __( 'Page %1$d of %2$d', 'lotalytics-lottery-results' ), $nav['page'], $nav['of'] ) );
				?>
			</span>
			<?php if ( $nav['next'] ) : ?>
				<a class="ltc-pager__next" href="<?php echo esc_url( $nav['next'] ); ?>"><?php esc_html_e( 'Older draws', 'lotalytics-lottery-results' ); ?></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
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
