<?php
/*
Plugin Name: Group Buying Addon - Passbook Vouchers
Version: 1.1
Description: Ability to send users a voucher in iOS Passbook.
Plugin URI: http://groupbuyingsite.com/marketplace
Author: GroupBuyingSite.com
Author URI: http://groupbuyingsite.com/features
Plugin Author: Daniel Cameron and Nathan Stryker
Plugin Author URI: http://sproutventure.com/
Text Domain: group-buying
*/


define( 'GB_PBLIB_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . 'lib/' );
define( 'GB_PBCERT_PATH', WP_PLUGIN_DIR . '/gbs-passbook/' );

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action( 'plugins_loaded', 'gb_load_passbook' );
function gb_load_passbook() {
	$gbs_min_version = '4.4';
	if ( class_exists( 'Group_Buying_Controller' ) && version_compare( Group_Buying::GB_VERSION, $gbs_min_version, '>=' ) ) {
		require_once 'classes/GBS_Passbook_Addon.php';

		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( 'GBS_Passbook_Addon', 'gb_addon' ), 10, 1 );
	}
}