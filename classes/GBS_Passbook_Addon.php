<?php

/**
 * Load via GBS Add-On API
 */
class GBS_Passbook_Addon extends Group_Buying_Controller {

	public static function init() {
		require_once('GBS_Show_Passbook.php');
		GBS_Show_Passbook::init();

		require_once('GBS_Create_Passbook.php');
		GBS_Create_Passbook::init();
	}

	public static function gb_addon( $addons ) {
		$addons['passbook'] = array(
			'label' => self::__( 'Passbook Vouchers' ),
			'description' => self::__( 'Allows users to add vouchers to iOS Passbook.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init' ),
			),
			'active' => TRUE,
		);
		return $addons;
	}

}