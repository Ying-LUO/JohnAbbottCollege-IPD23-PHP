<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Document</title>
</head>
<body>
    <div id="centeredContent">
    <?php 
        // include will issue just a warning, require will result a fatal error
        require_once 'db.php';
        // <<<MARKER heredoc
        function displayForm($name = "", $age = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Name: <input name="name" type="text" value="$name"><br>
                Age: <input name="age" type="text" value="$age"><br>
                <input type="submit" value="Say Hello">
            </form>
            ENDMARKER;
            echo $form;
        }
        
        if(isset($_POST['name']) && isset($_POST['age'])){
            $name = $_POST['name'];
            $age = $_POST['age'];
            // verify inputs
            $errorList = array();
            if(strlen($name)< 2 || strlen($name) > 50){
                array_push($errorList, "name must be 2-50");
                $name = "";
            }
            if(filter_var($age, FILTER_VALIDATE_INT) === FALSE || $age < 0 || $age >150){
                array_push($errorList, "age must be 0-150");
                $age = "";
            }
            // submission failed with error
            if($errorList){
                echo '<ul class="errorMessage">';
                foreach($errorList as $error){
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                displayForm($name, $age);
            }else{  // submission successful
                // parsing and save data into database
                // need to handle the database exception
                $sql = "INSERT INTO people VALUES (NULL, '$name', '$age')";
                if(!mysqli_query($link, $sql)){
                    echo "Fatal Error: Failed to execute SQL Query: " .mysqli_error($link);
                }
                echo "Hello $name, you are $age years old";
            }
        }else{  // first show
            displayForm();
        }
    ?>
    </div>
</body>
</html>