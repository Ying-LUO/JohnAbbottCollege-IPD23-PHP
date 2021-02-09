<?php 
    // include will issue just a warning, require will result a fatal error
    require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Register</title>
</head>
<body>
    <div id="centeredContent">
    <?php 
        // <<<MARKER heredoc
        function displayForm($username = "", $email = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Username: <input name="username" type="text" value="$username"><br>
                Email: <input name="email" type="email" value="$email"><br>
                Password: <input name="pass1" type="password"><br>
                Password (repeated): <input name="pass2" type="password"><br>
                <input type="submit" value="Register">
            </form>
            ENDMARKER;
            echo $form;
        }
        
        if(isset($_POST['username']) || isset($_POST['email'])){
            $username = $_POST['username'];
            $email = $_POST['email'];
            $pass1 = $_POST['pass1'];
            $pass2 = $_POST['pass2'];
            // verify inputs
            $errorList = array();
            if(preg_match('/^[a-z0-9]{4,20}$/', $username) != 1){
                $errorList[] = "Username must be 4-20 characters long made up of lower-case and numbers";
                $username = "";
            }else{
                // but the username already in use
                $result = mysqli_query($link, sprintf("SELECT * FROM users WHERE username='%s'",
                                                mysqli_real_escape_string($link, $username)));
                if(!$result){
                    echo "SQL Query failed: ".mysqli_error($link);
                    exit;
                }
                $userRecord = mysqli_fetch_assoc($result);
                if($userRecord){
                    array_push($errorList, "This username is already registered");
                    $username = "";
                }
            }
            if(filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE){
                array_push($errorList, "email is invalid");
                $email = "";
            }else{
                // but is the email already in use?
                $result = mysqli_query($link, sprintf("SELECT * FROM users WHERE email='%s'",
                                                mysqli_real_escape_string($link, $email)));
                if(!$result){
                    echo "SQL Query failed: ".mysqli_error($link);
                    exit;
                }
                $userRecord = mysqli_fetch_assoc($result);
                if($userRecord){
                    array_push($errorList, "This email is already registered");
                    $email = "";
                }
            }
            if($pass1 != $pass2){
                $errorList[] = "Passwords do not match";
            }else{
                if(strlen($pass1)< 6 || strlen($pass1) > 100
                    ||(preg_match("/[A-Z]/", $pass1) == FALSE)
                    ||(preg_match("/[a-z]/", $pass1) == FALSE)
                    ||(preg_match("/[0-9]/", $pass1) == FALSE)){
                            array_push($errorList, "Password must be 6-100 characters long, "
                            ."with at least one uppercase, one lowercase and one digit in it");
                    }
            }
            // submission failed with error
            if($errorList){
                echo '<ul class="errorMessage">';
                foreach($errorList as $error){
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                displayForm($username, $email);
            }else{  
                $sql = sprintf("INSERT INTO users VALUES (NULL, '%s', '%s', '%s')",
                                mysqli_real_escape_string($link, $username),
                                mysqli_real_escape_string($link, $email),
                                mysqli_real_escape_string($link, $pass1),
                                );

                if(!mysqli_query($link, $sql)){
                    echo "Fatal Error: Failed to execute SQL Query: " .mysqli_error($link);
                }
                echo "<p>Registration successful!</p>";
                echo '<p><a href="login.php">Click here to login</a></p>';
            }
        }else{  // first show
            displayForm();
        }
    ?>
    </div>
</body>
</html>