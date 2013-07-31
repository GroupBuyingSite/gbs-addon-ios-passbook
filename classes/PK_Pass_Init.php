<?php

require GB_PBLIB_PATH . 'php-passkit/PKPass/PKPass.php';

/**
 * Initialize and setup PKPass
 */
class PK_Pass_Init extends PKPass {

	public function init() {
		GBS_Passbook_Options::init();

		$pass = new PKPass();
		$pass->setCertificate( GB_PBCERT_PATH . 'Certificate.p12' );  // Set the path to your Pass Certificate (.p12 file)
		$pass->setCertificatePassword( GBS_Passbook_Options::$password );     // Set password for certificate
		$pass->setWWDRcertPath( GB_PBCERT_PATH . 'AppleWWDRCA.pem' ); // Set the path to your WWDR Intermediate certificate (.pem file)
		return $pass;
	}

}
