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
use function \WP_Marvelous\WP_Plastic_Fields\PLF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

require_once "vendor/autoload.php";


PLF()->init( array(
	'origin_filesystem_path' => __FILE__
) );

// Containers
add_filter( 'plf_containers', function ( $containers ) {
	$containers['plastic_slug'] = array(
		'type'       => 'options_page',
		'page_title' => 'Plastic',
		'menu'       => array(
			'capability' => 'manage_options',
			'menu_title' => 'Plastic',
		)
	);
	return $containers;
} );

// Navigation
add_filter( 'plf_container_' . 'plastic_slug' . '_navigation', function ( $navigation ) {
	$navigation = array(
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
	);
	return $navigation;
} );

// Fields
add_filter( 'plf_container_' . 'plastic_slug' . '_fields_groups', function ( $fields_groups ) {
	$fields_groups[] = array(
		'navigation' => array(
			'path' => 'first_tab > first_sub', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
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
				'title'    => __( 'My checkbox', 'wp-concept-fields' ),
				'id'       => 'first_field',
				'desc'     => __( 'Field description', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip', 'wp-concept-fields' ),
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Field Title 3 Cool', 'wp-concept-fields' ),
				'id'       => 'field_3',
				'desc'     => __( 'Field description 3', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'  => 'b',
				'type'     => 'text',
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
	);

	$fields_groups[] = array(
		'navigation' => array(
			'path' => 'first_tab > first_sub', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
		),
		'fields'     => array(
			array(
				'title'    => __( 'A great title 2', 'wp-concept-fields' ),
				'id'       => 'great_title_2',
				'desc'     => __( 'Field description 2', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'  => 'a',
				'type'     => 'title',
			),
			array(
				'title'    => __( 'My checkbox', 'wp-concept-fields' ),
				'id'       => 'another_field',
				'desc'     => __( 'Field description', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip dd', 'wp-concept-fields' ),
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
		)
	);
	return $fields_groups;
} );

// Containers
add_filter( 'plf_containers', function ( $containers ) {
	$containers['plf_woo'] = array(
		'type' => 'woo_options_page',
	);
	return $containers;
} );

// Navigation
add_filter( 'plf_container_' . 'plf_woo' . '_navigation', function ( $navigation ) {
	$navigation = array(
		'levels' => array(
			array(
				'level' => 0,
				'items' => array(
					array( 'id' => 'woo_test', 'title' => 'Woo Test' ),
				)
			),
			array(
				'level' => 1,
				'path'  => array( 'woo_test' ),
				'items' => array(
					array( 'id' => 'first_section', 'title' => 'First Section' ),
					array( 'id' => 'second_section', 'title' => 'Second Section' ),
				)
			),
		)
	);
	return $navigation;
} );

// Fields
add_filter( 'plf_container_' . 'plf_woo' . '_fields_groups', function ( $fields_groups ) {
	$fields_groups[] = array(
		'navigation' => array(
			'path' => 'woo_test > first_section', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
		),
		'fields'     => array(
			array(
				'title'    => __( 'A great title', 'wp-concept-fields' ),
				'id'       => 'great_title_a',
				'desc'     => __( 'Field description 2', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'type'     => 'title',
			),
			array(
				'title'    => __( 'My checkbox', 'wp-concept-fields' ),
				'id'       => 'first_field_b',
				'desc'     => __( 'Field description', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip', 'wp-concept-fields' ),
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Field Title 3 Cool', 'wp-concept-fields' ),
				'id'       => 'field_3_d',
				'desc'     => __( 'Field description 3', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'  => 'b',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Field Title 4', 'wp-concept-fields' ),
				'id'       => 'field_4_e',
				'desc'     => __( 'Field description 4', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 4', 'wp-concept-fields' ),
				'default'  => 'c',
				'type'     => 'text',
			),
		)
	);

	$fields_groups[] = array(
		'navigation' => array(
			'path' => 'woo_test > second_section', // or array( 'second_tab', 'clacla', 'first_sub_sub' ),
		),
		'fields'     => array(
			array(
				'title'    => __( 'A great title 2', 'wp-concept-fields' ),
				'id'       => 'great_title_2',
				'desc'     => __( 'Field description 2', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip 2', 'wp-concept-fields' ),
				'default'  => 'a',
				'type'     => 'title',
			),
			array(
				'title'    => __( 'My checkbox', 'wp-concept-fields' ),
				'id'       => 'another_field',
				'desc'     => __( 'Field description', 'wp-concept-fields' ),
				'desc_tip' => __( 'Field tip dd', 'wp-concept-fields' ),
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
		)
	);
	return $fields_groups;
} );