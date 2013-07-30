<?php

class Group_Buying_Passbooks extends Group_Buying_Controller {

	const PATH_OPTION = 'gb_passbook_vouchers_url_path';
	const VOUCHER_PATH_OPTION = 'gb_passbook_vouchers_path';
	const LOG_OPTION = 'gb_passbook_vouchers_logs';
	const QUERY_VAR = 'gbs_voucher';
	private static $path = 'gbs_voucher';
	private static $logs = TRUE;
	private static $base_voucher_path = 'secret-folder-29742/';
	
	public static function init() {
		
		// Path
		self::$path = get_option(self::PATH_OPTION, self::$path);
		self::register_path_callback(self::$path, array(get_class(), 'voucher'), self::QUERY_VAR, 'voucher');
		
		// Options
		add_action('admin_init', array(get_class(), 'register_settings_fields'), 20, 0);
		self::$base_voucher_path = get_option(self::VOUCHER_PATH_OPTION, self::$base_voucher_path);
		self::$logs = get_option(self::LOG_OPTION, self::$logs);
		
		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );
		add_filter( 'request', array( get_class(), 'column_orderby' ) );
		
		// URL
		add_filter( 'gb_get_voucher_permalink', array( get_class(), 'new_voucher_permalink' ), 10, 2 );
		add_filter( 'gb_voucher_link', array( get_class(), 'new_gb_voucher_link' ), 10, 2 );
		
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_voucher_options';
		add_settings_section( $section, self::__('Passbook Voucher Settings'), array(get_class(),'display_section'), $page );

		// Settings
		register_setting( $page, self::VOUCHER_PATH_OPTION );
		add_settings_field( self::VOUCHER_PATH_OPTION, self::__('Secret Voucher Folder'), array(get_class(), 'display_vouchers_path'), $page, $section);
	}

	public static function display_section() {
		echo self::__('Passbook Voucher Settings');
	}

	public static function display_vouchers_path() {
		echo trailingslashit( WP_CONTENT_DIR ) . ' <input type="text" name="' . self::VOUCHER_PATH_OPTION . '" id="' . self::VOUCHER_PATH_OPTION . '" value="' . esc_attr( self::$base_voucher_path ) . '" size="40"/><br /><small>'.self::__('This is the name of the sercret folder you created on your server.').'</small>';
	}

	public static function voucher() {
		self::login_required();
		if ( isset($_REQUEST['voucher']) && $_REQUEST['voucher'] != '' ) {
			$deal = Group_Buying_Deal::get_instance($_REQUEST['voucher']);
			if ( is_a( $deal, 'Group_Buying_Deal' ) ) {
				$user_id = get_current_user_id();
				$access = Group_Buying_Voucher::allowed_to_voucher( $deal, $user_id );
				if ( TRUE !== $access ) {
					self::set_message($access);
				} else {
					$file_name = Group_Buying_Voucher::get_file_name($deal);
					$vouchered = Group_Buying_Voucher::voucher_file( array( 'deal' => $deal, 'user_id' => $user_id, 'base_dir' => trailingslashit( WP_CONTENT_DIR ) . self::$base_voucher_path, 'file_name' => $file_name, 'log_option' => self::$logs ) );
				}
				$redirect = (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] != '') ? home_url($_REQUEST['redirect']) : Group_Buying_Accounts::get_url();
				wp_redirect($redirect);
				exit();
			}
		}
	}

	public static function new_voucher_permalink( $link, $voucher_id = 0 ) {
		if ( !$voucher_id ) {
			global $post;
			$voucher_id = $post->ID;
		}
		$voucher = Group_Buying_Voucher::get_instance($voucher_id);
		$deal = $voucher->get_deal();
		$deal_id = $deal->get_id();
		if ( Group_Buying_Voucher::is_voucher( $deal ) ) {
			$link = add_query_arg( array( 'voucher' => $deal_id, 'redirect' => $_SERVER['REQUEST_URI'] ), home_url(self::$path) );
		}
		return apply_filters('gb_voucher_voucher_permalink',$link,$voucher_id,$deal_id);
	}
	
	function new_gb_voucher_link( $link, $voucher_id = 0 ) {
		if ( !$voucher_id ) {
			global $post;
			$voucher_id = $post->ID;
		}
		$voucher = Group_Buying_Voucher::get_instance($voucher_id);
		$deal = $voucher->get_deal();
		$deal_id = $deal->get_id();
		if ( Group_Buying_Voucher::is_voucher( $deal ) ) {
			$user_id = get_current_user_id();
			$access = Group_Buying_Voucher::allowed_to_voucher( $deal, $user_id );
			if ( TRUE !== $access ) {
				$link = '<a href="javascript:void(0)" title="'.gb__('Passbook Voucher').'" class="alt_button voucher_voucher">'.$access.'</a>';
			} else {
				$link = '<a href="'.gb_get_voucher_permalink($voucher_id).'" title="'.gb__('Passbook Voucher').'" class="alt_button voucher_voucher">'.gb__('Voucher File').'</a>';
			}
		}
		echo apply_filters('gb_voucher_voucher_link',$link,$voucher_id);
	}

	public static function register_columns( $columns ) {
		$columns['vouchers'] = self::__('Vouchers');
		return $columns;
	}


	public static function column_display( $column_name, $id ) {
		global $post;
		$deal = Group_Buying_Deal::get_instance($id);

		if ( !is_a($deal,'Group_Buying_Deal') ) 
			return; // return for that temp post

		switch ( $column_name ) {
			
			case 'vouchers':
				echo Group_Buying_Voucher::get_count($deal);
				break;

			default:
				# code...
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['vouchers'] = 'vouchers';
		return $columns;
	}

	public function column_orderby( $vars ) {
		if (isset( $vars['orderby']) && is_admin()) {
			switch ($vars['orderby']) {
				case 'vouchers':
					$vars = array_merge( $vars, array(
						'meta_key' => '_gb_voucher_count',
						'orderby' => 'meta_value_num'
					) );
				break;
				default:
					# code...
					break;
			}
		}
 
		return $vars;
	}
}