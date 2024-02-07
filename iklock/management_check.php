<?php
switch (strtolower($USERNAME)) {
	case "myadmin":
		$blnManagement = true;
		break;
	case "tommy1":
		$blnManagement = true;
		break;
	default;
		$blnManagement = false;
		break;				
}

?>
