<?php
/**
 * The `wp_rig()` function.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

/**
 * Provides access to all available template tags of the theme.
 *
 * When called for the first time, the function will initialize the theme.
 *
 * @return Template_Tags Template tags instance exposing template tag methods.
 */
function wp_rig() : Template_Tags {
	static $theme = null;

	if ( null === $theme ) {
		$theme = new Theme();
		$theme->initialize();
	}

	return $theme->template_tags();
}

/**
 * Adds support for ASU Global Header and Footer Elements
 */
function asuwp_load_global_head_scripts() {
	$request  = wp_remote_get( 'http://www.asu.edu/asuthemes/4.8/heads/default.shtml' );
	$response = wp_remote_retrieve_body( $request );
	echo $response;
};

/**
 * Build header parent site name based on customizer settings
 */
function asuwp_load_header_sitenames() {

	if ( is_array( get_option( 'wordpress_asu_theme_options' ) ) ) {
		$theme_options = get_option( 'wordpress_asu_theme_options' );
	}

	if ( isset( $theme_options ) && $theme_options['parent'] ) {
		// Check box is true.
		$parent = '<a href="%1$s" id="parent-site">%2$s</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
		$parent = sprintf( $parent, esc_html( $theme_options['parent_url'] ), $theme_options['parent_site_name'] );
	} else {
		$parent = '';
	}

	return $parent;
};

/**
 * Remote get ASU global header elements. Print site name along with returned code.
 */
function asuwp_load_global_header() {
	$request  = wp_remote_get( 'http://www.asu.edu/asuthemes/4.8/headers/default.shtml' );
	$response = wp_remote_retrieve_body( $request );

	$parent = asuwp_load_header_sitenames();

	$response .= '<div id="sitename-wrapper"><div class="sitename">' . $parent . '<a href="' . get_home_url() . '" title="Home" rel="home" id="current-site">' . get_bloginfo( 'name' ) . '</a></div></div>';
	echo $response;
}
