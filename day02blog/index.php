<?php 
    require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Index</title>
</head>
<body>
    <div id="centeredContent">
    <?php 
        if(isset($_SESSION['blogUser'])){
            $username = $_SESSION['blogUser']['username'];
            echo "User $username logged in";
        }else{
            echo "Not logged in";
        }
    ?>
    </div>
</body>
</html>