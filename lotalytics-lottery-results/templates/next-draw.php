<?php
/**
 * The next scheduled draw of one game.
 *
 * Override by copying this file to yourtheme/lotalytics/next-draw.php.
 *
 * Available: $data, $args, $wrapper (see latest-results.php).
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lotalytics_class = trim( 'ltc-next-draw ' . $args['class'] );
?>
<div <?php echo $wrapper ? $wrapper : 'class="' . esc_attr( $lotalytics_class ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block wrapper attributes are escaped by get_block_wrapper_attributes(). ?>>
	<p class="ltc-next-draw__label">
		<?php
		/* translators: %s: lottery game name, e.g. "Powerball". */
		echo esc_html( sprintf( __( 'Next %s draw', 'lotalytics-lottery-results' ), $data['game']['name'] ) );
		?>
	</p>
	<p class="ltc-next-draw__date">
		<time datetime="<?php echo esc_attr( $data['next_draw'] ); ?>"><?php echo esc_html( Renderer::date( $data['next_draw'] ) ); ?></time>
	</p>
	<?php echo Renderer::credit( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Renderer::credit(). ?>
</div>
