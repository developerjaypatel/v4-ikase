<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
var_dump($_FILES);
echo "</pre>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["file"]["name"]);

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        echo "Upload successful!";
    } else {
        echo "Upload failed!";
    }
} else {
    echo "No file uploaded.";
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
