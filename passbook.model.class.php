<?php

/**
 * Load via GBS Add-On API
 */
class Group_Buying_Passbook_Addon extends Group_Buying_Controller {

	public static function init() {
		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( get_class(), 'gb_addon' ), 10, 1 );
	}

	public static function gb_addon( $addons ) {
		$addons['passbook'] = array(
			'label' => self::__( 'Passbook Vouchers' ),
			'description' => self::__( 'Allows users to add vouchers to iOS Passbook.' ),
			'files' => array(
				__FILE__,
				dirname( __FILE__ ) . '/passbook.controller.class.php',
				dirname( __FILE__ ) . '/library/PKPass.php',
				dirname( __FILE__ ) . '/library/template_tags.php',
			),
			'callbacks' => array(
				array( 'Group_Buying_Passbook', 'init' ),
				array( 'Group_Buying_Passbook', 'init' ),
			),
			'active' => TRUE,
		);
		return $addons;
	}

}

class Group_Buying_Passbook extends Group_Buying_Deal {

	const TAX = 'gb_vouchers';
	const TERM = 'digital-deals';
	const REWRITE_SLUG = 'voucher';
	const QUERY_VAR = 'gb_vouchers';
	const NO_EXPIRATION_DATE = -1;
	const VERSION = 1;

	private static $meta_keys = array(
		'voucherers' => '_gb_voucherer', // array
		'file_name' => '_gb_deal_voucher_url', // int
		'exp' => '_gb_deal_voucher_exp', // int
		'limit' => '_gb_deal_voucher_limit', // string
		'count' => '_gb_voucher_count', // string
		'user_prefix' => '_gb_voucher_count_', // string
	);

	public static function init() {

		// register Locations taxonomy
		$singular = 'Voucher';
		$plural = 'Vouchers';
		$taxonomy_args = array(
			'hierarchical' => TRUE,
			'public' => FALSE,
			'show_ui' => FALSE,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG,
				'with_front' => TRUE,
				'hierarchical' => FALSE,
			),
		);
		self::register_taxonomy( self::TAX, array( Group_Buying_Deal::POST_TYPE ), $singular, $plural, $taxonomy_args );

