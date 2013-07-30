<?php

/**
* Creates the Passbook Options
*/
class GBS_Passbook_Options extends GBS_Passbook_Addon {
	const PASSWORD = 'gb_passbook_password';
	const TYPE = 'gb_passbook_typeid';
	const TEAMID = 'gb_passbook_teamid';
	const LOGO = 'gb_passbook_logo';
	const ICON = 'gb_passbook_icon';
	const ICON2 = 'gb_passbook_icon2';
	private static $password;
	private static $passtype;
	private static $teamid;
	private static $logo;
	private static $icon;
	private static $icon2;

	public static function init() {
		self::$password = get_option( self::PASSWORD );
		self::$passtype = get_option( self::TYPE );
		self::$teamid = get_option( self::TEAMID );
		self::$logo = get_option( self::LOGO );
		self::$icon = get_option( self::ICON );
		self::$icon2 = get_option( self::ICON2 );
		// Options
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_passbook_voucher_settings';
		add_settings_section( $section, self::__( 'Passbook Vouchers' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::PASSWORD );
		register_setting( $page, self::TYPE );
		register_setting( $page, self::TEAMID );
		register_setting( $page, self::LOGO );
		register_setting( $page, self::ICON );
		register_setting( $page, self::ICON2 );

		// Fields
		add_settings_field( self::PASSWORD, self::__( 'Certificate Password' ), array( get_class(), 'display_option' ), $page, $section );
		add_settings_field( self::TYPE, self::__( 'passTypeIdentifier' ), array( get_class(), 'display_type' ), $page, $section );
		add_settings_field( self::TEAMID, self::__( 'teamIdentifier' ), array( get_class(), 'display_teamid' ), $page, $section );
		add_settings_field( self::LOGO, self::__( 'Passbook Logo Url' ), array( get_class(), 'display_logo' ), $page, $section );
		add_settings_field( self::ICON, self::__( 'Passbook Icon' ), array( get_class(), 'display_icon' ), $page, $section );
		add_settings_field( self::ICON2, self::__( 'Passbook Icon@2x (retina)' ), array( get_class(), 'display_icon2' ), $page, $section );
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
	}

	public static function display_icon() {
		echo '<input name="'.self::ICON.'" id="'.self::ICON.'" type="text" value="'.self::$icon.'">';
	}

	public static function display_icon2() {
		echo '<input name="'.self::ICON2.'" id="'.self::ICON2.'" type="text" value="'.self::$icon2.'">';
	}

}