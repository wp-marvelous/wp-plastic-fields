<?php
/**
 * Plugin Name: WP Plastic Fields
 * Description: WP Plastic Fields
 * Version: 1.0.0
 * Author: WP Marvelous
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-plastic-fields
 * Domain Path: /src/languages
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0
 */

use \WP_Marvelous\WP_Plastic_Fields\Container\Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

require_once "vendor/autoload.php";

// Container
$container = new Container( array(
	'id'         => 'plastic_slug',
	'type'       => 'options_page',
	'page_title' => 'Plastic',
	'menu'       => array(
		'capability' => 'manage_options',
		'menu_title' => 'Plastic',
	),
) );
$container->init();

// Navigation
add_action( 'plf_container_' . 'plastic_slug' . '_navigation', function ( Container $container ) {
	$container->set_navigation( new \WP_Marvelous\WP_Plastic_Fields\Navigation( array(
		'levels' => array(
			array(
				'level' => 0,
				'items' => array(
					array( 'id' => 'first_tab', 'title' => 'First Tab' ),
					array( 'id' => 'second_tab', 'title' => 'Second Tab' ),
				)
			),
			array(
				'level' => 1,
				'path'  => array( 'first_tab' ),
				'items' => array(
					array( 'id' => 'first_sub', 'title' => 'First Sub' ),
					array( 'id' => 'second', 'title' => 'Second Sub' ),
				)
			),
			array(
				'level' => 1,
				'path'  => array( 'second_tab' ),
				'items' => array(
					array( 'id' => 'blabla', 'title' => 'BLABLA' ),
					array( 'id' => 'clacla', 'title' => 'CLACLA' ),
				)
			),
			array(
				'level' => 2,
				'path'  => array( 'second_tab', 'clacla' ),
				'items' => array(
					array( 'id' => 'first_sub_sub', 'title' => 'First Sub Sub' ),
					array( 'id' => 'second_sub_sub', 'title' => 'Second Sub Sub' ),
				)
			),
		)
	) ) );
} );

// Fields
add_action( 'plf_container_' . 'plastic_slug' . '_fields_groups', function ( Container $container ) {
	$container->add_fields_group( array(
		'navigation' => array(
			'path' => 'first_tab > first_sub', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
			//'path' => 'second_tab > clacla > first_sub_sub', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
		),
		'fields'     => array(
			array(
				'title'    => __( 'A great title', 'wp-concept-fields' ),
				'id'       => 'great_title',
				'desc'     => __( 'Field description 2', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'  => 'a',
				'type'     => 'title',
			),
			array(
				'title'    => __( 'Field Title', 'wp-concept-fields' ),
				'id'       => 'first_field',
				'desc'     => __( 'Field description', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip', 'wp-concept-fields' ),
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'       => __( 'Field Title 3 Cool', 'wp-concept-fields' ),
				'id'          => 'field_3',
				'desc'        => __( 'Field description 3', 'wp-concept-fields' ),
				'desc_tip'    => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'     => 'b',
				'type'        => 'text',
			),
			array(
				'title'    => __( 'Field Title 4', 'wp-concept-fields' ),
				'id'       => 'field_4',
				'desc'     => __( 'Field description 4', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 4', 'wp-concept-fields' ),
				'default'  => 'c',
				'type'     => 'text',
			),
		)
	) );
} );

//CNCF();

/*CNCF()->container()->add( 'lll', array(
		'type' => 'options_page',
		'menu' => array(
			'capability' => 'manage_options',
			'menu_title' => 'ABC'
		),
		'navigation' => array(
			array(
				'level'   => 0,
				'items'   => array(
					array( 'id' => 'first_tab', 'title' => 'First Tab' ),
					array( 'id' => 'second_tab', 'title' => 'Second Tab' ),
				)
			),
			array(
				'level'   => 1,
				'parent'  => array( 'first_tab' ),
				'items'   => array(
					array( 'id' => 'first_sub', 'title' => 'First Sub' ),
					array( 'id' => 'second', 'title' => 'Second Sub' ),
				)
			),
			array(
				'level'   => 1,
				'parent'  => array( 'second_tab' ),
				'items'   => array(
					array( 'id' => 'blabla', 'title' => 'BLABLA' ),
					array( 'id' => 'clacla', 'title' => 'CLACLA' ),
				)
			),
			array(
				'level'   => 2,
				'parent'  => array( 'second_tab', 'clacla' ),
				'items'   => array(
					array( 'id' => 'first_sub_sub', 'title' => 'First Sub Sub' ),
					array( 'id' => 'second_sub_sub', 'title' => 'Second Sub Sub' ),
				)
			),
		)
	)
);

CNCF()->fields()->add_group( array(
	'container'  => 'lll',
	'navigation' => array(
		'path' => array( 'second_tab', 'clacla', 'first_sub_sub' )
	),
	'fields'     => array(
		array(
			'title'    => __( 'Field Title', 'wp-concept-fields' ),
			'id'       => 'first_field',
			'desc'     => __( 'Field description', 'wp-concept-fields' ),
			'desc_tip' => __( 'Field tip', 'wp-concept-fields' ),
			'default'  => 'yes',
			'type'     => 'checkbox',
		),
		array(
			'title'    => __( 'Field Title 2', 'wp-concept-fields' ),
			'id'       => 'second_field',
			'desc'     => __( 'Field description 2', 'wp-concept-fields' ),
			'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
			'default'  => 'abc',
			'type'     => 'text',
		),
	)
) );

CNCF()->fields()->add_group( array(
	'container'  => 'lll',
	'navigation' => array(
		'path' => 'first_tab'
	),
	'fields'     => array(
		array(
			'title'    => __( 'Field Title', 'wp-concept-fields' ),
			'id'       => 'first_field3',
			'desc'     => __( 'Field description', 'wp-concept-fields' ),
			'desc_tip' => __( 'Field tip', 'wp-concept-fields' ),
			'default'  => 'yes',
			'type'     => 'checkbox',
		),
	)
) );
*/