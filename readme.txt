# Passbook Vouchers
Ability to send users a voucher in iOS Passbook.

## Requirements
* PHP 5
* PHP [ZIP Support](http://php.net/manual/en/book.zip.php) (May be installed by default)
* Access to filesystem (Script must be able to create temporary folders)

## Installation
1. Upload and activate plugin.
2. Go to Group Buying > Add-ons, find Passbook Vouchers and activate the add-on.
3. Configure the pass under Group Buying > General Settings

## Template Modifications
Passbook Vouchers uses the PHP-PKPass library to build passes. Read more about it [here](https://github.com/tschoffelen/PHP-PKPass).
The json array for the pass is filterable via gb_passbook_vouchers_json_array.


## More Secure Setup

### Requesting the Pass Certificate
1. Go to the [iOS Provisioning portal](https://developer.apple.com/ios/manage/passtypeids/ios/manage)
2. Create a new Pass Type ID
3. Request the certificate and follow the directions to create a CSR file.
4. Download the .cer file and drag it into Keychain Access
5. Right click the certificate in Keychain Access and choose `Export 'pass.<id>'…`
6. Choose a password and export the file to a folder

### Configuring
1. Create a new directory under wp-content called "gbs-passbook", e.g. site-root/wp-content/gbs-passbook/...
2. Request the Pass certificate (`.p12`) and upload it to your server.
3. Set the password under Group Buying > General Settings > Passbook Vouchers
4. Download and import your [WWDR Intermediate certificate](https://developer.apple.com/certificationauthority/AppleWWDRCA.cer) to Keychain, export as `.pem` and upload it to the gbs-passbook directory created in step 1.
4. Change the `passTypeIdentifier` and `teamIndentifier` to the correct values, which can be found on the [iOS Provisioning portal](https://developer.apple.com/ios/manage/passtypeids/ios/manage) after clicking on 'Configure' next to the Pass ID, under Group Buying > General Settings > Passbook Vouchers.