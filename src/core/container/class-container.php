<?php
/**
 * WP Plastic Fields - Container
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields\Container;

use WP_Marvelous\WP_Plastic_Fields\Fields_Group;
use WP_Marvelous\WP_Plastic_Fields\Navigation;
use WP_Marvelous\WP_Plastic_Fields\String_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Container\Container' ) ) {
	class Container {
		public $id;
		public $type;
		public $fields_groups = array();
		public $menu;
		public $args;
		public $save_meta_function = 'update_option';
		public $get_meta_function = 'get_option';

		/**
		 * @var Navigation
		 */
		public $navigation;

		public function __construct( $args = null ) {
			$args       = wp_parse_args( $args, array(
				'id'         => '',
				'capability' => 'manage_options',
				'page_title' => 'Custom Page Title',
				'type'       => '',
				'menu'       => null,
			) );
			$this->id   = $args['id'];
			$this->type = $args['type'];
			if ( 'options_page' === $args['type'] ) {
				$args['menu'] = wp_parse_args( $args['menu'], array(
					'parent_slug' => '',
					'page_title'  => $args['page_title'],
					'menu_title'  => 'Custom Page',
					'capability'  => $args['capability'],
					'menu_slug'   => $args['id'],
					'function'    => array( $this, 'create_page_html' ),
					'icon_url'    => '',
					'position'    => null
				) );
			}
			$this->args = $args;
			$this->auto_set_properties();
		}

		/**
		 * auto_set_properties.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function auto_set_properties() {
			foreach ( $this->args as $key => $val ) {
				//if ( property_exists( $this, $key ) && ! in_array( $key, $this->get_ignored_auto_set_properties() ) ) {
				if ( property_exists( $this, $key ) ) {
					$this->{$key} = $val;
				}
			}
		}

		function init() {
			// Menu
			add_action( 'admin_menu', array( $this, 'handle_admin_menus' ) );

			//add_action( 'plf_container_' . 'plastic_slug' . '_navigation', function ( Container $container ) {

			// Before Container
			add_action( 'plf_before_container', array( $this, 'display_container_title' ), 10 );

			//Navigation
			add_filter("plf_container_{$this->id}_navigation",array($this,'override_navigation_params'));
			add_action( 'plf_before_container', array( $this, 'init_container_navigation' ), 11 );
			add_action( 'plf_before_container', array( $this, 'display_container_navigation' ), 12 );

			// Container
			add_action( 'plf_container', array( $this, 'init_fields_groups' ), 12 );
			add_action( 'plf_container', array( $this, 'detect_submit_btn' ), 13 );
			add_action( 'plf_container', array( $this, 'display_container_messages' ), 14 );

			// Container Form
			add_action( 'plf_container_form', array( $this, 'display_fields_groups' ), 12 );
			add_action( 'plf_container_form', array( $this, 'display_container_form_actions' ), 15 );

			// Show success message
			add_action( "plf_container_{$this->id}_submit", array( $this, 'check_validation_errors_after_submit' ), 20 );
			add_action( "plf_container_{$this->id}_no_validation_errors_after_submit", array( $this, 'show_success_message_after_submit' ) );
		}

		function override_navigation_params( $params ) {
			$params['original_url'] = admin_url( 'admin.php?page=' . $this->id );
			return $params;
		}

		function show_success_message_after_submit() {
			add_filter( "plf_container_{$this->id}_notices", function ( $notices ) {
				$notices[] = array(
					'message' => __( 'The settings have been saved successfully.', 'wp-plastic-fields' ),
					'type'    => 'success'
				);
				return $notices;
			} );
		}

		function check_validation_errors_after_submit() {
			$errors = array();
			foreach ( $this->fields_groups as $fields_group ) {
				foreach ( $fields_group->fields as $field ) {
					if ( ! empty( $field->validation_errors ) ) {
						$errors[] = $field->validation_errors;
					}
				}
			}
			if ( empty( $errors ) ) {
				do_action( "plf_container_{$this->id}_no_validation_errors_after_submit" );
			}
		}

		function detect_submit_btn() {
			if (
				! isset( $_POST["plf_container_{$this->id}_submit"] ) ||
				false === check_admin_referer( "save_settings_$this->id", 'plf-settings' )
			) {
				return;
			}
			do_action( "plf_container_{$this->id}_submit" );
		}

		function handle_admin_menus() {
			if ( 'options_page' !== $this->type ) {
				return;
			}
			$this->create_admin_menu();
		}

		function create_admin_menu() {
			$menu = $this->menu;
			if ( isset( $menu['parent_slug'] ) && ! empty( $menu['parent_slug'] ) ) {
				add_submenu_page( $menu['parent_slug'], $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], $menu['function'], $menu['position'] );
			} else {
				add_menu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], $menu['function'], $menu['icon_url'], $menu['position'] );
			}
		}

		function create_page_html() {
			$template = $this->get_template();
			echo $template;
		}

		function add_fields_group( $args = array() ) {
			$this->fields_groups[] = new Fields_Group( $args, $this );
		}

		function init_container_navigation() {
			do_action( "plf_container_{$this->id}_set_navigation", $this );
			if ( empty( $this->navigation ) ) {
				return;
			}
			$this->navigation->init();
		}

		function display_container_navigation() {
			if ( empty( $this->navigation ) ) {
				return;
			}
			$this->navigation->display();
		}

		function display_container_title() {
			?>
			<?php if ( ! empty( $this->menu['page_title'] ) ): ?>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php endif; ?>
			<?php
		}

		function display_container_messages() {
			$notices = apply_filters( "plf_container_{$this->id}_notices", array() );
			//echo '<br class="clear">';

			$notice_template = '<div class="{{class}}">{{message}}</div>';
			$notices_str='';
			foreach ( $notices as $notice ) {
				$notice_args = wp_parse_args( $notice, array(
					'is_dismissible' => false,
					'auto_p'         => true,
					'default_class'  => 'plf-notice inline notice',
					'type'           => 'info', // error | warning | success | info,
					'class'          => '',
					'message'        => '',
				) );
				if ( empty( $notice_args['class'] ) ) {
					$notice_args['class'] = $notice_args['default_class'];
					$notice_args['class'] .= $notice_args['is_dismissible'] ? ' is-dismissible' : '';
					$notice_args['class'] .= ' notice-' . $notice_args['type'];
				}
				$notices_str.= String_Functions::string_replace( array(
					'message' => $notice_args['auto_p'] ? '<p>' . wp_kses_post( $notice_args['message'] ) . '</p>' : wp_kses_post( $notice_args['message'] ),
					'class'   => $notice_args['class'],
				), $notice_template );
			}
			if ( ! empty( $notices_str ) ) {
				echo "<div class='plf-notices-wrapper'>{$notices_str}</div>";
			}
		}

		function init_fields_groups() {
			do_action( "plf_container_{$this->id}_set_fields_groups", $this );
			foreach ( $this->fields_groups as $fields_group ) {
				$fields_group->init();
			}
		}

		function display_fields_groups() {
			if ( empty( $this->fields_groups ) ) {
				return;
			}
			echo '<div class="plf-container-wrapper">';
			//echo '<table class="form-table plf-fields-group">';
			foreach ( $this->fields_groups as $fields_group ) {
				$fields_group->display();
			}
			echo '</div>';
			//echo '</table>';
		}

		function display_container_form_actions() {
			?>
			<div class="plf-container-actions">
				<?php
				submit_button( 'Save Settings', 'primary', "plf_container_{$this->id}_submit" );
				wp_nonce_field( "save_settings_{$this->id}", 'plf-settings' );
				?>
			</div>
			<?php
		}

		function get_template() {
			ob_start();
			?>
			<div class="wrap">
				<?php do_action( 'plf_before_container', $this ); ?>
				<div class="plf-container">
					<?php do_action( 'plf_container', $this ); ?>
					<form method="POST" id="mainform" action="" enctype="multipart/form-data">
						<?php do_action( 'plf_container_form', $this ); ?>
						<div style="clear:both"></div>
						<?php do_action( 'plf_container_form_actions', $this ); ?>
					</form>
				</div>
			</div>
			<?php //$this->display_css(); ?>
			<?php
			return ob_get_clean();
		}

		function set_navigation( Navigation $navigation ) {
			$this->navigation = $navigation;
		}
	}
}