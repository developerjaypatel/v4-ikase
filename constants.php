<?php
/** Just a shortcut for {@link DIRECTORY_SEPARATOR} */
const DC = DIRECTORY_SEPARATOR;

/** usually, C:\inetpub\wwwroot\iKase.org, but might not be there in case of tests or CLI */
define('ROOT_PATH', (($_SERVER['DOCUMENT_ROOT'] ?? false) ?: __DIR__).DC);
define('DOC_LIMITS','35');

const API_PATH        = ROOT_PATH.'api'.DC;
const APILIB_PATH     = ROOT_PATH.'api_lib'.DC;
const IKLOCK_API_PATH = ROOT_PATH.'iklock'.DC.'api'.DC;

// const UPLOADS_PATH = ROOT_PATH.'uploads'.DC;
const SCANS_PATH   = ROOT_PATH.'scans'.DC;
const UPLOADS_PATH = 'D:'.DC.'uploads'.DC;
// const SCANS_PATH   = 'D:'.DC.'scans'.DC;


const MIN  = 60;
const HOUR = 60 * 60;

if (isset($_SERVER['SERVER_NAME'])) { //may not be available happen in CLI setups
    if (in_array($_SERVER['SERVER_NAME'], ['v4.ikase.org', 'ikase.org'])) {
        define('ENVIRONMENT', 'prod');
    } elseif (in_array($_SERVER['SERVER_NAME'], ['ikase.website'])) {
        define('ENVIRONMENT', 'stg');
    }
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'dev');
}

const IS_PROD    = ENVIRONMENT == 'prod';
const IS_STAGING = ENVIRONMENT == 'stg';
const IS_TEST    = ENVIRONMENT == 'test';
const IS_DEV     = ENVIRONMENT == 'dev';
const ISNT_PROD  = !IS_PROD;
