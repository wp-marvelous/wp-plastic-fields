<?php
/**
 * WP Plastic Fields - Fields Group
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

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Fields_Group' ) ) {
	class Fields_Group {

		public $fields = array();
		public $args;

		/**
		 * @var Container
		 */
		protected $container;

		/**
		 * @var Field_Factory;
		 */
		protected $factory;

		public function __construct( $args, Container $container ) {
			$this->container = $container;
			$args            = wp_parse_args( $args, array(
				'navigation' => array(
					'path' => array()
				),
				'fields'     => array()
			) );
			$this->args      = $args;
		}

		function init() {
			$this->factory = new Field_Factory();
			$this->generate_fields();
		}

		function get_fields_output() {
			ob_start();
			foreach ( $this->fields as $field ) {
				$row_template = $this->get_field_row_template( array( 'style' => $field->display_style ) );
				echo String_Functions::string_replace( array(
					'field_presentation'  => $field->get_presentation_output(),
					'field_value'         => $field->get_field_output(),
					'field_value_classes' => 'forminp forminp-' . esc_attr( sanitize_title( $field->type ) )
				), $row_template );
			}
			return ob_get_clean();
		}

		function display() {
			if ( empty( $this->fields ) ) {
				return;
			}
			$fields_group_wrapper_template = $this->get_fields_group_wrapper_template();
			echo String_Functions::string_replace( array(
				'fields_group_classes' => '',
				'fields'               => $this->get_fields_output(),
			), $fields_group_wrapper_template );
		}

		function generate_fields() {
			if (
				! empty( $this->container->navigation ) &&
				( ! empty( $navigation_path = $this->args['navigation']['path'] ) ) &&
				$this->container->navigation->get_active_path() != $this->container->navigation->convert_navigation_path_to_id( $navigation_path )
			) {
				return;
			}

			$fields = array();
			foreach ( $this->args['fields'] as $field_data ) {
				$class_name = isset( $field_data['class_name'] ) ? $field_data['class_name'] : '';
				$field      = $this->factory->create( $field_data['type'], $field_data, $class_name );
				$field->set_container( $this->container );
				$field->set_fields_group( $this );
				$field->init();
				$fields[] = $field;
			}
			$this->fields = $fields;
		}

		/**
		 * get_field_row_template.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 *
		 * @return false|string
		 */
		function get_field_row_template( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'style' => 'two_columns' // two_columns || one_column
			) );
			ob_start();
			?>
			<tr valign="top">
				<?php if ( 'two_columns' == $args['style'] ): ?>
					<th scope="row" class="titledesc">
						{{field_presentation}}
					</th>
					<td class="{{field_value_classes}}">
						{{field_value}}
					</td>
				<?php elseif ( 'one_column' == $args['style'] ): ?>
					<td colspan="2" scope="row" class="{{field_value_classes}}">
						{{field_presentation}}{{field_value}}
					</td>
				<?php endif; ?>
			</tr>
			<?php
			return ob_get_clean();
		}

		/**
		 * get_fields_group_wrapper_template.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 *
		 * @return false|string
		 */
		function get_fields_group_wrapper_template( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'style' => 'table' // table || pure
			) );
			ob_start();
			?>
			<?php if ( 'table' == $args['style'] ): ?>
				<table class="form-table plf-fields-group {{fields_group_classes}}" role="presentation">
					{{fields}}
				</table>
			<?php elseif ( 'pure' == $args['style'] ): ?>
				{{fields}}
			<?php endif; ?>
			<?php
			return ob_get_clean();
		}
	}
}