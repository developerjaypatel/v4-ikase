<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Manually set the Ghostscript path

$output = [];
$returnVar = 0;

// Full paths to files
$pdfFile = "D:\\uploads\\1033\\83\\eams_forms\\4906H.pdf";
$fdfOutput = "D:\\uploads\\1033\\83\\eams_forms\\output.fdf";

// Run pdftk command
$cmd = "pdftk \"$pdfFile\" generate_fdf output \"$fdfOutput\" 2>&1";
exec($cmd, $output, $returnVar);

// Debug output
echo "Command: $cmd\n";
echo "Return Code: $returnVar\n";
print_r($output);



/* putenv("MAGICK_HOME=C:\\Program Files\\ImageMagick-7.1.1-Q16");
putenv("GS_LIB=C:\\Program Files\\gs\\gs10.00.0\\lib");
putenv("GS_PROG=C:\\Program Files\\gs\\gs10.00.0\\bin\\gswin64c.exe");
putenv("PATH=C:\\Program Files\\ImageMagick-7.1.1-Q16;C:\\Program Files\\gs\\gs10.00.0\\bin;" . getenv("PATH"));

// Convert PDF to PNG using ImageMagick
$image_magick = new Imagick();
$image_magick->readImage("C:\\inetpub\\wwwroot\\uploads\\1033\\278\\eams_forms\\4906H.pdf");

//$image_magick->writeImage("C:\\inetpub\\wwwroot\\uploads\\1033\\278\\eams_forms\\4906H.png");

echo "Conversion successful!";

echo shell_exec("C:\\Program Files (x86)\\PDFtk\\bin\\pdftk.exe --version");
echo shell_exec("C:\\Program Files\\ImageMagick-7.1.1-Q16-HDRI\\convert.exe -version"); */




?>