<?php
/**
 * WP Plastic Fields - Container Factory
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields\Factory;

use WP_Marvelous\WP_Plastic_Fields\Container\Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Factory\Container_Factory' ) ) {
	class Container_Factory extends Factory {

		public function create( $type, $field_args = null, $class_name = '' ) {
			if ( empty( $class_name ) ) {
				$class_name = $this->generate_class_name( 'WP_Marvelous\WP_Plastic_Fields', "Container\\" . $type . "_Container" );
			}
			if ( ! class_exists( $class_name ) ) {
				throw new \Exception( sprintf( __( 'Class %s doesn\'t exist', 'wp-concept-fields' ), $class_name ) );
			}
			if ( ! is_subclass_of( $class_name, Container::class ) ) {
				throw new \Exception( sprintf( __( 'Class %s must inherit %s', 'wp-concept-fields' ), $class_name, Container::class ) );
			}
			$container = new $class_name( ...array( $field_args ) );
			return $container;
		}
	}
}