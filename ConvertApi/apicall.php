<?php 
//Secret :GWnkMxyCkVnea9bp
//API Key : 971420074

echo 'hii';die;
include('./autoload.php');
try{
// //use ConvertApi\ConvertApi;
// ConvertApi::setApiSecret('GWnkMxyCkVnea9bp');
// $result = ConvertApi::convert('pdf', [
//         'File' => 'kk.docx',
//     ], 'docx'
// );
// $result->saveFiles('');
}
catch(PDOException $e)
{
    echo $e
}
?>
