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
                echo "<p>You are logged in as ".$_SESSION['blogUser']['username'].".";
                echo "You can <a href=\"logout.php\">Logout</a> or <a href=\"articleadd.php\">Post an article</a></p>\n";
            }else{
                echo "<a href=\"login.php\">Login</a> or <a href=\"register.php\">Register</a>";
            }
        ?>
        <div id="mainContent">
            <h1>Welcome to my blog</h1>
            <?php 
                $sql = "SELECT A.id, A.authorId, A.createdTS, A.title, A.body, U.username 
                        FROM articles as A, users AS U WHERE A.authorId = U.id ORDER BY A.id DESC";
                $result = mysqli_query($link, $sql);
                if(!$result){
                    die("SQL Query Failed: ".mysqli_error($link));
                }
                while($article = mysqli_fetch_assoc($result)){
                    echo '<div class="articlePreviewBox">';
                    echo '<h2><a href="article.php?id='. $article['id'] . '">'. htmlentities($article['title']) ."</a></h2>\n";
                    $postedDate = date('M d, Y \a\t H:i:s', strtotime($article['createdTS']));
                    echo "<i>Posted by ". htmlentities($article['username']) . " on " . $postedDate . "</i>\n";
                    $fullBodyNoTags = strip_tags($article['body']);
                    $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 150); // FIXME
                    $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
                    echo "<p>$bodyPreview</p>\n";
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</body>
</html>