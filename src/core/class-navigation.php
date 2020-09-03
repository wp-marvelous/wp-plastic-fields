<?php
/**
 * WP Plastic Fields - Field Group
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  WP Marvelous
 */

namespace WP_Marvelous\WP_Plastic_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_Marvelous\WP_Plastic_Fields\Navigation' ) ) {
	class Navigation {

		public $args = null;
		protected $levels = array();
		protected $active_path = array();
		protected $query_string = array();
		public $started = false;

		/**
		 * Navigation constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args
		 */
		public function __construct( $args = array() ) {
			$args       = wp_parse_args( $args, array(
				'levels'       => array(),
				'hide_levels'   => array(),
				'original_url' => '',
				'query_string' => array(
					'level_params' => array(),
					'level_prefix' => 'lvl_'
				)
			) );
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

		/**
		 * init.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function init() {
			if ( $this->started ) {
				return;
			}
			$this->started = true;
			$this->sort_levels();
			$this->generate_default_properties();
			$this->calculate_dynamic_info();
		}

		/**
		 * sort_levels.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function sort_levels() {
			usort( $this->levels, function ( $a, $b ) {
				return ( $a["level"] <= $b["level"] ) ? - 1 : 1;
			} );
		}

		/**
		 * generate_default_properties.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function generate_default_properties() {
			foreach ( $this->levels as $data_key => $data_value ) {
				$this->levels[ $data_key ]['active'] = false;
				if ( ! isset( $data_value['default'] ) ) {
					$this->levels[ $data_key ]['default'] = $data_value['items'][0]['id'];
				}
				foreach ( $data_value['items'] as $item_key => $item_value ) {
					$this->levels[ $data_key ]['items'][ $item_key ]['active'] = false;
				}
			}
		}

		/**
		 * get_menu_item.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $level_id
		 * @param $menu_item_id
		 *
		 * @return mixed
		 */
		function get_menu_item( $level_id, $menu_item_id ) {
			$navigation = $this->levels;
			foreach ( $navigation as $navigation_level_data ) {
				if ( $level_id != $navigation_level_data['level'] ) {
					continue;
				}
				foreach ( $navigation_level_data['items'] as $item ) {
					if ( $menu_item_id == $item['id'] ) {
						return $item;
					}
				}
			}
		}

		function get_level_param( $level ) {
			$param = isset( $this->query_string['level_params'][ $level ] ) ? $this->query_string['level_params'][ $level ] : $this->query_string['level_prefix'] . $level;
			return $param;
		}

		/**
		 * calculate_dynamic_info.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function calculate_dynamic_info( $search_level = false ) {
			foreach ( $this->levels as $navigation_level_key => $navigation_level_data ) {
				$level = $navigation_level_data['level'];
				if ( ! $search_level ) {
					$search_level = $level;
				} elseif (
					$search_level != $level ||
					empty( $parent_item = $this->get_menu_item( $search_level - 1, end( $navigation_level_data['path'] ) ) ) ||
					! $parent_item['active']
				) {
					//$this->navigation_data[ $search_level ]['active'] = true;
					continue;
				}
				$this->levels[ $navigation_level_key ]['active'] = true;
				foreach ( $navigation_level_data['items'] as $item_key => $item ) {
					$level_param = $this->get_level_param( $level );
					$current_item = empty( $_GET[ $level_param ] ) ? $navigation_level_data['default'] : sanitize_title( wp_unslash( $_GET[ $level_param ] ) );
					if ( $item['id'] == $current_item ) {
						//$this->navigation_data[ $navigation_level_key ]['active']                       = true;
						$this->levels[ $navigation_level_key ]['items'][ $item_key ]['active'] = true;
						$this->add_active_path_item( $item['id'] );
						$search_level ++;
						//error_log(print_r($search_level,true));
						$this->calculate_dynamic_info( $search_level );
					}
				}
			}
		}

		/**
		 * add_active_path_item.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $item_id
		 */
		function add_active_path_item( $item_id ) {
			if ( ! in_array( $item_id, $this->active_path ) ) {
				$this->active_path[] = $item_id;
			}
		}

		/**
		 * get_item_link.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $item
		 * @param $navigation_level_data
		 *
		 * @return string
		 */
		function get_item_link( $item, $navigation_level_data ) {
			$level      = $navigation_level_data['level'];
			$slug       = $item['id'];
			$query_args = array();
			if ( isset( $navigation_level_data['path'] ) && ! empty( $navigation_level_data['path'] ) ) {
				for ( $i = 0; $i < count( $navigation_level_data['path'] ); $i ++ ) {
					$query_args[ $this->get_level_param( $i ) ] = $navigation_level_data['path'][ $i ];
				}
			}
			$query_args[ $this->get_level_param( $level ) ] = esc_attr( $slug );
			$link                                           = add_query_arg( $query_args, $this->get_original_admin_page_url() );
			return $link;
		}

		/**
		 * get_original_admin_page_url.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_original_admin_page_url() {
			$page_url = $this->args['original_url'];
			if ( empty( $page_url ) ) {
				global $wp;
				return add_query_arg( $wp->query_vars, home_url( $wp->request ) );
			}
			return $page_url;
		}

		/**
		 * get_a_class.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $item
		 * @param $navigation_level_data
		 *
		 * @return string
		 */
		function get_a_class( $item, $navigation_level_data ) {
			$level   = $navigation_level_data['level'];
			$a_class = $level == 0 ? ( 'nav-tab ' . ( $item['active'] ? 'nav-tab-active' : '' ) ) : ( ( $item['active'] ? 'current' : '' ) );

			return $a_class;
		}

		/**
		 * get_navigation_data.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		function get_levels() {
			return $this->levels;
		}

		/**
		 * convert_navigation_path_to_id.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 * @param string $separator
		 *
		 * @return string
		 */
		function convert_navigation_path_to_id( $value, $separator = '>', $separate_by_space = true ) {
			if ( is_string( $value ) ) {
				$value = array_map( 'trim', array_filter( explode( $separator, $value ) ) );
			}
			if ( is_array( $value ) ) {
				return implode( ' ' . $separator . ' ', $value );
			}
			return $value;
		}

		/**
		 * display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function display() {
			$navigation_data = $this->levels;
			?>
			<?php foreach ( $navigation_data as $navigation_level_data ) : ?>
				<?php $level = intval( $navigation_level_data['level'] ); ?>
				<?php if(in_array($level,$this->args['hide_levels'])): ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php if ( ! $navigation_level_data['active'] ): ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php $wrapper_elem = $level == 0 ? 'nav' : 'ul'; ?>
				<?php $wrapper_class = $level == 0 ? 'plf-nav nav-tab-wrapper' : 'plf-nav subsubsub'; ?>
				<?php echo String_Functions::string_replace( array(
					'wrapper_element' => $wrapper_elem,
					'wrapper_class'   => esc_attr( $wrapper_class )
				), "<{{wrapper_element}} class='{{wrapper_class}}'>" ); ?>
				<?php foreach ( $navigation_level_data['items'] as $item ) : ?>
					<?php $label = $item['title']; ?>
					<?php $a_class = $this->get_a_class( $item, $navigation_level_data ); ?>
					<?php $a_href = $this->get_item_link( $item, $navigation_level_data ); ?>
					<?php echo $level > 0 ? '<li>' : ''; ?>
					<?php echo '<a href="' . esc_url( $a_href ) . '" class="' . esc_attr( $a_class ) . '">' . esc_html( $label ) . '</a>'; ?>
					<?php echo $level > 0 ? '</li>' : ''; ?>
				<?php endforeach; ?>
				<?php echo '</' . $wrapper_elem . '>'; ?>
			<?php endforeach; ?>
			<?php
		}

		/**
		 * get_active_path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param bool $convert_to_id
		 *
		 * @return array|string
		 */
		public function get_active_path( $convert_to_id = true ) {
			$active_path = $this->active_path;
			if ( $convert_to_id ) {
				$active_path = $this->convert_navigation_path_to_id( $active_path );
			}
			return $active_path;
		}
	}
}