<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form>
        Name: <input name="name" type="text">
        <br>
        Age: <input name="age" type="number">
        <br>
        <input type="submit" value="Say Hello">
    </form>
    <?php 
        if(isset($_GET['name'])){
            $name = $_GET['name'];
            $age = $_GET['age'];
            echo "Hi, $name, you are $age yrs old";
        }
    ?>
    
</body>
</html>