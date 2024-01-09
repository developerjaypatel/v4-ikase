<?php	
//die(print_r($_SERVER));
error_reporting(E_ALL);
ini_set('display_errors', '1');

$to      = 'nick@kustomweb.com';
$subject = "The Subject Here " . date("H:i:s") . "multi";
$message = 'hello';
$body = '--5324ec482b31a780d815ae6e8b6515b2
Content-Type: multipart/alternative; boundary="mFtZWQgYXR0YWNobWVu"

--mFtZWQgYXR0YWNobWVu
Content-Type: text/plain; charset=utf-8;
Content-Transfer-Encoding: 7bit

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque vel 
dapibus arcu. Duis quam dui, ornare non mi nec, luctus faucibus massa. Vivamus 
quis purus in erat euismod ullamcorper vitae eget dolor. Aliquam tempor erat 
accumsan, consectetur ex et, rhoncus risus.

--mFtZWQgYXR0YWNobWVu

--5324ec482b31a780d815ae6e8b6515b2--';

$headers = 'From: latommy1@gmail.com' . "\r\n" .
    'Reply-To: latommy1@gmail.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion() . "\r\n" .
	'X-Priority: 2' . "\r\n" .
	'Content-Type: multipart/mixed; boundary="5324ec482b31a780d815ae6e8b6515b2"';
$blnMail = mail($to, $subject, $body, $headers);
if ($blnMail) {
	echo "sent " . date("H:i:s");
} else {
	echo "not sent";
}
die();


$headers = '
MIME-Version: 1.0
To: nick@kustomweb.com
From: George <george@kustomweb.com>
Reply-To: George <george@kustomweb.com>
Sender: george@kustomweb.com
Subject: The Subject Here ' . date("H:i:s") . '
X-Priority: 2
Content-Type: multipart/mixed; boundary="5324ec482b31a780d815ae6e8b6515b2"
';

$body = '--5324ec482b31a780d815ae6e8b6515b2
Content-Type: multipart/alternative; boundary="mFtZWQgYXR0YWNobWVu"

--mFtZWQgYXR0YWNobWVu
Content-Type: text/plain; charset=utf-8;
Content-Transfer-Encoding: 7bit

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque vel 
dapibus arcu. Duis quam dui, ornare non mi nec, luctus faucibus massa. Vivamus 
quis purus in erat euismod ullamcorper vitae eget dolor. Aliquam tempor erat 
accumsan, consectetur ex et, rhoncus risus.

--mFtZWQgYXR0YWNobWVu
Content-Type: text/html; charset=utf-8;
Content-Transfer-Encoding: 7bit

<html>
<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>
<body bgcolor="#FFFFFF" text="#000000">
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque vel 
dapibus arcu. Duis quam dui, ornare non mi nec, luctus faucibus massa. Vivamus 
quis purus in erat euismod ullamcorper vitae eget dolor. Aliquam tempor erat 
accumsan, consectetur ex et, rhoncus risus.
</body>
</html>

--mFtZWQgYXR0YWNobWVu--

--5324ec482b31a780d815ae6e8b6515b2
Content-Type: application/pdf; name="myfile.pdf"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="myfile.pdf"

JVBERi0xLjINOCAwIG9iag08PCAvTGVuZ3RoIDkgMCBSIC9GaWx0ZXIgL0ZsYXRlRGVjb2Rl
ID4+DXN0cmVhbQ1oQ51bbY/cNg7+BfsfhAUO11w3riW/B7gPaZEAAdpcm06RL8EBzoyn68uM
vZ3xZLv//khKsuUxNaMNiiabpUg+pKiHsmxJEcN/UsgiilP4ab2/+XF1I81vszSqclHIOEpj
sdrf/PC2EFVUpmK1vXkZxVKs1uJlJJVYPYrvPra7XVvvxYdIrE7rL83hhVj97+bNyjUoFam7
FnOB+tubGI3FZEkwmhpKXpVRnqJi0PCyjBJ1DjyOYqWBxxXp/1h3X+ov9abZt434pV0feoG/
ars/xU/9/qEZmm7diJ+abmgOr0TGeFNFEuXx5M4B95Idns/QAaJMI1IpKeXi9+ZhaPafm4NQ
cRwzNpK0iirlRvisRBZpVJa+PP51091kkjBWBXrJxUuZRjIXh0Z8FN3MnB5X5st5Kay9355n

--5324ec482b31a780d815ae6e8b6515b2--
';
$subject = "The Subject Here " . date("H:i:s");
$blnMail = mail("nick@kustomweb.com", $subject, $body, $headers);
if ($blnMail) {
	echo "sent " . date("H:i:s");
} else {
	echo "not sent";
}
