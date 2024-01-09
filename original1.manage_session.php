<?php
if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
	session_save_path(ROOT_PATH.'\sessions\\');  //TODO: move to ouside the repository
	ini_set('session.gc_maxlifetime', 3*60*60); // 3 hours
	ini_set('session.gc_probability', 1);
	ini_set('session.gc_divisor', 100);
	ini_set('session.cookie_secure', false);
	ini_set('session.use_only_cookies', true);
}
session_start();
