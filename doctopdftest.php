<?php
error_reporting(E_ALL);
ini_set("display_errors", "ON");
$file_path = 'C:\inetpub\wwwroot\iKase.org\ConvertApi\test.docx';
$secret = 'rXEf71d4nSWiYDb4';

if (file_exists($file_path)) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($curl, CURLOPT_VERBOSE, true);
  $verbose = fopen('php://temp', 'w+');
  curl_setopt($curl, CURLOPT_STDERR, $verbose);
  curl_setopt($curl, CURLOPT_USERAGENT, 'ConvertAPI-PHP/1.1.0');
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/octet-stream'));
  curl_setopt($curl, CURLOPT_URL, "https://v2.convertapi.com/docx/to/pdf?secret=".$secret);
  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CurlFile($file_path)));
  $result = curl_exec($curl);  

    if ($result === FALSE) {
        printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
    }

    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);

    echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";


  if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
      file_put_contents("result.pdf", $result);
  } else {
      print("Server returned error:\n".$result."\n");
  }
} else {
  print('File does not exist: '.$file_path."\n");
}