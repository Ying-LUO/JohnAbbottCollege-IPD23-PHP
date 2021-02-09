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
    <?php 
        if(!isset($_GET['id'])){
            die("Error: Missing article Id in the URL");
        }
        // id not exist
        $id = $_GET['id'];
        // security printf
        $sql = sprintf("SELECT * FROM articles WHERE id='%s'", $id);
    ?>
    </div>
</body>
</html>