<?php
require __DIR__ . '/vendor/autoload.php';

use Hfig\MAPI\OLE\Pear\DocumentFactory;
use Hfig\MAPI\MapiMessageFactory;

// include_once('config.php');

// ==== CHECK FILE ====
if (!isset($_FILES['emailFile'])) {
    die("No file uploaded");
}

$fileTmp  = $_FILES['emailFile']['tmp_name'];
$fileName = $_FILES['emailFile']['name'];
$ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// ==== CREATE UPLOAD DIRECTORY ====
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ==== CREATE UNIQUE FILE NAME ====
$uniqueName = uniqid('mail_', true) . '.' . $ext;
$uploadPath = $uploadDir . '/' . $uniqueName;

// Move uploaded file to permanent folder
if (!move_uploaded_file($fileTmp, $uploadPath)) {
    die("Failed to move uploaded file.");
}

// ==== HELPER: SANITIZE HTML ====
function sanitize_html($html) {
    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    $html = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);
    return $html;
}

// ==== PARSE FILE ====
$subject = '';
$body    = '';
$isHtml  = false;

if ($ext === 'eml') {
    $content = file_get_contents($uploadPath);

    preg_match('/^Subject:(.*)$/mi', $content, $subjMatch);
    $subject = trim($subjMatch[1] ?? '');

    preg_match('/\r\n\r\n(.*)/s', $content, $bodyMatch);
    $rawBody = trim($bodyMatch[1] ?? '');

    $body = '<pre>' . htmlspecialchars($rawBody) . '</pre>';
    $isHtml = true;

} elseif ($ext === 'msg') {
    try {
        $docFactory = new DocumentFactory();
        $ole = $docFactory->createFromFile($uploadPath);

        $msgFactory = new MapiMessageFactory();
        $message = $msgFactory->parseMessage($ole);

        // var_dump($message);
         $properties = $message->properties;

        // Get subject and body
        $subject = $properties->get('subject') ?? '';
        $rawBody = $properties->get('body') ?? '';

        // Get body format (HTML or plain)
        $bodyHtml = $properties->get('bodyHTML');
        if ($bodyHtml) {
            $body = sanitize_html($bodyHtml);
            $isHtml = true;
        } else {
            $body = htmlspecialchars($rawBody);
            $isHtml = true;
        }

    } catch (Exception $e) {
        die("MSG parse error (hfig/mapi): " . $e->getMessage());
    }
} else {
    die("Unsupported file type");
}

// ==== SAVE TO DATABASE ====
// $stmt = $conn->prepare("INSERT INTO emails (file_name, subject, message, is_html, created_at) VALUES (?, ?, ?, ?, NOW())");
// $isHtmlInt = $isHtml ? 1 : 0;
// $stmt->bind_param("sssi", $uniqueName, $subject, $body, $isHtmlInt);
// $stmt->execute();

// if ($stmt->affected_rows > 0) {
    echo "✅ Saved successfully: <b>{$subject}</b> (file: {$uniqueName})";
// } else {
//     echo "❌ Failed to save email record.";
// }
?>
