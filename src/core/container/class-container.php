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

			// Container
			//add_action( 'admin_init', array( $this, 'check_submit_btn' ) );

			add_action( 'plf_before_container', array( $this, 'display_container_title' ), 10 );
			add_action( 'plf_before_container', array( $this, 'init_container_navigation' ), 11 );
			add_action( 'plf_before_container', array( $this, 'display_container_navigation' ), 12 );
			add_action( 'plf_container', array( $this, 'init_fields_groups' ), 12 );
			add_action( 'plf_container', array( $this, 'detect_submit_btn' ), 13 );
			add_action( 'plf_container', array( $this, 'display_container_messages' ), 14 );
			//add_action( 'plf_container_form', array( $this, 'init_fields_groups' ), 10 );

			add_action( 'plf_container_form', array( $this, 'display_fields_groups' ), 12 );
			add_action( 'plf_container_form', array( $this, 'display_container_form_actions' ), 15 );


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

		function save_fields() {

		}

		function init_container_navigation() {
			do_action( "plf_container_{$this->id}_navigation", $this );
			if ( empty( $this->navigation ) ) {
				return;
			}
			$this->navigation->args['original_url'] = admin_url( 'admin.php?page=' . $this->id );
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
			echo '<br class="clear">';
			$notice_template = '<div class="{{class}}">{{message}}</div>';
			foreach ( $notices as $notice ) {
				$notice_args = wp_parse_args( $notice, array(
					'is_dismissible' => false,
					'auto_p'         => true,
					'default_class'  => 'inline notice',
					'type'           => 'info', // error | warning | success | info,
					'class'          => '',
					'message'        => '',
				) );
				if ( empty( $notice_args['class'] ) ) {
					$notice_args['class'] = $notice_args['default_class'];
					$notice_args['class'] .= $notice_args['is_dismissible'] ? ' is-dismissible' : '';
					$notice_args['class'] .= ' notice-' . $notice_args['type'];
				}
				echo String_Functions::string_replace( array(
					'message' => $notice_args['auto_p'] ? '<p>' . wp_kses_post( $notice_args['message'] ) . '</p>' : wp_kses_post( $notice_args['message'] ),
					'class'   => $notice_args['class'],
				), $notice_template );
			}
		}

		function init_fields_groups() {
			do_action( "plf_container_{$this->id}_fields_groups", $this );
			foreach ( $this->fields_groups as $fields_group ) {
				$fields_group->init();
			}
		}

		function display_fields_groups() {
			if ( empty( $this->fields_groups ) ) {
				return;
			}
			echo '<div class="plf-container-wrapper">';
			//echo '<div style="clear:both"></div>';
			//echo '<table class="form-table plf-fields-group">';
			foreach ( $this->fields_groups as $fields_group ) {
				$fields_group->display();
			}
			echo '</div>';
			//echo '</table>';
		}

		/**
		 * display_css.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function display_css() {
			?>
			<style>

				/* Container */
				.plf-container-wrapper .form-table th {
					min-width: 200px;
					/*max-width:221px;*/
					max-width: 260px;
					width: auto;
				}

				.plf-container-wrapper .form-table {
					width: auto;
				}

				/* Container actions */
				.plf-container-actions {
					clear: both;
				}

				/* Fields */
				.plf-container-wrapper td.forminp-title{
					margin:0;
					padding:0;
				}
				.plf-container-wrapper td.forminp-title{
					font-size:13px;
					line-height: 1.3em;
				}

				.plf-container-wrapper td.forminp-title p{
					margin: 1em 0;
				}

				.form-table.plf-fields-group:not(:first-child) {
					margin-top: 0;
				}

				.plf-container-wrapper th label {
					display: block;
				}

				.plf-container-wrapper table.form-table input:not([type="checkbox"]), .plf-container-wrapper table.form-table textarea {
					min-width: 400px
				}

				/* Navigation */
				.plf-nav.subsubsub {
					clear: both;
				}

				.plf-nav.subsubsub li:last-child:after {
					content: none;
				}

				.plf-nav.subsubsub li:after {
					content: "|";
					margin: 0 0 0 0;
				}

				/* Fields Icons */
				.plf-icons {
					float: right;
					position: relative;
					right: -11px;
					margin-left: 10px;
					vertical-align: middle;
					/*font-size:0;*/
				}

				.plf-icon {
					position: relative;
					display: inline-block;
					vertical-align: middle;
					color: #fff;
					font-size: 0;
					height: 17px;
					cursor: pointer;
					top: -1px;
				}

				/*.plf-icon.dashicons-editor-help{
					top:1px;
				}*/
				.plf-icon.dashicons-lock {
					top: -2px;
				}

				.plf-icon:before {
					color: #666;
					text-align: center;
					font-size: 17px;
					display: inline-block;
					vertical-align: middle;
					position: relative;
				}

				@media only screen and (max-width: 782px) {
					.plf-container-wrapper .form-table {
						width: 100%;
					}

					.plf-icons {
						float: none;
						right: -9px;
						margin-left: 0px;
						top: -1px;
					}

					.plf-container-wrapper th label {
						float: left;
					}

					.plf-container-wrapper .form-table th {
						width: auto;
						max-width: unset;
					}
				}
			</style>
			<?php
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
			<?php $this->display_css(); ?>
			<?php
			return ob_get_clean();
		}

		function set_navigation( Navigation $navigation ) {
			$this->navigation = $navigation;
		}
	}
}