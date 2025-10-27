<?php
try {
	$filename = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\1111\\envelopes\\envelope_1032.docx";
    unlink($filename);
	die($filename . " unlinked");
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>