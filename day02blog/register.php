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
        function displayForm($username = "", $email = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Username: <input name="username" type="text" value="$username"><br>
                Email: <input name="email" type="email" value="$email"><br>
                Password: <input name="pass1" type="password"><br>
                Password (repeated): <input name="pass2" type="password"><br>
                <input type="submit" value="Say Hello">
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
                
            }
            if(filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE){
                array_push($errorList, "email is invalid");
                $email = "";
            }else{
                // but is the email already in use?

            }
            // submission failed with error
            if($errorList){
                echo '<ul class="errorMessage">';
                foreach($errorList as $error){
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                displayForm($username, $email);
            }else{  // submission successful
                // parsing and save data into database
                // need to handle the database exception

                //$sql = "INSERT INTO people VALUES (NULL, '$name', '$age')"; 

                // this is dangerous
                // due to SQL injection, like back slash, e.g. Tommy's Friend will have exception
                // or if UPDATE users WHERE email='hacker@gamil.com' SET role='Admin' -- by execute the sql script, this user will be changed to Admin which is dangerous

                $sql = sprintf("INSERT INTO people VALUES (NULL, '%s', '%s')",
                                mysqli_real_escape_string($link, $username),
                                mysqli_real_escape_string($link, $email),
                                );

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