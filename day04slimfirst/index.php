<?php
    // like java library, load lib files from vendor 
    // depending on which lib you're using/needed for your program
    require_once 'vendor/autoload.php';  

    DB::$dbName = 'day02people';
    DB::$user = 'day02people';
    DB::$password = '2VxyQMHdCfiPFHu2';
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

    // Define app routes
    // event handler
    // triggered by a request by a specific url
    // name is part of the url
    // $args is from part of the name of the url
    $app->get('/hello/{name}', function ($request, $response, $args) {
        return $response->write("Hello " . $args['name']);
    });

    $app->get('/hello/{name}/{age}', function ($request, $response, $args) {
        // display on webpage
        //return $response->write("<p>Hello " . $args['name']." you are ".$args['age']." y/o</p>");
        $name = $args['name'];
        $age = $args['age'];
        // save into database
        DB::insert('people',[
            'name' => $name,
            'age' => $age
        ]);
        return $this->view->render($response, 'hello.html.twig', ['nameV' => $name, 'ageV' => $age ]);
    });

    // STATE 1: first display of then form
    $app->get('/addperson', function($request, $response, $args){ // agrs only contains the arguments from URL
        return $this->view->render($response, 'addperson.html.twig');
    });

    // STATE 2&3: recieving submission
    $app->post('/addperson', function($request, $response, $args){
        $name = $request->getParam('name');
        $age = $request->getParam('age');

        $errorList = [];
        if(strlen($name)<2 || strlen($name)>50){
            $errorList[] = "Name must be 2-50 characters long";
            $name = "";
        }
        if(filter_var($age, FILTER_VALIDATE_INT) === FALSE || $age <0 || $age>150){
            $errorList[] = "Age must be a number between 0-150";
            $age = "";
        }
        if($errorList){  // STATE 2 - ERRORS: REDISPLAY THE FORM WITHout error msg
            // pass errorlist to the template
            $valuesList = ['name'=>$name, 'age'=>$age];
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

    // Run app - must be the last operation
    // if you forget it all you'll see is a blank page
    $app->run();


// no need to close it