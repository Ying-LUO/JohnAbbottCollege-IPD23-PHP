<?php
    // like java library, load lib files from vendor 
    // depending on which lib you're using/needed for your program

use Slim\Http\Response;

require_once 'vendor/autoload.php';  

    DB::$dbName = 'quiz1auctions';
    DB::$user = 'quiz1auctions';
    DB::$password = 'OC4NRZAayb0hWimY';
    DB::$host = 'localhost';
    DB::$port = 3333;
    
    // Create and configure Slim app
    $config = ['settings' => [
        'addContentLengthHeader' => false,
        'displayErrorDetails' => true   // for debug
    ]];
    $app = new \Slim\App($config);   // instantiate 

    // FECTCH DI container
    $container = $app->getContainer();

    // Register twig view helper
    $container['view'] = function($c){
        $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
            'cache' => dirname(__FILE__) . '/cache',
            'debug' => true,  // this line should enable debug mode
        ]);
        //
        $view->getEnvironment()->addGlobal('test1','VALUE');
        // Instantiate and add Slim specific extension
        $router = $c->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new Slim\Views\TwigExtension($router, $uri));
        return $view;
    };

    // STATE 1: first display of then form
    $app->get('/newauction', function($request, $response, $args){ // agrs only contains the arguments from URL
        $auctionList = DB::query("SELECT * FROM auctions ORDER BY id DESC id");
        return $this->view->render($response, 'index.html.twig', ['list' => $auctionList]);
    });

    // STATE 2&3: recieving submission
    $app->post('/addperson', function($request, $response, $args){
        $itemDescription = $request->getParam('itemDescription');
        $sellersName = $request->getParam('sellersName');
        $sellerEmail = $request->getParam('sellerEmail');
        $lastBidPrice = $request->getParam('lastBidPrice');

        $errorList = [];

        if(strlen($itemDescription)<2 || strlen($itemDescription)>1000){
            $errorList[] = "Description must be 2-1000 characters long";
        }
        if(preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $sellersName) !== 1){
            $errorList[] = "Name must be 2-100 characters long";
            $sellersName = "";
        }

        if(filter_var($sellerEmail, FILTER_VALIDATE_EMAIL) === FALSE){
            $errorList[] = "Email is invalid";
            $sellerEmail = "";
        }

        if($errorList){  // STATE 2 - ERRORS: REDISPLAY THE FORM WITHout error msg
            // pass errorlist to the template
            $valuesList = ['sellersName'=>$sellersName, 'itemDescription'=>$itemDescription, 'sellerEmail' => $sellerEmail];
            return $this->view->render($response, 
                                        'addperson.html.twig', 
                                        [
                                            'errorList' => $errorList,
                                            'v' => $valuesList
                                        ]);
        }else{
            DB::insert('people', ['name' => $name, 'age' => $age]);
            return $this->view->render($response, 'addperson_success.html.twig');
        }
    });

    // Override the default not found handler before createing app
    $container['notFoundHandler'] = function($container){
        $response = $response->withStatus(404);
        return $container['view']->render($response, '404.html.twig');
    }

    // STATE 1: first display of then form
    $app->get('/placebid/{id:[0-9]+}', function($request, $response, $args){ // agrs only contains the arguments from URL
        $auction = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%d", $args['id']);
        if($auction){
            return $this->view->render($response, 'placebid.html.twig');
        }else{
           throw new \Slim\Views\NotFoundException($request, $response);
        }
    });

    // STATE 2&3: recieving submission
    $app->post('/addperson', function($request, $response, $args){
        $biddersName = $request->getParam('biddersName');
        $biddersEmail = $request->getParam('biddersEmail');
        $newBidPrice = $request->getParam('newBidPrice');

        $errorList = [];

        if(preg_match('/^[a-zA-Z0-9 ,\.-]{2,100}$/', $biddersName) !== 1){
            $errorList[] = "Name must be 2-100 characters long";
            $biddersName = "";
        }

        if(filter_var($biddersEmail, FILTER_VALIDATE_EMAIL) === FALSE){
            $errorList[] = "Email is invalid";
            $biddersEmail = "";
        }
        $auction = DB::queryFirstRow("SELECT * FROM auctions WHERE id=%d", $args['id']);
        if(!is_numeric($newBidPrice) || $newBidPrice < 0 || $newBidPrice > 99999999.99){
            $errorList[] = "Initial bid price";
        }

    // Run app - must be the last operation
    // if you forget it all you'll see is a blank page
    $app->run();


// no need to close it