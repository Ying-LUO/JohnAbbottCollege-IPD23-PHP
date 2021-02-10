<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>
<body>
    <?php 
        require_once 'db.php';
        $sql = "SELECT * FROM auctions";
        $result = mysqli_query($link, $sql);
        if(!$result){
            die("SQL Query Failed: ".mysqli_error($link));
        }
        while($acutions = mysqli_fetch_assoc($result)){
            echo '<div class="auctionList">';
            
            echo '</div>';
        } 
    ?>
</body>
</html>