<?php

/**
* Creates the Passbook Options
*/
class GBS_Passbook_Options extends GBS_Passbook_Addon {
	const PASSWORD = 'gb_passbook_vouchers_password_v2';
	const TYPE = 'gb_passbook_vouchers_typeid_v2';
	const TEAMID = 'gb_passbook_vouchers_teamid_v2';
	const LOGO = 'gb_passbook_vouchers_logo_v2';
	const ICON = 'gb_passbook_vouchers_icon_v2';
	const ICON2 = 'gb_passbook_vouchers_icon2_v2';
	const BG = 'gb_passbook_vouchers_background_v2';
	public static $password;
	public static $passtype;
	public static $teamid;
	public static $logo;
	public static $icon;
	public static $icon2;
	public static $bg;

	public static function init() {
		self::$password = get_option( self::PASSWORD, 'defaultgbscertificatepassword' );
		self::$passtype = get_option( self::TYPE, 'pass.com.groupbuyingsite.passbook-addon' );
		self::$teamid = get_option( self::TEAMID, 'WERN56M6YH' );
		self::$logo = get_option( self::LOGO, GB_PB_PATH . '/temp/logo.png' );
		self::$icon = get_option( self::ICON, GB_PB_PATH . '/temp/icon.png'  );
		self::$icon2 = get_option( self::ICON2, GB_PB_PATH . '/temp/icon@2x.png'  );
		self::$bg = get_option( self::BG, GB_PB_PATH . '/temp/background.png'  );

		// Options
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_passbook_vouchers_settings';
		add_settings_section( $section, self::__( 'Passbook Vouchers' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::PASSWORD );
		register_setting( $page, self::TYPE );
		register_setting( $page, self::TEAMID );
		register_setting( $page, self::LOGO );
		register_setting( $page, self::ICON );
		register_setting( $page, self::ICON2 );
		register_setting( $page, self::BG );

		// Fields
		//add_settings_field( self::PASSWORD, self::__( 'Certificate Password' ), array( get_class(), 'display_option' ), $page, $section );
		//add_settings_field( self::TYPE, self::__( 'passTypeIdentifier' ), array( get_class(), 'display_type' ), $page, $section );
		//add_settings_field( self::TEAMID, self::__( 'teamIdentifier' ), array( get_class(), 'display_teamid' ), $page, $section );
		add_settings_field( self::LOGO, self::__( 'Passbook Logo' ), array( get_class(), 'display_logo' ), $page, $section );
		add_settings_field( self::ICON, self::__( 'Passbook Icon' ), array( get_class(), 'display_icon' ), $page, $section );
		add_settings_field( self::ICON2, self::__( 'Passbook Icon@2x (retina)' ), array( get_class(), 'display_icon2' ), $page, $section );
		add_settings_field( self::BG, self::__( 'Passbook Background Image' ), array( get_class(), 'display_bg' ), $page, $section );
	}

	public function display_settings_section() {
		gb_e( 'Documentation on how to configure Passbook Vouchers can be found within the add-on download, readme.txt.' );
	}

	public static function display_option() {
		echo '<input name="'.self::PASSWORD.'" id="'.self::PASSWORD.'" type="text" value="'.self::$password.'">';
	}

	public static function display_type() {
		echo '<input name="'.self::TYPE.'" id="'.self::TYPE.'" type="text" value="'.self::$passtype.'">';
	}

	public static function display_teamid() {
		echo '<input name="'.self::TEAMID.'" id="'.self::TEAMID.'" type="text" value="'.self::$teamid.'">';
	}

	public static function display_logo() {
		echo '<input name="'.self::LOGO.'" id="'.self::LOGO.'" type="text" value="'.self::$logo.'">';
		echo '<br/><p class="desc">Full filepath necessary, not a URL.</p>';
	}

	public static function display_icon() {
		echo '<input name="'.self::ICON.'" id="'.self::ICON.'" type="text" value="'.self::$icon.'">';
		echo '<br/><p class="desc">Full filepath necessary, not a URL.</p>';
	}

	public static function display_icon2() {
		echo '<input name="'.self::ICON2.'" id="'.self::ICON2.'" type="text" value="'.self::$icon2.'">';
		echo '<br/><p class="desc">Full filepath necessary, not a URL.</p>';
	}

	public static function display_bg() {
		echo '<input name="'.self::BG.'" id="'.self::BG.'" type="text" value="'.self::$bg.'">';
		echo '<br/><p class="desc">Full filepath necessary, not a URL.</p>';
	}

}