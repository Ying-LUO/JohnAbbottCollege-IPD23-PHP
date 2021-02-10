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
</head>
<body>
    <?php 
        
        function displayForm($sellersName = "", $sellersEmail = "", $lastBidPrice = ""){
            $form = <<< ENDMARKER
            <form method="POST">
                Sellers Name: <input name="sellersName" type="text" value="$sellersName" class="collection-item"><br>
                Sellers Email: <input name="sellersEmail" type="email" value="$sellersEmail" class="collection-item"><br>
                New Bid Price: <input name="lastBidPrice" type="number" value="$lastBidPrice" placeholder="0.00" class="collection-item" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$"><br>
                <input type="submit" name="submit" value="New Auction" class="btn">
            </form>
            ENDMARKER;
            echo $form;
        }

        if (isset($_POST['submit'])){

            if(isset($_POST['sellersName']) || isset($_POST['sellersEmail']) || 
                isset($_POST['lastBidPrice'])){
                    $sellersName = $_POST['sellersName'];
                    $sellersEmail = $_POST['sellersEmail'];
                    $lastBidPrice = $_POST['lastBidPrice'];
            
                // verify inputs
                $errorList = array();
    
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
            }
            // submission failed with error
            if($errorList){
                echo '<ul class="errorMessage">';
                foreach($errorList as $error){
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                displayForm($sellersName, $sellersEmail, $lastBidPrice);
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