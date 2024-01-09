<?php

function getConnection(){
     $servername = "25.70.61.4";
     $username = "root";
     $password = "admin527#";
    $dbname = "ikase";
    
    //$servername = "localhost";
    //$username = "root";
    //$password = "";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function save_to_notes($q,$phone_number,$fullPathUploadedFile){ 

    echo "IN SIDE SAVE FUNCTION :" .$q. 'phone :' .$phone_number;

    if ($q !== "") {
    
      $conn = getConnection();
      
    //$sql = "INSERT INTO cse_notes(notes_uuid,type,subject, note, title,attachments, status) VALUES ('','SMS','SMS','$q','SMS Tittle','$fullPathUploadedFile','SMS Status')";
   $sql = "INSERT INTO sms_notes(notes_uuid,type,subject, note, title,attachments, status,sender,receiver) VALUES ('','SMS','SMS','$q','SMS Tittle','$fullPathUploadedFile','SMS Status',$phone_number,$phone_number)";
       
    
    if ($conn->query($sql) === TRUE) {
      echo "Records saved successfully";
    } else {
      echo "Error saving record: " . $conn->error;
    }
    
    $conn->close(); 
    }
    }




  
function upload_attachment($phone_number, $target_file, $temp_file){
    
 
  
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  
  // Check if image file is a actual image or fake image
  // if(isset($_POST["submit"])) {
  //   $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  //   if($check !== false) {
  //     echo "File is an image - " . $check["mime"] . ".";
  //     $uploadOk = 1;
  //   } else {
  //     echo "File is not an image.";
  //     $uploadOk = 0;
  //   }
  // }
  
  // Check if file already exists
  if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
  }
  
  // Check file size - 5MB Max
  // if ($_FILES["fileToUpload"]["size"] > 15242880) {
  //   echo "Sorry, your file is too large.";
  //   $uploadOk = 0;
  // }
  
  
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "txt") {
    echo "Sorry, This file Type is not allowed.";
    $uploadOk = 0;
  }
  
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
  // if everything is ok, try to upload file
  } else {   
    
   // echo "folder--". $folder;
  // $fileName = moveUploadedFiles();
  
  }  
}

// function moveUploadedFilesToAlFolders($phone_numbers_arr,$folder,$target_file,$temp_file){

//   try {
//     $a = $folder.$target_file;
//      if (move_uploaded_file($temp_file , $a)) {

//       copy($folder . '1.jpg', $folder . '2.jpg');
//       copy($folder . '1.jpg', $folder . '3.jpg');

//      $renamedFile = name_attachment($folder,$target_file,$phone_number);
//      return $renamedFile;
//      echo "The Attached files have been uploaded. ";
//    } }catch(Exception $e){
//      // Display error
//      echo $e->getMessage();
//  }

// }


// function moveUploadedFiles($phone_number,$folder,$target_file,$temp_file){

//   try {
//     $a = $folder.$target_file;
//      if (move_uploaded_file($temp_file , $a)) {
//      $renamedFile = name_attachment($folder,$target_file,$phone_number);
//      return $renamedFile;
//      echo "The Attached files have been uploaded. ";
//    } }catch(Exception $e){
//      // Display error
//      echo $e->getMessage();
//  }

// }


function name_attachment($folder,$target_file,$phone_number){
  $set_name = 'ClientName_'.$phone_number.'_'.date('Y-m-d-H-i-s').'_'.rand(5,1000);
  $fname = preg_replace('/(.+?)\.([^\.]+)$/i',$set_name.'.$2',$target_file); 
  rename($folder.'/'.$target_file,$folder.'/'.$fname);

  //echo "set_name ---" .$set_name;
  //echo "fname ---" .$fname;

  return $folder.$fname;
}

function create_upload_directory($target_dir,$phone_number){  
  if (!file_exists($target_dir."ClientName_".$phone_number)) {
    mkdir($target_dir."ClientName_".$phone_number, 0777, true);  
    //echo "New Folder Created. ";
    }   
}


function save_complete_API_response($result){
  $target_dir = "backupOriginalAPIResponse/";
  $file = fopen("text.txt", "w");
fwrite($file, $result);
fwrite($file, "\n Skies are blue");
fclose($file);
}


?>