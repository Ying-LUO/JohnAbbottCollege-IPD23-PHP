<?php

session_start();

require_once 'vendor/autoload.php';

require_once 'init.php';

// Define app routes below

require_once 'admin.php';

require_once 'auctions.php';

// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();
