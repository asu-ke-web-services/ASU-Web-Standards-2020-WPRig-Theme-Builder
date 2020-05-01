<?php
/**
 * WP_Rig\WP_Rig\Customizer\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Customizer;

use WP_Rig\WP_Rig\Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use WP_Customize_Manager;
use WP_Customize_Control;
use function add_action;
use function bloginfo;
use function wp_enqueue_script;
use function get_theme_file_uri;

/**
 * Class for managing Customizer integration.
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'customizer';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'customize_register', [ $this, 'action_customize_register' ] );
		add_action( 'customize_preview_init', [ $this, 'action_enqueue_customize_preview_js' ] );
	}

	/**
	 * Adds postMessage support for site title and description, plus a custom Theme Options section.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				[
					'selector'        => '.site-title a',
					'render_callback' => function() {
						bloginfo( 'name' );
					},
				]
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				[
					'selector'        => '.site-description',
					'render_callback' => function() {
						bloginfo( 'description' );
					},
				]
			);
		}

		/**
		 * ASU global header options panel.
		 * Allows an end user to specifiy if the site has a parent site and provide a name and an URL if so.
		 */

		$wp_customize->add_panel(
			'wordpress_asu_theme_panel',
			array(
				'title'      => __( 'ASU Branding Elements', 'wp-rig' ),
				'priority'   => 1,
			)
		);

		$wp_customize->add_setting(
			'wordpress_asu_theme_options[parent]',
			array(
				'default'           => '',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'subsite',
				array(
					'label'      => __( 'Does this site have a parent?', 'wp-rig' ),
					'section'    => 'wordpress_asu_theme_section_subsite_settings',
					'settings'   => 'wordpress_asu_theme_options[parent]',
					'type'       => 'checkbox',
				)
			)
		);

		$wp_customize->add_section(
			'wordpress_asu_theme_section_subsite_settings',
			array(
				'title'      => __( 'Global Header', 'wp-rig' ),
				'priority'   => 10,
				'panel'      => 'wordpress_asu_theme_panel',
			)
		);

		$wp_customize->add_setting(
			'wordpress_asu_theme_options[parent_site_name]',
			array(
				'default'           => '',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'parent_blog_name',
				array(
					'label'      => __( 'Parent Site Name', 'wp-rig' ),
					'section'    => 'wordpress_asu_theme_section_subsite_settings',
					'settings'   => 'wordpress_asu_theme_options[parent_site_name]',
				)
			)
		);

		$wp_customize->add_setting(
			'wordpress_asu_theme_options[parent_url]',
			array(
				'default'           => '',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'parent_blog_id',
				array(
					'label'      => __( 'Parent Site URL', 'wp-rig' ),
					'section'    => 'wordpress_asu_theme_section_subsite_settings',
					'settings'   => 'wordpress_asu_theme_options[parent_url]',
				)
			)
		);
	}

	/**
	 * Enqueues JavaScript to make Customizer preview reload changes asynchronously.
	 */
	public function action_enqueue_customize_preview_js() {
		wp_enqueue_script(
			'wp-rig-customizer',
			get_theme_file_uri( '/assets/js/customizer.min.js' ),
			[ 'customize-preview' ],
			wp_rig()->get_asset_version( get_theme_file_path( '/assets/js/customizer.min.js' ) ),
			true
		);
	}
}
