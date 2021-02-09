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
    <title>Add new article</title>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>tinymce.init({selector:'textarea[name=body]'});</script>
</head>
<body>
    <div id="centeredContent">
    <?php 
        function displayForm($title = "", $body = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Title: <input name="title" type="text" value="$title"><br>
                <textarea name="body" cols="60" rows="10">$body</textarea><br>
                <input type="submit" value="Post article">
            </form>
            ENDMARKER;
            echo $form;
        }

        // only logged in user may access this script
        if(!isset($_SESSION['blogUser'])){
            echo '<p>Please login first<a href="index.php">Click to continue</a></p>';
        }else{
            if(isset($_POST['title']) || isset($_POST['body'])){
                $title = $_POST['title'];
                $body = $_POST['body'];
                // verify inputs
                $errorList = array();
                if(strlen($title)<2 || strlen($title) >100){
                    $errorList[] = "Title must be 2-100 long";
                }
                if(strlen($body)<2 || strlen($body) >4000){
                    $errorList[] = "Body must be 2-4000 long";
                }
                // submission failed with error
                if($errorList){
                    echo '<ul class="errorMessage">';
                    foreach($errorList as $error){
                        echo "<li>$error</li>";
                    }
                    echo '</ul>';
                    displayForm($title, $body);
                }else{  
                    $userId = $_SESSION['blogUser']['id'];
                    $sql = sprintf("INSERT INTO articles VALUES (NULL, '%s', NULL, '%s', '%s')",
                                    mysqli_real_escape_string($link, $userId),
                                    mysqli_real_escape_string($link, $title),
                                    mysqli_real_escape_string($link, $body),
                                    );
    
                    if(!mysqli_query($link, $sql)){
                        die("Fatal Error: Failed to execute SQL Query: " .mysqli_error($link));
                    }
                    $articleId = mysqli_insert_id($link);
                    echo "<p>Articale added successful!</p>";
                    echo '<p><a href="article.php?id='.$articleId.'">Click here to view it</a></p>';
                }
            }else{  // first show
                displayForm();
            }
        }
    ?>
    </div>
</body>
</html>