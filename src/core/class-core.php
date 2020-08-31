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
use WP_Marvelous\WP_Plastic_Fields\Factory\Field_Factory;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Core' ) ) {
	class Core {

		public function __construct() {
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