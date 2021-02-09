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
    <title>Article View</title>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>tinymce.init({selector:'textarea[name=body]'});</script>
</head>
<body>
    <div id="centeredContent">
    <p><a href="index.php">View all articles</a></p>
    <?php 
        
        if(!isset($_GET['id'])){
            die("Error: Missing article Id in the URL");
        }
        // id not exist
        $id = $_GET['id'];
        // security printf
        // must verify anything before save into database
        $sql = sprintf("SELECT A.id, A.authorId, A.createdTS, A.title, A.body, U.username 
                        FROM articles as A, users AS U WHERE A.id ='%s' AND A.authorId = U.id", 
        mysqli_real_escape_string($link, $id));
        $result = mysqli_query($link, $sql);
        if(!$result){
            die("SQL Query Failed: ".mysqli_error($link));
        }
        $article = mysqli_fetch_assoc($result);
        if($article){
            echo '<div class="articleBox">';
            echo '<h2>'.htmlentities($article['title']).'</h2>';
            $postedDate = date('M d, Y \a\t H:i:s', strtotime($article['createdTS']));
            echo '<i>Posted by '.$article['username'].' on: '.$postedDate."</i>\n";
            echo '<div class="articleBody">'.$article['body']."</div>\n";
            echo '</div>';
            echo "<script>document.title='".htmlentities($article['title'])."';</script>\n";
        }else{
            echo "<h2>Article not found</h2>";
        }
    
    ?>
    </div>
</body>
</html>