<?php 
    $dbUser = 'quiz1auctions';
    $dbPass = 'OC4NRZAayb0hWimY';
    $dbName = 'quiz1auctions';
    $dbHost = 'localhost:3333';

    // @ exclude the warning from php because the code after will handle the exception
    // if no exception handling below, MUST remove the @ to let error throw
    // global variable
    $link = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    // check if connection failed
    if(mysqli_connect_errno()){   // plus is used for calculate, not for concactenate, use dot
        echo "Fatal error: Failed to connect to MySQL: ".mysqli_connect_error();
        exit; // halt the script
    }

?>