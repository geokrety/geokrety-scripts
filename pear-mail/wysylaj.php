#!/usr/bin/env php

<?php

include 'Mail.php';
include 'Mail/mime.php' ;

$text = 'Text version of email';
$html = '<html><body>HTML version of email</body></html>';
$file = '/home/richard/example.php';
$crlf = "\n";
$hdrs = array(
              'From'    => "\"Company Name\" <geokrety@gmail.com>",
              'Subject' => 'Test mime message'
              );

$mime = new Mail_mime(array('eol' => $crlf));

$mime->setTXTBody($text);
$mime->setHTMLBody($html);
$mime->addAttachment($file, 'text/plain');

$body = $mime->get();
$hdrs = $mime->headers($hdrs);

$mail =& Mail::factory('mail');
$mail->send('stefaniak.mail@gmail.com', $hdrs, $body)

?>