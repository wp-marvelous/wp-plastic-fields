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

if ( ! class_exists( 'ThanksToIT\DPWP\Factory\Namespace_Factory' ) ) {

	class Namespace_Factory {

		private $base_namespace = '';
		private $derived_namespaces = array();
		private $check_derived_class = '';
		public $messages = array(
			'class_doesnt_exist'   => 'Class %s does not exist.',
			'class_doesnt_inherit' => 'Class %s must inherit %s',
		);

		/**
		 * Namespace_Factory constructor.
		 *
		 * @param string $base_namespace
		 * @param array $derived_namespaces
		 * @param bool $check_derived_class
		 */
		public function __construct( $base_namespace = '', $derived_namespaces = array(), $check_derived_class = false ) {
			$this->base_namespace      = $base_namespace;
			$this->derived_namespaces  = $derived_namespaces;
			$this->check_derived_class = $check_derived_class;
		}

		/**
		 * get_full_class.
		 *
		 * @param $namespace
		 * @param $name
		 *
		 * @return string
		 */
		function get_full_class( $namespace, $name ) {
			return '\\' . $this->remove_namespace_slashes( $namespace ) . '\\' . $this->remove_namespace_slashes( $name );
		}

		/**
		 * Creates the class.
		 *
		 * @param string $class_name
		 * @param bool $check_derived_class
		 * @param string $subclass_to_check
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		function create( $class_name = '', $check_derived_class = null, $subclass_to_check = '' ) {
			$check_derived_class = is_null( $check_derived_class ) ? $this->check_derived_class : $check_derived_class;
			$class               = $this->get_full_class( $this->base_namespace, $class_name );
			if ( ! class_exists( $class ) ) {
				throw new \Exception( sprintf( $this->messages['class_doesnt_exist'], $class ) );
			}
			if ( $check_derived_class ) {
				foreach ( $this->derived_namespaces as $namespace ) {
					$derived_class = $this->get_full_class( $namespace, $class_name );
					if ( class_exists( $derived_class ) ) {
						if ( is_subclass_of( $derived_class, $class ) ) {
							$class = $derived_class;
							break;
						} else {
							throw new \Exception( sprintf( $this->messages['class_doesnt_inherit'], $derived_class, $class ) );
							break;
						}
					}
				}
			}
			if ( ! empty( $subclass_to_check ) && ! is_subclass_of( $class, $subclass_to_check ) ) {
				throw new \Exception( sprintf( __( $this->messages['class_doesnt_inherit'] ), $class, $subclass_to_check ) );
			}
			return new $class();
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