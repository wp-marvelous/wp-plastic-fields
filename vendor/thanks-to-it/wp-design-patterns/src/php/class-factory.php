<?php
/**
 * Factory Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\DPWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\DPWP\Factory' ) ) {

	class Factory {

		public $main_namespace;
		public $sub_namespaces = array();

		public function __construct( $main_namespace, $sub_namespaces = array() ) {
			$this->main_namespace = $main_namespace;
			$this->sub_namespaces = $sub_namespaces;
		}

		private static $instances = array();

		/**
		 * get_class.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $full_class
		 * @param $params
		 * @param bool $singleton
		 *
		 * @return mixed|null|object
		 * @throws \ReflectionException
		 */
		function get_class( $original_class_name, $full_class, $params = null, $singleton = true ) {
			$reflection_class    = null;
			$need_to_instantiate = true;
			if ( class_exists( $full_class ) ) {
				if ( $singleton ) {
					if ( ! isset( self::$instances[ $full_class ] ) ) {
						$reflection_class = self::$instances[ $full_class ] = new \ReflectionClass( $full_class );
					} else {
						$need_to_instantiate = false;
						$reflection_class    = self::$instances[ $full_class ];
					}
				} else {
					$reflection_class = new \ReflectionClass( $full_class );
				}

				if ( $need_to_instantiate ) {
					if ( is_array( $params ) && count( $params ) > 0 ) {
						$reflection_class = self::$instances[ $full_class ] = $reflection_class->newInstanceArgs( $params );
					} else {
						$reflection_class = self::$instances[ $full_class ] = $reflection_class->newInstance();
					}
					if (
						$this->main_namespace . '\\' . $original_class_name != $full_class &&
						! is_subclass_of( $reflection_class, $this->main_namespace . '\\' . $original_class_name, false ) ) {
						throw new \Exception( "Class {$full_class} must inherit " . $this->main_namespace . '\\' . $original_class_name );
					}
				}
			}

			return $reflection_class;
		}

		/**
		 * get_instance.
		 *
		 * @version 1.0.0
		 * @since  1.0.0
		 *
		 * @param $class_name
		 * @param $params
		 * @param bool $singleton
		 *
		 * @return mixed|null|object
		 * @throws \ReflectionException
		 */
		function get_instance( $class_name, $params = null, $singleton = true ) {
			$namespaces       = $this->get_namespaces();
			$reflection_class = null;
			foreach ( $namespaces as $namespace ) {
				$reflection_class = $this->get_class( $class_name, $namespace . $class_name, $params, $singleton );
				if ( $reflection_class ) {
					break;
				}
			}
			return $reflection_class;
		}

		/**
		 * get_namespaces.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		function get_namespaces() {
			$namespaces = array();
			foreach ( $this->sub_namespaces as $sub_namespace ) {
				$namespaces[] = $this->main_namespace . '\\' . $sub_namespace . '\\';
			}
			$namespaces[] = $this->main_namespace . '\\';
			return $namespaces;
		}
	}
}