<?php 
    require_once 'db.php';

    if(!isset($_GET['username'])){
        die('Error: username parameter not provided');
    }

    $username = $_GET['username'];  // expect there is a username argument in the url

    $result = mysqli_query($link, sprintf("SELECT * FROM users WHERE username='%s'",
                                            mysqli_real_escape_string($link, $username)));
    if(!$result){
        die("SQL Query Failed: ".mysqli_error($link));
    }
    $userRecord = mysqli_fetch_assoc($result);
    if($userRecord){
        echo "ERROR: Username already registered";
    }
?>