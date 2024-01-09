<?php

if (!extension_loaded('zbarcode')) {
	echo "Skip.<br />ZBar is not loaded<br />Loaded Extensions<br />";
	$extensions = get_loaded_extensions ();
	rsort($extensions);
	foreach($extensions as $extension) {
		echo "<strong>" . $extension . "</strong><br />";
	}
	die();
} else {
	die("ZBar loaded!!");
}
?>