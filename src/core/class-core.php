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

		public function __construct() {

		}

		function init( $initial_params = array() ) {
			if ( self::$loaded ) {
				return;
			}
			self::$loaded         = true;
			$this->factory = new Container_Factory();
			$this->initial_params = wp_parse_args( $initial_params, array(
				'origin_filesystem_path' => ''
			) );
			//$this->generate_containers();
			add_action('init',array($this,'generate_containers'));
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		function generate_containers() {
			$this->containers = apply_filters( 'plf_containers', array() );
			foreach ( $this->containers as $key=>$container_args ) {
				$container_args = wp_parse_args( $container_args, array(
					'id' => $key,
				) );
				$class_name = isset( $container_args['class_name'] ) ? $container_args['class_name'] : '';
				$container      = $this->factory->create( $container_args['type'], $container_args, $class_name );
				$container->init();
				$this->containers[] = $container;
			}

			/*$container_args = wp_parse_args( $container_args, array(
					'type'       => 'options_page',
					'page_title' => 'Plastic',
					'menu'       => array(
						'capability' => 'manage_options',
						'menu_title' => '',
					)
				) );*/
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
				$instance     = new static();
			}

			return $instance;
		}
	}
}