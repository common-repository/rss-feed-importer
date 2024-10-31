<?php
    include_once('google-plus/autoload.php');
    include('google-plus/src/Google/Client.php');
    
    $client_id = 'symbolic-heaven-766';
    $client_secret = 'QFsvaXBMDBC8qT_Bjruf-ZzP';
    $redirect_uri = 'http://quanticpost.com/wordpress';
    
    $client = new Google_Client();
    $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
    $client->originalContent = 'testing';
?>