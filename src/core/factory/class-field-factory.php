<?php
/**
 * WP Plastic Fields - Field Factory
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields\Factory;

use WP_Marvelous\WP_Plastic_Fields\Field\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Factory\Field_Factory' ) ) {
	class Field_Factory extends Factory {

		public function create( $type, $field_args = null, $class_name = '' ) {
			if ( empty( $class_name ) ) {
				$class_name = $this->generate_class_name( 'WP_Marvelous\WP_Plastic_Fields', "Field\\" . $type . "_Field" );
			}
			if ( ! class_exists( $class_name ) ) {
				throw new \Exception( sprintf( __( 'Class %s doesn\'t exist', 'wp-concept-fields' ), $class_name ) );
			}
			if ( ! is_subclass_of( $class_name, Field::class ) ) {
				throw new \Exception( sprintf( __( 'Class %s must inherit %s', 'wp-concept-fields' ), $class_name, Field::class ) );
			}
			$field = new $class_name( ...array( $field_args ) );
			return $field;
		}
	}
}