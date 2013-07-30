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


define( 'GB_PB_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action( 'plugins_loaded', 'gb_load_passbook' );
function gb_load_passbook() {
	if ( class_exists( 'Group_Buying_Controller' ) ) {
		require_once 'passbook.model.class.php';
		Group_Buying_Passbook_Addon::init();
	}
}
