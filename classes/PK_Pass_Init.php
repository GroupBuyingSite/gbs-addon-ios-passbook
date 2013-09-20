<?php
// GB Passbook AddOn
// pass.com.groupbuyingsite.passbook-addon
// defaultgbscertificatepassword


require GB_PBLIB_PATH . 'php-passkit/PKPass/PKPass.php';

/**
 * Initialize and setup PKPass
 */
class PK_Pass_Init extends PKPass {

	public function init() {
		if ( !class_exists('GBS_Passbook_Options') ) {
			require_once( 'GBS_Passbook_Options.php' );
		}
		GBS_Passbook_Options::init();

		$cert_path = ( file_exists( GB_PBCERT_PATH . 'Certificates.p12' ) ) ? GB_PBCERT_PATH . 'Certificates.p12' : GB_PB_PATH . 'certs/Certificates.p12';
		$pem = ( file_exists( GB_PBCERT_PATH . 'AppleWWDRCA.pem' ) ) ? GB_PBCERT_PATH . 'AppleWWDRCA.pem' : GB_PB_PATH . 'certs/AppleWWDRCA.pem';
		$pass = new PKPass();
		$pass->setCertificate( $cert_path );  // Set the path to your Pass Certificate (.p12 file)
		$pass->setCertificatePassword( GBS_Passbook_Options::$password );     // Set password for certificate
		$pass->setWWDRcertPath( $pem ); // Set the path to your WWDR Intermediate certificate (.pem file)
		return $pass;
	}

}
