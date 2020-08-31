<?php
/**
 * WP Plastic Fields - Field
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields\Field;

use WP_Marvelous\WP_Plastic_Fields\Container\Container;
use WP_Marvelous\WP_Plastic_Fields\Fields_Group;
use WP_Marvelous\WP_Plastic_Fields\String_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Field\Field' ) ) {

	class Field {

		// Auto Properties
		public $id;
		public $args;
		public $title;
		public $disabled;
		public $class;
		public $css;
		public $placeholder;
		public $type;
		public $desc;
		public $desc_tip;
		public $description;
		public $custom_attributes;
		public $suffix;
		public $value;
		public $default = '';
		public $display_style = '';
		public $validation = array();

		/**
		 * @var Container
		 */
		protected $container;

		/**
		 * @var Fields_Group
		 */
		protected $fields_group;

		public $validation_errors = array();

		/**
		 * Field constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $args
		 */
		public function __construct( $args ) {
			$args       = wp_parse_args( $args, $this->get_default_values() );
			$this->args = $args;
			foreach ( $args as $key => $val ) {
				if ( property_exists( $this, $key ) ) {
					$this->{$key} = $val;
				}
			}
		}

		function init() {
			add_action( "plf_container_{$this->container->id}_field_{$this->id}_icons", array( $this, 'get_field_icons' ) );
			add_action( "plf_container_{$this->container->id}_submit", array( $this, 'save_value' ) );
			add_filter( "plf_container_{$this->container->id}_notices", array( $this, 'add_validation_error_notices' ) );

		}

		function get_error_messages() {
			$messages = array(
				'wp_http_validate_url' => 'Couldn\'t save {{field_value}} on {{field_title}} because it\'s not a valid URL.'
			);
			return apply_filters( 'plf_error_messages', $messages );
		}

		function get_error_message_by_validation_function( $function ) {
			foreach ( $this->get_error_messages() as $key => $value ) {
				if ( $function === $key ) {
					return $value;
				}
			}
			return '';
		}

		function add_validation_error_notices( $notices ) {
			if ( empty( $this->validation_errors ) ) {
				return $notices;
			}
			foreach ( $this->validation_errors as $error ) {
				$message_template = $this->get_error_message_by_validation_function( $error['function'] );
				if ( empty( $message_template ) ) {
					continue;
				}
				$message   = String_Functions::string_replace( array(
					'field_value' => '<code>'.$error['input_value'].'</code>',
					'field_title' => '<strong>'.$this->title.'</strong>',
				), $message_template );
				$notices[] = array(
					'message' => $message,
					'type'    => 'error'
				);
			}
			//error_log( print_r( $this->validation_errors, true ) );
			/*add_action( "plf_container_{$this->container->id}_field_{$this->id}_validation_errors", function( $function, $value) use($notices){
				$notices[] = array(
					'message' => 'asd <code>asd</code>',
					'type'=>'error'
				);
			},10,2 );*/
			return $notices;
		}

		function validation_errors( $function, $value ) {
			//error_log(print_r($function,true));
		}

		function validate( $value ) {
			$validation = array();
			foreach ( $this->validation as $function ) {
				if ( empty( $function ) ) {
					continue;
				}
				$validation[] = $valid = call_user_func( $function, $value );
				if ( ! $valid ) {
					$this->validation_errors[] = array( 'function' => $function, 'input_value' => $value );
				}
			}
			if ( in_array( false, $validation ) ) {
				return false;
			} else {
				return true;
			}
		}

		function save_value() {
			$field_value = isset( $_POST[ $this->id ] ) ? $_POST[ $this->id ] : '';
			if ( ! $this->validate( $field_value ) ) {
				return;
			}
			$this->value = call_user_func_array( $this->container->save_meta_function, array( $this->id, $field_value, 'yes' ) );
		}

		function get_value() {
			$this->value = call_user_func_array( $this->container->get_meta_function, array( $this->id, $this->default ) );
			return $this->value;
			//$this->value = self::get_option( $value['id'], $value['default'] );
		}

		function get_field_icons() {
			?>
			<span class="plf-icons">
				<!--<span class="plf-icon dashicons-before dashicons-awards"></span>-->
				<!--<span class="plf-icon dashicons-before dashicons-heart"></span>-->
				<!--<span class="plf-icon dashicons-before dashicons-lock"></span>-->
				<span title="<?php echo $this->desc_tip?>" class="plf-icon dashicons-before dashicons-editor-help"></span>
			</span>
			<?php
		}

		/**
		 * get_default_values.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		function get_default_values() {
			return array(
				'id'                => '',
				'title'             => '',
				'disabled'          => false,
				'default'           => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc'              => '',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => array(),
				'validation'        => array( '' ),
				'display_style'     => 'two_columns',
				'suffix'            => '',
				'value'             => ''
			);
		}

		/**
		 * get_label_output.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return false|string
		 */
		function get_presentation_output() {
			ob_start();
			?>
			<?php do_action( "plf_container_{$this->container->id}_field_{$this->id}_icons", $this ); ?>
			<label for="<?php echo esc_attr( $this->id ); ?>"><?php echo wp_kses_post( $this->title ); ?></label>
			<?php
			return ob_get_clean();
		}

		function get_custom_attributes() {
			$custom_attributes = array();
			if ( ! empty( $this->custom_attributes ) && is_array( $this->custom_attributes ) ) {
				foreach ( $this->custom_attributes as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}
			return $custom_attributes;
		}

		/**
		 * get_field_output.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return false|string
		 */
		function get_field_output() {
			ob_start();
			?>
			<input
			name="<?php echo esc_attr( $this->id ); ?>"
			id="<?php echo esc_attr( $this->id ); ?>"
			type="<?php echo esc_attr( $this->type ); ?>"
			style="<?php echo esc_attr( $this->css ); ?>"
			value="<?php echo esc_attr( $this->get_value() ); ?>"
			class="<?php echo esc_attr( $this->class ); ?>"
			placeholder="<?php echo esc_attr( $this->placeholder ); ?>"
			<?php echo implode( ' ', $this->get_custom_attributes() ); // WPCS: XSS ok. ?>
			/><?php echo esc_html( $this->suffix ); ?><?php echo $this->get_field_description()['description']; // WPCS: XSS ok. ?>
			<?php
			return ob_get_clean();
		}

		/**
		 * Helper function to get the formatted description and tip HTML for a
		 * given form field. Plugins can call this when implementing their own custom
		 * settings types.
		 *
		 * @return array The description and tip as a 2 element array.
		 */
		public function get_field_description() {
			$description  = '';
			$tooltip_html = '';

			if ( true === $this->desc_tip ) {
				$tooltip_html = $this->desc;
			} elseif ( ! empty( $this->desc_tip ) ) {
				$description  = $this->desc;
				$tooltip_html = $this->desc_tip;
			} elseif ( ! empty( $this->desc ) ) {
				$description = $this->desc;
			}

			if ( $description && in_array( $this->type, array( 'textarea', 'radio' ), true ) ) {
				$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
			} elseif ( $description && in_array( $this->type, array( 'checkbox' ), true ) ) {
				$description = wp_kses_post( $description );
			} elseif ( $description ) {
				$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
			}

			if ( $tooltip_html && in_array( $this->type, array( 'checkbox' ), true ) ) {
				$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
			} elseif ( $tooltip_html ) {
				$tooltip_html = wc_help_tip( $tooltip_html );
			}

			return array(
				'description'  => $description,
				'tooltip_html' => $tooltip_html,
			);
		}

		/**
		 * display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		function display() {
			$row_template = $this->fields_group->get_field_row_template();
			echo String_Functions::string_replace( array(
				'field_presentation'  => $this->get_presentation_output(),
				'field_value'         => $this->get_field_output(),
				'field_value_classes' => 'forminp forminp-' . esc_attr( sanitize_title( $this->type ) )
			), $row_template );
		}

		/**
		 * @return mixed
		 */
		public function get_container() {
			return $this->container;
		}

		/**
		 * @param mixed $container
		 */
		public function set_container( $container ) {
			$this->container = $container;
		}

		/**
		 * @return Fields_Group
		 */
		public function get_fields_group(): Fields_Group {
			return $this->fields_group;
		}

		/**
		 * @param Fields_Group $fields_group
		 */
		public function set_fields_group( Fields_Group $fields_group ) {
			$this->fields_group = $fields_group;
		}

	}
}