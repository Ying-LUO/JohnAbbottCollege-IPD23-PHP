<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register with passport and photo</title>
</head>
<body>
    <?php 
          require_once 'db.php';
          function printForm($passportNo = "") {
            $passportNo = htmlentities($passportNo); // avoid invalid html in case <>" are part of name
            $form = <<< END
            <form method="post" enctype="multipart/form-data">
                Passport No.: <input type="text" name="passportNo" value="$passportNo"><br>
                Photo (optional): <input type="file" name="photo" /><br>
                <input type="submit" name="submit" value="Add passport">
            </form>
        END;
            echo $form;
        }

        // returns TRUE on success
// returns a string with error message on failure
function verifyUploadedPhoto(&$photoFilePath, $passportNo) {
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
        $photoFilePath = "uploads/" .  $passportNo . "." . $ext;
    }
	return TRUE;
}

if (isset($_POST['submit'])) { // are we receiving a submission?
    $passportNo = $_POST['passportNo'];
    $errorList = array();
    if (preg_match('/^[A-Z]{2}[0-9]{6}$/', $passportNo) != 1) {
        $errorList[] = "Passport number must be in AB123456 format";
    }
    // TODO: verify the picture upload is acceptable
    $photoFilePath = null;  // in SQL INSERT query this must become NULL and *not* 'NULL'
    $retval = verifyUploadedPhoto($photoFilePath, $passportNo);
    if ($retval !== TRUE) {
        $errorList[] = $retval; // string with error was returned - add it to list of errors
    }
    // it's okay if no photo was selected - we will just insert NULL value
    //
    if ($errorList) { // STATE 2: errors in submission - failed
        echo "<p>There were problems with your submission:</p>\n<ul>\n";
        foreach ($errorList as $error) {
            echo "<li class=\"errorMessage\">$error</li>\n";
        }
        echo "</ul>\n";
        printForm($passportNo);
    } else { // STATE 3: successful submission
        if ($photoFilePath != null) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoFilePath) != true) {
                die("Error moving the uploaded file. Action aborted.");
            }
        }
        $sql = sprintf("INSERT INTO passports VALUES (NULL, '%s', %s)",
            mysqli_real_escape_string($link, $passportNo),
            ($photoFilePath == null) ? "NULL" : "'" . mysqli_real_escape_string($link, $photoFilePath) . "'");
        $result = mysqli_query($link, $sql);
        if (!$result) {
            die("SQL Query failed: " . mysqli_error($link));
        }
        
        echo "<p>Passport successfully added</p>";
    }
} else { // STATE 1: first display
    printForm();
}

    ?>
</body>
</html>