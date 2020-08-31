<?php
/**
 * Singleton
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\DPWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\DPWP\Singleton' ) ) {

	class Singleton {
		private static $instances = array();

		protected function __construct() {
		}

		protected function __clone() {
		}

		public function __wakeup() {
			throw new \Exception( "Cannot unserialize singleton" );
		}

		/**
		 * @return $this
		 */
		public static function get_instance() {
			$cls = get_called_class(); // late-static-bound class name
			if ( ! isset( self::$instances[ $cls ] ) ) {
				self::$instances[ $cls ] = new static;
			}
			return self::$instances[ $cls ];
		}
	}
}