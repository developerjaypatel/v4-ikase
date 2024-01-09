
<?php
// Include database configuration file
require_once ('dbConfig.php');

// Include URL Shortener library file
require_once ('Shortener.class.php');

// Initialize Shortener class and pass PDO object
$shortener = new Shortener($db);

// Long URL - Get Attachment Link
//https://v2.ikase.org/api/textmagic-rest-php-v2-master/src/uploads/ClientName_+18184555294/ClientName_+18184555294_2023-07-10-22-31-17_688.jpg
$longURL = 'https://v2.ikase.org/api/textmagic-rest-php-v2-master/src/uploads/ClientName_+18184555294/ClientName_+18184555294_2023-07-10-22-31-17_688.jpg';

// Prefix of the short URL 
//$shortURL_Prefix = 'http://localhost/textmagic-rest-php-v2-master/src/URL_Shortner/'; // with URL rewrite
$shortURL_Prefix = 'https://v2.ikase.org/api/textmagic-rest-php-v2-master/src/URL_Shortner/redirect.php?c='; // without URL rewrite

try{
    // Get short code of the URL
    $shortCode = $shortener->urlToShortCode($longURL);
    
    // Create short URL
    $shortURL = $shortURL_Prefix.$shortCode;
    
    // Display short URL
    //send this in SMS

    echo 'Short URL: <a href= "'.$shortURL.'" target="_blank">'."ikase.xyx".' </a> ';
}catch(Exception $e){
    // Display error
    echo $e->getMessage();
}

