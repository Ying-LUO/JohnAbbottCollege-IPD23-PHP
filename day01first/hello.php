<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
        if(!isset($_GET['name']) || !isset($_GET['age'])){
            echo "ERROR: you must provide name and age in the URL";
        }else if(($_GET['age']) <=0 || ($_GET['age']) >150){
            echo "ERROR: your age must between 0-150";
        }else{
            $name = $_GET['name'];
            $age = $_GET['age'];
            echo "Hello $name, $age yrs old, nice to meet you!";
        }
    ?>
</body>
</html>