		//add_action('pre_get_posts', array(get_class(), 'filter_query'), 10, 1);
	}

	/**
	 * Edit the query to remove vouchers from the main loops
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public static function filter_query( WP_Query $wp_query ) {
		// we only care if this is the query for vouchers
		if ( ( self::is_deal_query( $wp_query ) || self::is_deal_tax_query( $wp_query ) || is_search() ) && !is_admin() && $query->query_vars['post_status'] != 'pending' ) {
			// get all the user's purchases
			$wp_query->set( 'tax_query', array(
					array(
						'taxonomy' => self::TAX,
						'field' => 'slug',
						'terms' =>
						array( self::TERM ), 'operator' => 'NOT IN' )
				) );
		}
		return $wp_query;
	}

	public static function get_term_slug() {
		$term = get_term_by( 'slug', self::TERM, self::TAX );
		if ( !empty( $term->slug ) ) {
			return $term->slug;
		} else {
			$return = wp_insert_term(
				self::TERM, // the term
				self::TAX, // the taxonomy
				array(
					'description'=> 'This is a voucherable deal.',
					'slug' => self::TERM, )
			);
			return $return['slug'];
		}

	}

	public static function get_url() {
		return get_term_link( self::TERM, self::TAX );
	}

	public static function is_voucher( Group_Buying_Deal $deal ) {
		$post_id = $deal->get_ID();
		$term = array_pop( wp_get_object_terms( $post_id, self::TAX ) );
		$voucher = FALSE;
		if ( !empty( $term ) && $term->slug = self::get_term_slug() ) {
			$voucher = TRUE;
		}
		return $voucher;
	}

	public function allowed_to_voucher( Group_Buying_Deal $deal, $user_id = null ) {
		if ( self::is_expired( $deal ) ) {
			return self::__( 'Expired Voucher!' );
		}
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( !$user_id ) {
			return self::__( 'User Error!' );
		}
		if ( !gb_has_purchased( $deal->get_id(), $user_id ) ) {
			return self::__( 'You need to purchase this voucher first!' );
		}
		$count = self::get_vouchers_by_user( $deal, $user_id );
		if ( $count >= self::get_limit( $deal ) ) {
			return self::__( 'Voucher Limit Exceeded!' );
		}
		return TRUE;
	}

	public function set_file_name( Group_Buying_Deal $deal, $file_name ) {
		return $deal->save_post_meta( array( self::$meta_keys['file_name'] => $file_name ) );
	}

	public function get_file_name( Group_Buying_Deal $deal ) {
		return $deal->get_post_meta( self::$meta_keys['file_name'] );
	}

	public function is_expired( Group_Buying_Deal $deal ) {
		$exp = $deal->get_voucher_expiration_date();
		if ( self::never_expires( $deal ) ) {
			return FALSE;
		}
		if ( current_time( 'timestamp' ) > $exp ) {
			return TRUE;
		}
		return FALSE;
	}

	public function never_expires( Group_Buying_Deal $deal ) {
		return $deal->get_voucher_expiration_date() == self::NO_EXPIRATION_DATE;
	}

	public function get_count( Group_Buying_Deal $deal ) {
		$count = $deal->get_post_meta( self::$meta_keys['count'] );
		if ( empty( $count ) ) {
			$count = self::set_count( $deal, 0, TRUE );
		}
		return $count;
	}

	public function set_count( Group_Buying_Deal $deal, $count = 1, $reset = FALSE ) {
		if ( !$reset ) {
			$count = self::get_count( $deal ) + $count;
		}
		$deal->save_post_meta( array( self::$meta_keys['count'] => $count ) );
		return $count;
	}

	public function set_voucher( Group_Buying_Deal $deal, $user_id = null, $talley = 1 ) {
		self::set_count( $deal, $talley );
		self::set_voucher_record( $deal, $user_id );
	}

	public function get_voucherers( Group_Buying_Deal $deal ) {
		$voters = $deal->get_post_meta( self::$meta_keys['voucherers'] );
		if ( empty( $voters ) || $voters = '' ) {
			return FALSE;
		}
		return $deal->get_post_meta( self::$meta_keys['voucherers'] );
	}

	public function set_voucher_record( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		$current_voucherers = self::get_voucherers( $deal );
		$voucherers = array_merge( (array)$current_voucherers, array( $user_id ) );
		$deal->save_post_meta( array( self::$meta_keys['voucherers'] => $voucherers ) );
		self::set_users_voucher_count( $deal, $user_id );
	}

	public function set_users_voucher_count( Group_Buying_Deal $deal, $user_id = null, $count = 1, $reset = FALSE ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( !$reset ) {
			$count = self::get_vouchers_by_user( $deal ) + $count;
		}
		$deal->save_post_meta( array( self::$meta_keys['user_prefix'].$user_id => $count ) );
		return $count;
	}

	public function get_vouchers_by_user( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		$count = $deal->get_post_meta( self::$meta_keys['user_prefix'].$user_id, TRUE );
		if ( empty( $count ) ) {
			$count = self::set_users_voucher_count( $deal, $user_id, 0, TRUE );
		}
		return $count;
	}

	public static function is_vouchers_query( WP_Query $query = NULL ) {
		$taxonomy = get_query_var( 'taxonomy' );
		if ( $taxonomy == self::TAX || $taxonomy == self::TAX || $taxonomy == self::TAX ) {
			return TRUE;
		}
		return FALSE;
	}

	public static function voucher_file( $args = array() ) {
		// TODO review voucher_url() function

		if ( !empty( $args ) ) extract( $args );

		// log file name
		$log_file = trailingslashit( $base_dir ) . 'vouchers.log';

		// Allowed extensions list in format 'extension' => 'mime type'
		// If myme type is set to empty string then script will try to detect mime type
		// itself, which would only work if you have Mimetype or Fileinfo extensions
		// installed on server.
		$allowed_ext = array (

			// archives
			'zip' => 'application/zip',

			// documents
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// executables
			'exe' => 'application/octet-stream',

			// images
			'gif' => 'image/gif',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',

			// audio
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/x-wav',

			// video
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo'
		);


		// Make sure program execution doesn't time out
		// Set maximum script execution time in seconds (0 means no limit)
		set_time_limit( 0 );

		if ( empty( $file_name ) ) {
			Group_Buying_Controller::set_message( "Something is wrong, you might want to tell someone." );
			return FALSE;
		}

		// Nullbyte hack fix
		if ( strpos( $file_name, "\0" ) !== FALSE )
			return FALSE;

		// get full file path (including subfolders)

		$file_path = self::find_file( trailingslashit( $base_dir ), $file_name );

		if ( !is_file( $file_path ) ) {
			Group_Buying_Controller::set_message( "File does not exist. Notify the site admin." );
			return FALSE;
		}

		// file size in bytes
		$fsize = filesize( $file_path );

		// file extension
		$fext = strtolower( substr( strrchr( $file_name, "." ), 1 ) );

		// check if allowed extension
		if ( !array_key_exists( $fext, $allowed_ext ) ) {
			Group_Buying_Controller::set_message( "Not allowed file type. (file type attempted:" .$fext. ")" );
			return FALSE;
		}

		// get mime type
		if ( $allowed_ext[$fext] == '' ) {
			$mtype = '';
			// mime type is not set, get from server settings
			if ( function_exists( 'mime_content_type' ) ) {
				$mtype = mime_content_type( $file_path );
			}
			else if ( function_exists( 'finfo_file' ) ) {
					$finfo = finfo_open( FILEINFO_MIME ); // return mime type
					$mtype = finfo_file( $finfo, $file_path );
					finfo_close( $finfo );
				}
			if ( $mtype == '' ) {
				$mtype = "application/force-voucher";
			}
		}
		else {
			// get mime type defined by admin
			$mtype = $allowed_ext[$fext];
		}

		// Browser will try to save file with this filename, regardless original filename.
		// You can override it if needed.
		$asfname = str_replace( array( '"', "'", '\\', '/' ), '', $file_name );
		if ( $asfname === '' ) $asfname = 'NoName';

		// set headers
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Cache-Control: public" );
		header( "Content-Description: File Transfer" );
		header( "Content-Type: $mtype" );
		header( "Content-Disposition: attachment; filename=\"$asfname\"" );
		header( "Content-Transfer-Encoding: binary" );
		header( "Content-Length: " . $fsize );

		// voucher
		// @readfile($file_path);
		$file = @fopen( $file_path, "rb" );
		if ( $file ) {
			while ( !feof( $file ) ) {
				print( fread( $file, 1024*8 ) );
				flush();
				if ( connection_status()!=0 ) {
					@fclose( $file );
					die();
				}
			}
			@fclose( $file );
		}
		self::set_voucher( $deal, $user_id );

		// log vouchers
		if ( !$log_option ) die();

		$f = @fopen( $log_file, 'a+' );
		if ( $f ) {
			@fputs( $f, date( "m.d.Y g:ia" )."	".$_SERVER['REMOTE_ADDR']."	".$file_name."\n" );
			@fclose( $f );
		}
	}

	public static function find_file( $dirname, $file_name, $file_path = '' ) {
		$dir = opendir( $dirname );
		while ( $file = readdir( $dir ) ) {
			if ( empty( $file_path ) && $file != '.' && $file != '..' ) {
				if ( is_dir( $dirname.'/'.$file ) ) {
					self::find_file( $dirname.'/'.$file, $file_name, $file_path );
				}
				else {
					if ( file_exists( $dirname.'/'.$file_name ) ) {
						return $dirname.'/'.$file_name;
					}
				}
			}
		}
	}

}
