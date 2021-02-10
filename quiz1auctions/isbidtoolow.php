<?php 
    require_once 'db.php';

    if(!isset($_GET['bid'])){
        die('Error: lastBidPrice parameter not provided');
    }
    if(!isset($_GET['id'])){
        die('Error: auction id parameter not provided');
    }

    $acutionId = $_GET['id'];
    $bid = $_GET['bid'];

    $result = mysqli_query($link, sprintf("SELECT * FROM auctions WHERE id='%s'",
                                            mysqli_real_escape_string($link, $acutionId)));
    if(!$result){
        die("SQL Query Failed: ".mysqli_error($link));
    }
    $userRecord = mysqli_fetch_row($result);
    if($userRecord){
        echo "ERROR: Username already registered";
    }
    if($userRecord['lastBidPrice'] >= $bid){
        echo "bid too low";
    }
?>