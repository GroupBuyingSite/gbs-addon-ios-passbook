<?php

/**
 * Creates the Passbook
 */
class GBS_Create_Passbook {

	public function pass( $voucher_id = 0, $output = TRUE, $validation = FALSE ) {
		
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
		$expiration = ( gb_get_voucher_expiration_date( $voucher_id ) ) ? date( get_option( 'date_format' ), gb_get_voucher_expiration_date( $voucher_id ) ) : gb__('No Expiration') ;
		$instructions = gb_get_voucher_usage_instructions( $voucher_id );
		$legal = gb_get_voucher_legal( $voucher_id );
		$fine_print = gb_get_univ_voucher_fine_print( $voucher_id );
		$voucher_name = sprintf( gb__( 'Voucher for %s' ), get_the_title( $voucher_id ) );
		$name = esc_attr__( gb_get_name( $user_id ) );

		$json = '{
		   	"passTypeIdentifier": "'.GBS_Passbook_Options::$passtype.'",
		   	"formatVersion": 1,
		    "organizationName": "'.addcslashes(get_option( 'blogname' ),'"').'",
		    "teamIdentifier": "'.GBS_Passbook_Options::$teamid.'",
		   	"serialNumber": "'.addcslashes($serial,'"').'",
			"backgroundColor": "rgb(240,240,240)",
			"logoText": "'.addcslashes(get_option( 'blogname' ),'"').'",
			"description": "'.addcslashes($voucher_name,'"').'",
			"storeCard": {
				"secondaryFields": [
					{
						"key": "name",
						"label": "'.gb__('NAME').'",
						"value": "'.addcslashes($name,'"').'"
					},
					{
						"key": "balance",
						"label": "'.gb__('EXPIRATION').'",
						"value": "'.addcslashes($expiration,'"').'"
					}
				],
				"backFields": [
					{
					"key": "id",
					"label": "'.gb__('Voucher Code').'",
					"value": "'.addcslashes($serial,'"').'"
					},
					{
					"key": "security",
					"label": "'.gb__('Reference').'",
					"value": "'.addcslashes($security_code,'"').'"
					},
					{
					"key": "instructions",
					"label": "'.gb__('Instructions').'",
					"value": "'.addcslashes($instructions,'"').'"
					},
					{
					"key": "fineprint",
					"label": "'.gb__('Fine Print').'",
					"value": "'.addcslashes($fine_print,'"').'"
					},
					{
					"key": "legal",
					"label": "'.gb__('Terms').'",
					"value": "'.addcslashes($legal,'"').'"
					}
				]
			},
			"barcode": {
				"format": "PKBarcodeFormatPDF417",
				"message": "'.addcslashes($serial,'"').'",
				"messageEncoding": "iso-8859-1",
				"altText": "'.addcslashes($serial,'"').'"
			}
		}';

		if ( !$validation ) {
			// Validation
			require GB_PBLIB_PATH . 'php-passkit/shared/PKLog.php';
			require GB_PBLIB_PATH . 'php-passkit/PKValidate/PKValidate.php';

			// Load validator class
			$validator = new PKValidate();

			// Load pass.json file for validation
			$result = $validator->validate( $json );
			error_log( 'validation result ' . print_r( $result, TRUE ) );
		}

		$pass = PK_Pass_Init::init();
		$pass->setJSON( apply_filters( 'gb_passbook_vouchers_json_array', $json, $voucher_id, $output ) );

		// add files to the PKPass package
		$pass->addFile( GBS_Passbook_Options::$icon, 'icon.png' );
		$pass->addFile( GBS_Passbook_Options::$icon2, 'icon@2x.png' );
		$pass->addFile( GBS_Passbook_Options::$logo, 'logo.png' );
		$pass->addFile( GBS_Passbook_Options::$bg, 'strip.png' );

		$passbook = $pass->create( $output );

		// Fail
		if ( !$passbook ) { // Create and output the PKPass
			if ( $output ) {
				echo 'Error: '.$pass->getError();
			}
			else {
				error_log( 'passbook error ' . print_r( $pass->getError(), TRUE ) );
			}
		}

		// Success
		if ( !$output ) {
			return $passbook;
		} else { // exit since the passbook was already sent to the browser.
			exit;
		}
	}
}
