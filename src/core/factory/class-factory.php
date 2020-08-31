<?php
/**
 * WP Plastic Fields - Factory
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields\Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Factory\Factory' ) ) {
	abstract class Factory {

		/**
		 * create.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function create( $type, $args, $class_name ) {

		}

		/**
		 * generate_class_name.
		 *
		 * @param $namespace
		 * @param $class_name
		 *
		 * @return string
		 */
		function generate_class_name( $namespace, $class_name ) {
			$class_name = $this->to_camel_case( $class_name, true );
			return '\\' . $this->remove_namespace_slashes( $namespace ) . '\\' . $this->remove_namespace_slashes( $class_name );
		}

		/**
		 * dashesToCamelCase.
		 *
		 * @param $string
		 * @param bool $capitalizeFirstCharacter
		 *
		 * @return mixed|string
		 */
		function to_camel_case( $string, $capitalizeFirstCharacter = false ) {
			$str = str_replace( '-', '_', ucwords( $string, '-_\\' ) );
			if ( ! $capitalizeFirstCharacter ) {
				$str = lcfirst( $str );
			}
			return $str;
		}

		/**
		 * remove_namespace_slashes.
		 *
		 * @param $string
		 *
		 * @return string
		 */
		function remove_namespace_slashes( $string ) {
			return trim( $string, '\\' );
		}
	}
}