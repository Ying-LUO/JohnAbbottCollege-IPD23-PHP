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
    <title>Login</title>
</head>
<body>
    <div id="centeredContent">
    <?php 
        // <<<MARKER heredoc
        function displayForm($username = "", $email = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Username: <input name="username" type="text" value="$username"><br>
                Password: <input name="password" type="password"><br>
                <input type="submit" value="Login">
                <a href="register.php">Register</a>
            </form>
            ENDMARKER;
            echo $form;
        }
        
        if(isset($_POST['username']) || isset($_POST['password'])){
            $username = $_POST['username'];
            $password = $_POST['password'];
            // verify inputs
            $result = mysqli_query($link, sprintf("SELECT * FROM users WHERE username='%s'",
                                            mysqli_real_escape_string($link, $username)));
            if(!$result){
                echo "SQL Query failed: ".mysqli_error($link);
                exit;
            }
            $userRecord = mysqli_fetch_assoc($result);
            $loginSuccessful = false;
            if($userRecord){
                if($userRecord['password'] == $password){
                    $loginSuccessful = true;
                }
            }
            // submission failed with error
            if(!$loginSuccessful){
                echo '<p class="errorMessage">Invalid username or password</p>';
                displayForm();
            }else{  
                unset($userRecord['password']); // for safty reason remove the password
                $_SESSION['blogUser'] = $userRecord;
                echo "<p>Login successful!</p>";
                echo '<p><a href="articleadd.php">Click here to continues</a></p>';
            }
        }else{  // first show
            displayForm();
        }
    ?>
    </div>
</body>
</html>