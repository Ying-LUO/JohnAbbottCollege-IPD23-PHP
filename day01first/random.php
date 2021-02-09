<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form>
        Min: <input name="min" type="text"><br>
        Max: <input name="max" type="text"><br>
        <input type="submit" value="Generate 10 Random numbers">
    </form>
    <?php 
        // function for checking value if it's integer
        function isInt($value){
            return (is_numeric($value) && (int)$value == $value);  
            // checking the value is integer AND cast value to integer
            // double restriction
        }

        $errorList = array();
        // you could check if the count of errorlist >0 
        // but normally php is loosely type language not like java strict language
        // empty string or string of "0" or any 0 int/float numbers is considered as false
        // empty array with zero element is considered as false
        if($errorList){  // there were errors - display them
            echo '<ul>';
            foreach($errorList as $error){
                echo "<li>$error</li>";
            }
            echo '</ul>';
        }else{
            if(!isset($_GET['min']) || !isset($_GET['max'])){
                array_push($errorList, "Please enter values for submission");
                //or
                $errorList[] = "Please enter values for submission";
            }else if(filter_var($min, FILTER_VALIDATE_INT) === FALSE){
                // another way to validate
            }else if(!isInt($_GET['min']) || !isInt($_GET['max'])){
                array_push($errorList, "Min/Max must be integer values");
                // or
                $errorList[] = "Min/Max must be integer values";
                // is_int() method is only checking the variable type but not checking the content
                // but currently all input from ui is all string, you have to cast it to other type
                // that's why if you check is_int() will always return false
                // use user-added function isInt() above to check if integers
            }else if(($_GET['min']) > ($_GET['max'])){
                echo "ERROR: min must be smaller than max";
            }else{ // submission recieved
                for ($x = 0; $x < 10; $x++) {
                    $random = rand($_GET['min'], $_GET['max']);
                    echo "$random ,";
                }
            }
        }
    ?>
</body>
</html>