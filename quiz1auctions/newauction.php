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
    <title>New Auction</title>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>tinymce.init({selector:'textarea[name=description]'});</script>
</head>
<body>
    <?php 
        function displayForm($sellersName = "", $sellersEmail = "", $lastBidPrice = "", $description = ""){
            $sellersName = htmlentities($sellersName);
            $sellersEmail = htmlentities($sellersEmail);
            $lastBidPrice = htmlentities($lastBidPrice);
            $form = <<< ENDMARKER
            <form method="POST">
                Sellers Name: <input name="sellersName" type="text" value="$sellersName" class="collection-item"><br>
                Sellers Email: <input name="sellersEmail" type="email" value="$sellersEmail" class="collection-item"><br>
                Initial Bid Price: <input name="lastBidPrice" type="number" value="$lastBidPrice" placeholder="0.00" class="collection-item" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$"><br>
                Item Description: <textarea name="description" cols="50" rows="20" class="collection-item">$description</textarea><br>
                Item Image (required): <input type="file" name="photo" /><br><br>
                <input type="submit" name="submit" value="New Auction" class="btn">
            </form>
            ENDMARKER;
            echo $form;
        }

        function verifyUploadedPhoto(&$photoFilePath) {
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] != 4) { // file uploaded
                // print_r($_FILES);
                $photo = $_FILES['photo'];
                if ($photo['error'] != 0) {
                    return "Error uploading photo " . $photo['error'];
                } 
                if ($photo['size'] > 1024*1024) { // 1MB
                    return "File too big. 1MB max is allowed.";
                }
                $info = getimagesize($photo['tmp_name']);
                if (!$info) {
                    return "File is not an image";
                }
                // echo "\n\nimage info\n";
                // print_r($info);
                if ($info[0] < 200 || $info[0] > 1000 || $info[1] < 200 || $info[1] > 1000) {
                    return "Width and height must be within 200-1000 pixels range";
                }
                $ext = "";
                switch ($info['mime']) {
                    case 'image/jpeg': $ext = "jpg"; break;
                    case 'image/gif': $ext = "gif"; break;
                    case 'image/png': $ext = "png"; break;
                    default:
                        return "Only JPG, GIF and PNG file types are allowed";
                    }
                $photoFilePath = "uploads/" .  "test" . "." . $ext;
            }
            return TRUE;
        }

        if (isset($_POST['submit'])){

            if(isset($_POST['sellersName']) || isset($_POST['sellersEmail']) || 
                isset($_POST['lastBidPrice']) || isset($_POST['description'])){
                    $sellersName = $_POST['sellersName'];
                    $sellersEmail = $_POST['sellersEmail'];
                    $lastBidPrice = $_POST['lastBidPrice'];
                    $description = $_POST['description'];
            
                // verify inputs
                $errorList = array();
    
                // sanitize
                $description = strip_tags($description, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
                if(strlen($description)<2 || strlen($description) >1000){
                    $errorList[] = "Description must be 2-1000 characters long";
                }
    
                if(preg_match('/^[a-zA-Z0-9,.-]{2,100}$/', $sellersName) != 1){
                    $errorList[] = "Sellers Name must be 2-100 characters long made up of only letters (upper/lower-case), space, dash, dot, comma and numbers";
                    $sellersName = "";
                }
    
                if(filter_var($sellersEmail, FILTER_VALIDATE_EMAIL) === FALSE){
                    array_push($errorList, "Sellers Email is invalid");
                    $sellersEmail = "";
                }
    
                // TODO: CHECH REGEX OF DECIMAL
                if($lastBidPrice < 0){
                    array_push($errorList, "Initial Bid Price must be equal or above zero");
                    $lastBidPrice = "";
                }

                $retval = verifyUploadedPhoto($photoFilePath);
                if ($retval !== TRUE) {
                    $errorList[] = $retval; 
                }
            }
            // submission failed with error
            if($errorList){
                echo '<ul class="errorMessage">';
                foreach($errorList as $error){
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                displayForm($sellersName, $sellersEmail, $lastBidPrice, $description);
            }else{  
                if ($photoFilePath != null) {
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoFilePath) != true) {
                        die("Error moving the uploaded file. Action aborted.");
                    }
                }
                $sql = sprintf("INSERT INTO auctions VALUES (NULL, '%s', '%s', '%s', '%s', '%s', NULL, NULL)",
                                mysqli_real_escape_string($link, $description),
                                mysqli_real_escape_string($link, $photoFilePath),
                                mysqli_real_escape_string($link, $sellersName),
                                mysqli_real_escape_string($link, $sellersEmail),
                                mysqli_real_escape_string($link, $lastBidPrice)
                                );

                if(!mysqli_query($link, $sql)){
                    echo "Fatal Error: Failed to execute SQL Query: " .mysqli_error($link);
                }
                $auctionId = mysqli_insert_id($link);
                echo "<p>New Auction added successfully!</p>";
                echo '<p><a href="isbidtoolow.php?id='.$auctionId.'&bid='.$lastBidPrice.'">Click here to view it</a></p>';
            }
        }else{  // first show
            displayForm();
        }
    ?>
</body>
</html>