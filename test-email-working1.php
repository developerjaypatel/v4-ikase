<?php    
    $email_message = '<h2>Matrix Document Imaging -  Legal Records Uploaded</h2><p>The following records uploaded on Legal-Records.us</p><hr><p>Order Number:123</p>';

    $from = 'Matrix Document Imaging <support@legal-records.com>';
    
    // $sendTo = 'Matrix Document Imaging <MatrixDISrecords@gmail.com>, Matrix Document Imaging <matrixscheduling@gmail.com>, Matrix Document Imaging <jaypatel4396.jp6@gmail.com>';    
    $sendTo = 'Matrix Document Imaging <developermukesh3@gmail.com>';            

    $replyTo = 'Matrix Document Imaging <support@legal-records.com>';
    
    $subject = 'Records uploaded from the Legal-Records.us';
    $headers = array('Content-Type: text/html; charset=ISO-8859-1\r\n";',
    'From: ' . $from,
    'Reply-To: ' . $from,
    'Return-Path: ' . $from,
    );            

    mail($sendTo, $subject, $email_message, implode("\n", $headers));
    // end send email ----------------------------------------------
?>
