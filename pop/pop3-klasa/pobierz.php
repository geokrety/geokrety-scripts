<?php

    require('mime_parser.php');
    require('rfc822_addresses.php');
    require("pop3.php");
    include_once("$geokrety_www/templates/konfig.php");

  /* Uncomment when using SASL authentication mechanisms */
    /*
    require("sasl.php");
    */

    stream_wrapper_register('pop3', 'pop3_stream');  /* Register the pop3 stream handler class */

    $pop3=new pop3_class;
    $pop3->hostname=POP_HOSTNAME;             /* POP 3 server host name                      */
    $pop3->port=POP_PORT;                         /* POP 3 server host port,
                                                usually 110 but some servers use other ports
                                                Gmail uses 995                              */
    $pop3->tls=POP_TLS;                            /* Establish secure connections using TLS      */
    $user=POP_USERNAME;                        /* Authentication user name                    */
    $password=POP_PASSWORD;                    /* Authentication password                     */
    $pop3->realm="";                         /* Authentication realm or domain              */
    $pop3->workstation="";                   /* Workstation for NTLM authentication         */
    $apop=0;                                 /* Use APOP authentication                     */
    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
    $pop3->debug=0;                          /* Output debug information                    */
    $pop3->html_debug=0;                     /* Debug information is in HTML                */
    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */



    if (($error=$pop3->Open())=="") {
        if (($error=$pop3->Login($user, $password, $apop))=="") {
            if (($error=$pop3->Statistics($messages, $size))=="") {
                //				echo "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";
                if ($messages>0) {
                    for ($message=1; $message <= $messages; $message++) {
                        $pop3->GetConnectionName($connection_name);
                        $message_file='pop3://'.$connection_name.'/'.$message;
                        $mime=new mime_parser_class;

                        /*
                        * Set to 0 for not decoding the message bodies
                        */
                        $mime->decode_bodies = 1;

                        $parameters=array(
                            'File'=>$message_file,
                            //'SaveBody'=>'/tmp',

                            /* Do not retrieve or save message body parts     */
                                'SkipBody'=>0,
                        );
                        $success=$mime->Decode($parameters, $decoded);


                        if (!$success) {
                            $error = '<h2>MIME message decoding error: '.HtmlSpecialChars($mime->error)."</h2>\n";
                        }
                        //echo '<h2>MIME message decoding error: '.HtmlSpecialChars($mime->error)."</h2>\n";
                        else {
                            $wiadomosci[$message]['data'] = $decoded[0]['Headers']['date:'];
                            $wiadomosci[$message]['temat'] = $decoded[0]['Headers']['subject:'];
                            $wiadomosci[$message]['tresc'] = $decoded[0]['Tresc'];
                        }
                    }
                    $pop3->DeleteMessage($message);
                } //if messages > 0
            } //for
        }
        $pop3->Close();
    }
