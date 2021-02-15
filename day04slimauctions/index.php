<?php

session_start();

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

$log->pushProcessor();

if (strpos($_SERVER['HTTP_HOST'], "ipd23.com") !== false) {

    DB::$dbName = 'cp4996_yingauctions';
    DB::$user = 'cp4996_yingauctions';
    DB::$password = 'OxWZGW9PNqXa8iPC';
}else{
    DB::$dbName = 'quiz1auctions';
    DB::$user = 'quiz1auctions';
    DB::$password = 'OC4NRZAayb0hWimY';
    DB::$host = 'localhost';
    DB::$port = 3333;
}

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true
]];
$app = new \Slim\App($config);

// Fetch DI Container
$log->pushProcessor(function ($record) {
    $record['extra']['user'] = isset($_SESSION['user']) ? $_SESSION['user']['username'] : '=anonymous=';
    $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'];
    return $record;
});

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        'cache' => dirname(__FILE__) . '/tmplcache',
        'debug' => true, // This line should enable debug mode
    ]);
    // this value will be us
    $view->getEnvironment()->addGlobal('test1','VALUE');
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

//Override the default Not Found Handler before creating App
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $response = $response->withStatus(404);
        return $container['view']->render($response, '404.html.twig');
    };
};

// Flash messages handling

$container['view']->getEnvironment()->addGlobal('flashMessage', getAndClearFlashMessage());

function setFlashMessage($message) {
    $_SESSION['flashMessage'] = $message;
}

// returns empty string if no message, otherwise returns string with message and clears is
function getAndClearFlashMessage() {
    if (isset($_SESSION['flashMessage'])) {
        $message = $_SESSION['flashMessage'];
        unset($_SESSION['flashMessage']);
        return $message;
    }
    return "";
}

// Define app routes below

// STATE 1: first display of the form
$app->get('/', function ($request, $response, $args) {
    $auctionList = DB::query("SELECT * FROM auctions ORDER BY id DESC");
    return $this->view->render($response, 'index.html.twig', ['list' => $auctionList]);
});

// STATE 1: first display of the form
$app->get('/newauction', function ($request, $response, $args) {
    return $this->view->render($response, 'newauction.html.twig');
});

// STATE 2&3: receiving submission                             // for log global use
$app->post('/newauction', function ($request, $response, $args) use ($log){
    $itemDescription = $request->getParam('itemDescription');
    $sellersName = $request->getParam('sellersName');
    $sellersEmail = $request->getParam('sellersEmail');
    $lastBidPrice = $request->getParam('lastBidPrice');
    //
    $errorList = [];
    if (strlen($itemDescription) < 2 || strlen($itemDescription) > 1000) {
        $errorList[] = "Item description must be 2-1000 characters long";
    }
    if (preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $sellersName) !== 1) {
        $errorList[] = "Seller's name must be 2-100 characters long made up of letters, digits, space, comma, dot, dash";
    }
    if (filter_var($sellersEmail, FILTER_VALIDATE_EMAIL) === false) {
        $errorList[] = "Seller's email must look like an email";
    }
    if (!is_numeric($lastBidPrice) || $lastBidPrice < 0 || $lastBidPrice > 99999999.99) {
        $errorList[] = "Initial bid price must be a number between 0 and 99,999,999.99";
    }
    //
    $valuesList = ['itemDescription' => $itemDescription, 'sellersName' => $sellersName, 
                    'sellersEmail' => $sellersEmail, 'lastBidPrice' => $lastBidPrice];
    if ($errorList) { // STATE 2: errors - redisplay the form
        return $this->view->render($response, 'newauction.html.twig', ['errorList' => $errorList, 'v' => $valuesList]);
    } else { // STATE 3: success
        DB::insert('auctions', $valuesList);
        //global $log; // give access to global variable of log
        $log->debug(sprintf("New auction created with Id=%s FROM IP=%s", DB::insertId(), $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'newauction_success.html.twig');
    }
});


// STATE 1: first display of the form
$app->get('/placebid/{id:[0-9]+}', function ($request, $response, $args) {
    $auction = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%d", $args['id']);
    if ($auction) {
        return $this->view->render($response, 'placebid.html.twig', ['a' => $auction]);
    } else { // not found - cause 404 here
        throw new \Slim\Exception\NotFoundException($request, $response);
    }
}); // regex for id

// STATE 2&3: receiving submission
$app->post('/placebid/{id:[0-9]+}', function ($request, $response, $args) use($log){
    $biddersName = $request->getParam('biddersName');
    $biddersEmail = $request->getParam('biddersEmail');
    $newBidPrice = $request->getParam('newBidPrice');
    //
    $errorList = [];
    if (preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $biddersName) !== 1) {
        $errorList[] = "Bidder's name must be 2-100 characters long made up of letters, digits, space, comma, dot, dash";
    }
    if (filter_var($biddersEmail, FILTER_VALIDATE_EMAIL) === false) {
        $errorList[] = "Bidder's email must look like an email";
    }
    $auction = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%d", $args['id']);
    if (!is_numeric($newBidPrice) || $newBidPrice < 0 || $newBidPrice > 99999999.99) {
        $errorList[] = "Initial bid price must be a number between 0 and 99,999,999.99";
    } else {
        if ($auction['lastBidPrice'] >= $newBidPrice) {
            $errorList[] = "The new bid must be higher than the last bid price";
        }
    }
    //
    if ($errorList) { // STATE 2: errors - redisplay the form
        $valuesList = ['biddersName' => $biddersName, 'biddersEmail' => $biddersEmail, 'newBidPrice' => $newBidPrice];
        return $this->view->render($response, 'placebid.html.twig',
                ['errorList' => $errorList, 'a' => $auction, 'v' => $valuesList]);
    } else { // STATE 3: success
        $valuesList = ['lastBidderName' => $biddersName, 'lastBidderEmail' => $biddersEmail, 'lastBidPrice' => $newBidPrice];
        DB::update('auctions', $valuesList, "id=%i", $args['id']);
        $log->debug(sprintf("New auction created with Id=%s FROM IP=%s", $args['id'], $_SERVER['REMOTE_ADDR']));
        
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("Bid placed successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $response->withStatus(302)->withHeader('Location', '/');
    }
});




// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();
