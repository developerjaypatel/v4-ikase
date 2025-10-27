<?php 
ini_set("com.allow_dcom","true");
// echo 'hii'
//Secret :GWnkMxyCkVnea9bp
//API Key : 971420074
error_reporting(E_ALL); ini_set('display_errors', 1); 
require_once('C:\inetpub\wwwroot\ikase.org\ConvertApi\autoload.php');
// echo 'hii';die;
use \ConvertApi\ConvertApi;

ConvertApi::setApiSecret('rXEf71d4nSWiYDb4');
$result = ConvertApi::convert('pdf', [
        'File' => 'C:\inetpub\wwwroot\ikase.org\ConvertApi\test.docx',
    ], 'docx'
);
$result->saveFiles('C:\inetpub\wwwroot\ikase.org\ConvertApi');

// $content = file_get_contents('C:\\inetpub\\wwwroot\\ikase.org\\ConvertApi\\kk.docx');
// header("Content-type: pdf");
// header("Content-Disposition: attachment; Filename = fullapp.pdf");
// set_time_limit(0);
    // function MakePropertyValue($name,$value,$osm){
    // $oStruct = $osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue");
    // $oStruct->Name = $name;
    // $oStruct->Value = $value;
    // return $oStruct;
    // }
    // function word2pdf($doc_url, $output_url){
    // //echo $output_url;
    // //Invoke the OpenOffice.org service manager
    // $osm = new COM("com.sun.star.ServiceManager") or die ("Please be sure that OpenOffice.org is installed.\n");
    // //Set the application to remain hidden to avoid flashing the document onscreen
    // print_r($osm);die;
    // $args = array(MakePropertyValue("Hidden",true,$osm));
    // //Launch the desktop
    // $oDesktop = $osm->createInstance("com.sun.star.frame.Desktop");
    // //Load the .doc file, and pass in the "Hidden" property from above
    // $oWriterDoc = $oDesktop->loadComponentFromURL($doc_url,"_blank", 0, $args);
    // //Set up the arguments for the PDF output
    // $export_args = array(MakePropertyValue("FilterName","writer_pdf_Export",$osm));
    // //print_r($export_args);
    // //Write out the PDF
    // $oWriterDoc->storeToURL($output_url,$export_args);
    // $oWriterDoc->close(true);
    // }
    // $output_dir = "C:\\inetpub\\wwwroot\\ikase.org\\ConvertApi\\";
    // $doc_file = "C:\\inetpub\\wwwroot\\ikase.org\\ConvertApi\\kk.docx";
    // $pdf_file = "kk.pdf";
    // $output_file = $output_dir . $pdf_file;
    // $doc_file = "file:///" . $doc_file;
    // $output_file = "file:///" . $output_file;
    // word2pdf($doc_file,$output_file);



      //Word Doc to PDF using Com
            // ini_set("com.allow_dcom","true");

            // try{
            //     $word = new com('word.application') or die('MS Word could not be loaded');
            // }
            // catch (com_exception $e)
            // {
            //         $nl = "<br />";
            //         echo $e->getMessage() . $nl;
            //         echo $e->getCode() . $nl;
            //         echo $e->getTraceAsString();
            //         echo $e->getFile() . " LINE: " . $e->getLine();
            //         $word->Quit();
            //         $word = null;
            //         die;

            // }

            // $word->Visible = 0;
            // $word->DisplayAlerts = 0;





            // try{
            // $doc = $word->Documents->Open('C:\\inetpub\\wwwroot\\ikase.org\\ConvertApi\\kk.docx');
            // }
            // catch (com_exception $e)
            // {
            //     $nl = "<br />";
            //     echo $e->getMessage() . $nl;
            //     echo $e->getCode() . $nl;
            //     echo $e->getFile() . " LINE: " . $e->getLine();
            //     $word->Quit();
            //     $word = null;
            //     die;
            // }
            // echo "doc opened";
            // try{
            //     $doc->ExportAsFixedFormat("test_image.pdf", 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);

            // }
            // catch (com_exception $e)
            // {
            //     $nl = "<br />";
            //     echo $e->getMessage() . $nl;
            //     echo $e->getCode() . $nl;
            //     echo $e->getTraceAsString();
            //     echo $e->getFile() . " LINE: " . $e->getLine();
            //     $word->Quit();
            //     $word = null;
            //     die;
            // }

            // echo "created pdf";
            // $word->Quit();
            // $word = null; 


?>
