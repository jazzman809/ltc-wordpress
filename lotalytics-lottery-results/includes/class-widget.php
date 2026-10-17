<?php
/**
 * Classic widget, for themes that still use widget areas.
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Lottery results" classic widget.
 */
class Widget extends \WP_Widget {

	/**
	 * Register the widget.
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}

	/**
	 * Set up.
	 */
	public function __construct() {
		parent::__construct(
			'lotalytics_results',
			__( 'Lottery results (Lotalytics)', 'lotalytics-lottery-results' ),
			array(
				'description'                 => __( 'The latest winning numbers of a lottery.', 'lotalytics-lottery-results' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Front end.
	 *
	 * @param array $args     Sidebar arguments.
	 * @param array $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$html = Renderer::latest_results(
			array(
				'game'       => ! empty( $instance['game'] ) ? $instance['game'] : Settings::default_game(),
				'draws'      => isset( $instance['draws'] ) ? $instance['draws'] : 3,
				'compact'    => ! empty( $instance['compact'] ),
				'show_title' => empty( $instance['title'] ),
			)
		);
		if ( '' === $html ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided markup.
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided markup.
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided markup.
	}

	/**
	 * Admin form.
	 *
	 * @param array $instance Settings.
	 * @return string
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'   => '',
				'game'    => Settings::default_game(),
				'draws'   => 3,
				'compact' => 0,
			)
		);
		$games    = Api_Client::games();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional)', 'lotalytics-lottery-results' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'game' ) ); ?>"><?php esc_html_e( 'Game', 'lotalytics-lottery-results' ); ?></label>
			<?php if ( $games ) : ?>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'game' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'game' ) ); ?>">
					<?php foreach ( $games as $g ) : ?>
						<option value="<?php echo esc_attr( $g['slug'] ); ?>" <?php selected( $instance['game'], $g['slug'] ); ?>><?php echo esc_html( $g['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'game' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'game' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['game'] ); ?>">
			<?php endif; ?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'draws' ) ); ?>"><?php esc_html_e( 'Number of draws', 'lotalytics-lottery-results' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'draws' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'draws' ) ); ?>" type="number" min="1" max="5" value="<?php echo esc_attr( (string) (int) $instance['draws'] ); ?>">
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'compact' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'compact' ) ); ?>" value="1" <?php checked( 1, (int) $instance['compact'] ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'compact' ) ); ?>"><?php esc_html_e( 'Compact (hide the next draw date)', 'lotalytics-lottery-results' ); ?></label>
		</p>
		<?php
		return '';
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'   => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
			'game'    => sanitize_key( isset( $new_instance['game'] ) ? $new_instance['game'] : '' ),
			'draws'   => max( 1, min( 5, (int) ( isset( $new_instance['draws'] ) ? $new_instance['draws'] : 3 ) ) ),
			'compact' => empty( $new_instance['compact'] ) ? 0 : 1,
		);
	}
}
