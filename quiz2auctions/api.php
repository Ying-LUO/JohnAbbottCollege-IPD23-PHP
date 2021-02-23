<?php

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

if (strpos($_SERVER['HTTP_HOST'], "ipd23.com") !== false) {
    //hosting on ipd23.com database connection setup
    DB::$dbName = 'cp4996_yingauctions';
    DB::$user = 'cp4996_yingauctions';
    DB::$password = 'OxWZGW9PNqXa8iPC';
    }else{
        DB::$dbName = 'quiz2auctions';
        DB::$user = 'quiz2auctions';
        DB::$password = 'gMfnFpMybRGsnccs';
        DB::$host = 'localhost';
        DB::$port = 3333;
    }


DB::$error_handler = 'db_error_handler'; // runs on mysql query errors
DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

function db_error_handler($params) {
    global $log, $container;
    // log first
    $log->error("Database error: " . $params['error']);
    if (isset($params['query'])) {
        $log->error("SQL query: " . $params['query']);
    }
    http_response_code(500); // internal server error
    header('Content-type: application/json; charset=UTF-8');
    die(json_encode("500 - Internal error"));
}

// Create and configure Slim app
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true
]];
$app = new \Slim\App($config);
$container = $app->getContainer();

//Override the default Not Found Handler before creating App
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    };
};

// set content-type globally using middleware (untested)
$app->add(function($request, $response, $next) {
    //sleep(1); // artificially delay all responses by 1 second
    $response = $next($request, $response);
    return $response->withHeader('Content-Type', 'application/json');
});

// API calls handlers are below
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("quiz2auctions with RESTful API");
    return $response;
});

// GET /auctions
$app->get('/auctions', function (Request $request, Response $response, array $args) {
    $list = DB::query("SELECT * FROM auctions");
    $json = json_encode($list, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);
    return $response;
});

// fetch one record
$app->get('/auctions/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($log) {

    $item = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%i", $args['id']);
    if (!$item) {
        $log->error("Auctions id=" . $args['id'] . " not found in database");
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    $json = json_encode($item, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);
    return $response;
});

$app->post('/auctions', function (Request $request, Response $response, array $args) use ($log) {

    $json = $request->getBody();
    $item = json_decode($json, TRUE); // true makes it return an associative array instead of an object
    // validate
    if ( ($result = validateAuction($item)) !== TRUE) {
        $log->error("New Auctions validation failed");
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode("400 - " . $result));
        return $response;
    }
    DB::insert('auctions', $item);
    $insertId = DB::insertId();
    $log->debug("New auctions added id=" . $insertId);
    $response = $response->withStatus(201);
    $response->getBody()->write(json_encode($insertId));
    return $response;
});

$app->map(['PUT', 'PATCH'], '/auctions/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($log) {
    
    $json = $request->getBody();
    $item = json_decode($json, TRUE); // true makes it return an associative array instead of an object
    $origItem = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%i", $args['id']);
    if (!$origItem) { // record not found
        $log->error("Auctions id=" . $args['id'] . " not found in database");
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    // Auction: validate
    $method = $request->getMethod();
    if ( ($result = validateNewBid($item, $origItem['lastBid'], $method == 'PATCH')) !== TRUE) {
        $log->error("Auctions id=" . $args['id'] . " validation failed");
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode("400 - " . $result));
        return $response;
    }
    DB::update('auctions', $item, "id=%i", $args['id']);
    $log->debug("Auctions id=" . $args['id'] . " has new bid=" . $item['lastBid'] . " by " . $item['lastBidderEmail']);
    $response->getBody()->write(json_encode(true)); // JavaScript clients (web browsers) do not like empty responses
    return $response;
});

function validateNewBid($newBid, $existingBid, $forPatch = false){
    if ($newBid === NULL) { // probably json_decode failed due to JSON syntax errors
        return "Invalid JSON data provided";
    }
    // - only allow the fields that must/can be present
    $expectedFields = ['lastBid', 'lastBidderEmail'];
    $newBidFields = array_keys($newBid); 
    if ($diff = array_diff($newBidFields, $expectedFields)) {
        return "Invalid fields in new Bid: [". implode(',', $diff). "]";
    }
    //
    if (!$forPatch) { // is it PUT or POST
        // - check if any fields are missing that must be there
        if ($diff = array_diff($expectedFields, $newBidFields)) {
            return "Missing fields in Auction: [". implode(',', $diff). "]";
        }
    }
    // do not allow any fields to be null - database would not accept it
    $nullableFields = []; // put list of nullable fields here
    foreach($newBid as $key => $value) {
        if (!in_array($key, $nullableFields)) {
            if (@is_null($value)) { // @ is to suppress a warning (which would be printed out)
                return "$key must not be null";
            }
        }
    }
    if (isset($newBid['lastBid'])) {
        $lastBid = $newBid['lastBid'];
        if ($lastBid < $existingBid) {
            return "Last bid must be higher than existing bid";
        }
    }
    if (isset($newBid['lastBidderEmail'])) {
        $lastBidderEmail = $newBid['lastBidderEmail'];
        if (strlen($lastBidderEmail) < 1 || strlen($lastBidderEmail) > 320) {
            return "Last Bidder Email must be 1-320 characters long";
        }
        if(filter_var($lastBidderEmail, FILTER_VALIDATE_EMAIL) == false){
            return "Last Bidder Emai is invalid";
        }
    }
    
    // if we passed all tests return TRUE
    return TRUE;

}

// returns TRUE if all is fine otherwise returns string describing the problem
function validateAuction($auction, $forPatch = false) {
    if ($auction === NULL) { // probably json_decode failed due to JSON syntax errors
        return "Invalid JSON data provided";
    }
    // - only allow the fields that must/can be present
    $expectedFields = ['itemDesc', 'sellerEmail'];
    $auctionFields = array_keys($auction); 
    if ($diff = array_diff($auctionFields, $expectedFields)) {
        return "Invalid fields in Auction: [". implode(',', $diff). "]";
    }
    //
    if (!$forPatch) { // is it PUT or POST
        // - check if any fields are missing that must be there
        if ($diff = array_diff($expectedFields, $auctionFields)) {
            return "Missing fields in Auction: [". implode(',', $diff). "]";
        }
    }
    // do not allow any fields to be null - database would not accept it
    $nullableFields = []; // put list of nullable fields here
    foreach($auction as $key => $value) {
        if (!in_array($key, $nullableFields)) {
            if (@is_null($value)) { // @ is to suppress a warning (which would be printed out)
                return "$key must not be null";
            }
        }
    }
    
    if (isset($auction['itemDesc'])) {
        $itemDesc = $auction['itemDesc'];
        if (strlen($itemDesc) < 1 || strlen($itemDesc) > 200) {
            return "Auction description must be 1-200 characters long";
        }
    }

    if (isset($auction['sellerEmail'])) {
        $sellerEmail = $auction['sellerEmail'];
        if (strlen($sellerEmail) < 1 || strlen($sellerEmail) > 320) {
            return "Auction sellerEmail must be 1-320 characters long";
        }
        if(filter_var($sellerEmail, FILTER_VALIDATE_EMAIL) == false){
            return "Auction sellerEmail is invalid";
        }
    }
    
    // if we passed all tests return TRUE
    return TRUE;
}

// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();

