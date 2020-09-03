<?php
/**
 * WP Plastic Fields - Core
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields;


use WP_Marvelous\WP_Plastic_Fields\Container\Container;
use WP_Marvelous\WP_Plastic_Fields\Factory\Container_Factory;
use WP_Marvelous\WP_Plastic_Fields\Factory\Field_Factory;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Core' ) ) {
	class Core {

		public static $loaded = false;
		public $initial_params = array();
		public $containers = array();

		/**
		 * @var Container_Factory
		 */
		protected $factory;

		function init( $initial_params = array() ) {
			if ( self::$loaded ) {
				return;
			}
			self::$loaded         = true;
			$this->factory        = new Container_Factory();
			$this->initial_params = wp_parse_args( $initial_params, array(
				'origin_filesystem_path' => ''
			) );
			add_action( 'init', array( $this, 'generate_containers' ) );
			add_action( 'plf_containers_ready', array( $this, 'handle_navigation_and_fields_groups' ) );
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		function handle_navigation_and_fields_groups() {
			foreach ( $this->containers as $container_id => $container_args ) {
				add_action( "plf_container_{$container_id}_set_navigation", array( $this, 'generate_navigation' ) );
				add_action( "plf_container_{$container_id}_set_fields_groups", array( $this, 'generate_fields_groups' ) );
			}
		}

		function generate_fields_groups( Container $container ) {
			$fields_groups = apply_filters( "plf_container_{$container->id}_fields_groups", array() );
			foreach ( $fields_groups as $field_group_arg ) {
				$container->add_fields_group( $field_group_arg );
			}
		}

		function generate_navigation( Container $container ) {
			$container_args = apply_filters( "plf_container_{$container->id}_navigation", array() );
			if ( ! empty( $container_args ) ) {
				$container->set_navigation( new Navigation( $container_args ) );
			}
		}

		function generate_containers() {
			$this->containers = apply_filters( 'plf_containers', array() );
			foreach ( $this->containers as $key => $container_args ) {
				$container_args = wp_parse_args( $container_args, array(
					'id' => $key,
				) );
				$class_name     = isset( $container_args['class_name'] ) ? $container_args['class_name'] : '';
				$container      = $this->factory->create( $container_args['type'], $container_args, $class_name );
				$container->init();
				$this->containers[] = $container;
			}
			do_action('plf_containers_ready');
		}

		function enqueue_scripts() {
			wp_enqueue_style( 'wpf-style', plugins_url( 'assets/admin.css', $this->initial_params['origin_filesystem_path'] ) );
		}

		/**
		 * Singleton implementation.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return Core
		 */
		public static function instance() {
			static $instance = null;
			if ( $instance === null ) {
				$instance = new static();
			}

			return $instance;
		}
	}
}