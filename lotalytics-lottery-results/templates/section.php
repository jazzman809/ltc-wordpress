<?php
/**
 * The page wrapper for the results section (/lottery/…): the theme's header and
 * footer around the section's content, for classic and block themes.
 *
 * Override by copying this file to yourtheme/lotalytics/section.php.
 *
 * @package Lotalytics\Results
 */

use Lotalytics\Results\Results_Section;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lotalytics_theme_file = locate_template( array( 'lotalytics/section.php' ) );
if ( $lotalytics_theme_file && realpath( $lotalytics_theme_file ) !== realpath( __FILE__ ) ) {
	include $lotalytics_theme_file;
	return;
}

if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) :
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'lotalytics-section' ); ?>>
	<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php block_template_part( 'header' ); ?>
	<main class="wp-block-group is-layout-constrained lotalytics-section__main">
		<?php echo Results_Section::content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the templates. ?>
	</main>
	<?php block_template_part( 'footer' ); ?>
</div>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
else :
	get_header();
	?>
<main id="primary" class="site-main lotalytics-section__main">
	<?php echo Results_Section::content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the templates. ?>
</main>
	<?php
	get_footer();
endif;
