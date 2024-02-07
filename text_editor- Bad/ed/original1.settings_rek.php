<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

session_start();
session_write_close();

$filename = "C:\\inetpub\\wwwroot\\iKase.website\\api\\chain.config";
$version_number = "8";

$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$key = base64_decode($contents);
$key = base64_decode($key);
$key = base64_decode($key);

define("CRYPT_KEY", $key);

date_default_timezone_set('America/Los_Angeles');

$MySqlHostname = "localhost";
$MySqlUsername = "gtg_caseuser";
$MySqlPassword = "thecase";
$db = "gtg_thecase";
$dbname = $db;	

if (isset($_SERVER['DOCUMENT_ROOT'])) {
	if ($_SERVER['DOCUMENT_ROOT']=="C:\\inetpub\\wwwroot\\iKase.website") {
		$MySqlHostname = "ikase.website";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "rek";	
		$dbname = $db;	

		
		$db = $dbname;
	}
}


//echo $MySqlHostname;
//this key needs to gotten from another server
$crypt_key = CRYPT_KEY;

DEFINE ("SQL_PERSONX", "SELECT 
			pers.`personx_id` `person_id`,
			pers.`personx_uuid` `person_uuid`,
			pers.`parent_personx_uuid` `parent_person_uuid`,
			CAST(AES_DECRYPT(pers.`full_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `full_name`,
			CAST(AES_DECRYPT(pers.`company_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `company_name`,
			CAST(AES_DECRYPT(pers.`first_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `first_name`,
			CAST(AES_DECRYPT(pers.`middle_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `middle_name`,
			CAST(AES_DECRYPT(pers.`last_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `last_name`,
			CAST(AES_DECRYPT(pers.`aka`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `aka`,
			CAST(AES_DECRYPT(pers.`preferred_name`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `preferred_name`,
			CAST(AES_DECRYPT(pers.`full_address`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `full_address`,
			pers.`longitude`,
			pers.`latitude`,
			CAST(AES_DECRYPT(pers.`street`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `street`,
			pers.`city`,
			pers.`state`,
			pers.`zip`,
			CAST(AES_DECRYPT(pers.`suite`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `suite`,
			CAST(AES_DECRYPT(pers.`phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `phone`,
			CAST(AES_DECRYPT(pers.`email`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `email`,
			CAST(AES_DECRYPT(pers.`fax`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `fax`,
			CAST(AES_DECRYPT(pers.`work_phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `work_phone`,
			CAST(AES_DECRYPT(pers.`cell_phone`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `cell_phone`,
			CAST(AES_DECRYPT(pers.`work_email`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `work_email`,
			CAST(AES_DECRYPT(pers.`ssn`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `ssn`,
			CAST(AES_DECRYPT(pers.`ssn_last_four`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `ssn_last_four`,
			CAST(AES_DECRYPT(pers.`dob`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `dob`,
			CAST(AES_DECRYPT(pers.`license_number`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `license_number`,
			pers.`title`,
			CAST(AES_DECRYPT(pers.`ref_source`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `ref_source`,
			CAST(AES_DECRYPT(pers.`salutation`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `salutation`,
			pers.`age`,
			pers.`priority_flag`,
			pers.`gender`,
			pers.`language`,
			CAST(AES_DECRYPT(pers.`birth_state`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `birth_state`,
			CAST(AES_DECRYPT(pers.`birth_city`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `birth_city`,
			pers.`marital_status`,
			pers.`legal_status`,
			CAST(AES_DECRYPT(pers.`spouse`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `spouse`,
			CAST(AES_DECRYPT(pers.`spouse_contact`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `spouse_contact`,
			CAST(AES_DECRYPT(pers.`emergency`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `emergency`,
			CAST(AES_DECRYPT(pers.`emergency_contact`, '" . CRYPT_KEY . "')  AS CHAR(10000) CHARACTER SET utf8) `emergency_contact`,
			pers.`last_updated_date`,
			pers.`last_update_user`,
			pers.`deleted`,
			pers.`customer_id`,
			pers.personx_id id, pers.personx_uuid uuid
			  
			FROM `cse_personx` pers 
			WHERE pers.deleted = 'N'
			AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by pers.personx_id");
?>