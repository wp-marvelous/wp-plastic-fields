<?php
/**
 * WP Plastic Fields - Functions
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\String_Functions\String_Functions' ) ) {

	class String_Functions {

		/**
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $arr_from_to
		 * @param $text
		 * @param null $args
		 *
		 * @return mixed
		 */
		static function string_replace( $arr_from_to, $text, $args = null ) {
			$args            = wp_parse_args( $args, array(
				'item_template' => '{{%s}}'
			) );
			$new_arr_from_to = $arr_from_to;
			foreach ( $arr_from_to as $k => $v ) {
				$item_template_dynamic                     = str_replace( '%s', $k, $args['item_template'] );
				$new_arr_from_to[ $item_template_dynamic ] = $arr_from_to[ $k ];
				unset( $new_arr_from_to[ $k ] );
			}
			return str_replace( array_keys( $new_arr_from_to ), $new_arr_from_to, $text );
		}

		/**
		 * display_template_variables.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $variables
		 * @param null $args
		 *
		 * @return string
		 */
		static function display_template_variables( $variables, $args = null ) {
			$args = wp_parse_args( $args, array(
				'item_template' => '{{%s}}',
				'style'         => 'full' // compact || full
			) );
			if ( 'full' == $args['style'] ) {
				$result = '<ul>';
				foreach ( $variables as $key => $value ) {
					$item   = str_replace( '%s', $key, $args['item_template'] );
					$result .= '<li><code>' . $item . '</code> - ' . $value . '</li>';
				}
				$result .= '</ul>';
			} elseif ( 'compact' == $args['style'] ) {
				$result          = '';
				$new_arr_from_to = $variables;
				foreach ( $variables as $k => $v ) {
					$item_template_dynamic                     = str_replace( '%s', $k, '<code>' . $args['item_template'] . '</code>' );
					$new_arr_from_to[ $item_template_dynamic ] = $variables[ $k ];
					unset( $new_arr_from_to[ $k ] );
				}
				$result = implode( ', ', array_keys( $new_arr_from_to ) );
			}

			return $result;
		}


	}
}