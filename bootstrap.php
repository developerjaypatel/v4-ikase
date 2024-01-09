<?php
/**
 * This file should be required via php.ini's auto_prepend_file setting, so all legacy files get these novelties.
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'constants.php';
require_once ROOT_PATH.DC.'vendor'.DC.'autoload.php';

//FIXME: use E_ALL when it's feasible to run tests with proper notice checking
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', ISNT_PROD);

date_default_timezone_set('America/Los_Angeles');
