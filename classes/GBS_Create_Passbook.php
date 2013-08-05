<?php

/**
 * Creates the Passbook
 */
class GBS_Create_Passbook {

	public function pass( $voucher_id = 0, $output = TRUE ) {
		$pass = PK_Pass_Init::init();
		if ( !class_exists('GBS_Passbook_Options') ) {
			require_once( 'GBS_Passbook_Options.php' );
		}
		GBS_Passbook_Options::init();

		$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
		$purchase = $voucher->get_purchase();
		$account_id = $purchase->get_account_id();
		$account = Group_Buying_Account::get_instance_by_id( $account_id );
		$user_id = $account->get_user_id();

		$serial = gb_get_voucher_code( $voucher_id );
		$security_code = gb_get_voucher_security_code( $voucher_id );
		$voucher_name = sprintf( gb__( 'Voucher for %s' ), get_the_title( $voucher_id ) );
		$name = esc_attr__( gb_get_name( $user_id ) );

		$pass->setJSON( '{
		   	"passTypeIdentifier": "'.GBS_Passbook_Options::$passtype.'",
		   	"formatVersion": 1,
		    "organizationName": "'.get_option( 'blogname' ).'",
		    "teamIdentifier": "'.GBS_Passbook_Options::$teamid.'",
		   	"serialNumber": "'.$serial.'",
			"backgroundColor": "rgb(240,240,240)",
			"logoText": "'.get_option( 'blogname' ).'",
			"description": "'.$voucher_name.'",
			"storeCard": {
				"secondaryFields": [
					{
						"key": "name",
						"label": "NAME",
						"value": "'.$name.'"
					},
					{
						"key": "balance",
						"label": "CODE",
						"value": "'.$security_code.'"
					}
				],
				"backFields": [
					{
					"key": "id",
					"label": "Card Number",
					"value": "'.$serial.'"
					}
				]
			},
			"barcode": {
				"format": "PKBarcodeFormatPDF417",
				"message": "'.$serial.'",
				"messageEncoding": "iso-8859-1",
				"altText": "'.$serial.'"
			}
		}' );

		// add files to the PKPass package
		$pass->addFile( GBS_Passbook_Options::$icon );
		$pass->addFile( GBS_Passbook_Options::$icon2 );
		$pass->addFile( GBS_Passbook_Options::$logo );
		$pass->addFile( GBS_Passbook_Options::$bg );

		if ( !$pass->create( $output ) ) { // Create and output the PKPass
			if ( $output ) {
				echo 'Error: '.$pass->getError();
			}
			else {
				error_log( 'passbook error ' . print_r( $pass->getError(), TRUE ) );
			}
		}
		exit;
	}
}
