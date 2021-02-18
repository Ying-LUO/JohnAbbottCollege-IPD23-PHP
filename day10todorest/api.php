<?php

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

DB::$dbName = 'day10todorest';
DB::$user = 'day10todorest';
DB::$password = '9PbPNQINGDT8QTK4';
DB::$host = 'localhost';
DB::$port = 3333;

DB::$error_handler = 'db_error_handler'; // runs on mysql query errors
DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

function db_error_handler($params) {
    global $log, $container;
    // log first
    $log->error("Database error: " . $params['error']);
    if (isset($params['query'])) {
        $log->error("SQL query: " . $params['query']);
    }
    // this was tricky to find - getting access to twig rendering directly, without PHP Slim
    http_response_code(500); // internal server error
    header('Content-type', 'application/json; charset=UTF-8');
    die(json_encode("500 - Internel Error")); // example: client send some fields which not existed in DB
}

// for response request
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Create and configure Slim app
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
        return $container['view']->render($response, 'error_404.html.twig');
    };
};

// apache local server configuration


// API calls bandlers are below
$app->get('/todos', function(Request $request, Response $response, array $args){
    // browser thought json response is html(from Network->Content Type)
    // so change the default format to application/json
    $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    // get array list from db
    $list = DB::query("SELECT * FROM todos");
    // translate the list into JSON
    // DEBUG: print_r($list);
    $json = json_encode($list, JSON_PRETTY_PRINT);
    // DEBUG: echo $json;
    $response->getBody()->write($json);
    return $response;
});

// need to recieve data from client but cannot get from form
// to get the data from browser, need to install a tool called "Postman"
// 1 section: url
// 2 section: request
    // body: raw data, copy from 1 json record, remove the 'id' which will be generated auto
    // after sending, we'll get a single string of what we have send
    // we what change the string to a key-value pair array
// 3 section: response
$app->post('/todos', function(Request $request, Response $response, array $args) use($log){
    $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    $json = $request->getBody();  // recieveing
    $item = json_decode($json, TRUE); // decode the string of json and return it into an associated array instead of an object
    // TODO: VALIDATE THE ITEM
    // CHECK if all required fields are recieved from client browser
    // CHECK if content of those fields are valid date/enum
    $result = validateTodo($item);
    if($result !== TRUE){
        $response = $response->withStatus(404);
        return $response;
    }
    DB::insert('todos', $item);
    $insertId = DB::insertId();  // send back the id information to the response
    $log->debug("Record todos added id = " . $insertId);
    $response = $response->withStatus(201);
    $response->getBody()->write(json_encode($insertId));  // sending // also make sure everything send out is in formate of json
    return $response;
});

// delete one record by id
$app->delete('/todos/{id:[0-9]+}', function(Request $request, Response $response, array $args) use($log){
    $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    DB::delete('todos', "%id=%i", $args['id']);
    $log->debug("Record todos deleted id= " . $args['id']);
    // code is always 200
    // return true if record actually deleted, false if it did not exist in the first place
    $count = DB::affectedRows();
    $json = json_encode($count!=0, JSON_PRETTY_PRINT);
    return $response->getBody()->write($json);
});

// fetch one record by id
$app->get('/todos/{id:[0-9]+}', function(Request $request, Response $response, array $args){
    $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    // use queryFirstRow rather than query to select only one record
    $item = DB::queryFirstRow("SELECT * FROM todos WHERE id=%i", $args['id']);
    if(!$item){
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    $json = json_encode($item, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);
    return $response;
});

$app->map(['PUT', 'PATCH'], '/todos/{id:[0-9]+}', function(Request $request, Response $response, array $args) use($log){
    $response = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    $json = $request->getBody();  // recieveing
    $item = json_decode($json, TRUE); // json decode return null if failed to validate due to syntax errors
    // TODO: VALIDATE
    // verify if the one in DB exists to update first
    $method = $request->getMethod();
    $result = validateTodo($item, $method == 'PATCH');
    $origItem = DB::queryFirstRow("SELECT * FROM todos WHERE id=%i", $args['id']);
    // if the original item aimed to updated is not existed in DB, then throw exception not found
    if(!$origItem){
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    // if exist, then use the new item to update the original item in DB
    DB::update('todos', $item, 'id=%i', $args['id']);
    $response->getBody()->write(json_encode(true));  // javascript clients do not take empty response, so just return true in json format
    return $response;
});

// post&post: all fields are required
// patch: some of fields are required
// returns TRUE if all is fine otherwise returns string describing the problem
function validateTodo($todo, $forPatch = false){
    if($todo === NULL){
        return "Invalid JSON data provided";
    }
    // only allow the fields which can be present
    $expectedFields = ['task','dueDate','isDone'];
    $todoFields = array_keys($todo); // get names of fields as an array
    if($diff = array_diff($todoFields, $expectedFields)){
        return "Invalid fields in Todos: [" . implode(',', $diff) . "]";
    }
    if(!$forPatch){  // is it PUT or POST
        // check if any fields are missing that must be there
        if($diff = array_diff($todoFields, $expectedFields)){
            return "Missing fields in Todos: [" . implode(',', $diff) . "]";
        }
    }
    // do not allow any fields to be null -- database would not accept it
    $nullableFields = [];  // put list of nullable fields here if any fields is nullable in DB
    foreach($todo as $key => $value){
        if(!in_array($key, $nullableFields)){
            if(@is_null($value)){  // @ is to suppress a warning which would be printed out
                return "$key must not be null";
            }
        }
    }
    // task 1- 100 characters long
    if(isset($todo['task'])){
        $task = $todo['task'];
        if(strlen($task)<1 || strlen($task)>100){
            return "Task description must be 1-100";
        }
    }

}

// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();

