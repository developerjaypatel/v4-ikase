<?php
set_time_limit(0);
$params = $_GET;
//$url = 'http://kustomweb.xyz/a1_archive/archive.php?db=reyes&params=NzMyMXw1MHxyZXllcw==&source=ikase.website';
$url = 'http://kustomweb.xyz/a1_archive/archive.php?'.http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
	
	// Check the return value of curl_exec(), too
    if ($data === false) {
        throw new \Exception(curl_error($ch), curl_errno($ch));
    }

    curl_close($ch);

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	if(!empty($params['file_name'])) {
		header('Content-Disposition: attachment; filename='.$params["file_name"]);
	} else {
		header('Content-Disposition: attachment; filename=document');
	}
	//header('Content-Disposition: attachment; filename='.isset($params['file_name']) ? $params["file_name"]: "document");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	ob_clean();
	flush();
	echo $data;
	exit;