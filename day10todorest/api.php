<?php

require_once 'vendor/autoload.php';

// would this be better to use for server-side validation?
// use Respect\Validation\Validator as Validator;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

DB::$dbName = 'day10todorest';
DB::$user = 'day10todorest';
DB::$password = 'd3llopla0GOkWeDt';
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
    sleep(1); // artificially delay all responses by 1 second
    $response = $next($request, $response);
    return $response->withHeader('Content-Type', 'application/json');
});

// API calls handlers are below
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Todo app with RESTful API");
    return $response;
});

// GET /todos
// GET /todos?sortBy=dueDate
// Alternative $app->get('/todos(/{sortBy:(id|task|dueDate|isDone)})', function (Request $request, Response $response, array $args) {
$app->get('/todos', function (Request $request, Response $response, array $args) {
    $userId = getAuthUserId($request);
    if (!$userId) {
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode("403 - authentication failed"));
        return $response;
    }
    $queryParams = $request->getQueryParams();
    $sortBy = isset($queryParams['sortBy']) ? $queryParams['sortBy'] : "id";
    if (!in_array($sortBy, ['id', 'task', 'dueDate', 'isDone'])) {
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode("400 - invalid sortBy value"));
        return $response;
    }
    $list = DB::query("SELECT * FROM todos WHERE ownerId=%i ORDER BY %l", $userId, $sortBy);
    $json = json_encode($list, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);
    return $response;
});

// fetch one record
$app->get('/todos/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
    $userId = getAuthUserId($request);
    if (!$userId) {
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode("403 - authentication failed"));
        return $response;
    }
    $item = DB::queryFirstRow("SELECT * FROM todos WHERE id=%i AND ownerId=%i", $args['id'], $userId);
    if (!$item) {
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    $json = json_encode($item, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);
    return $response;
});

$app->post('/todos', function (Request $request, Response $response, array $args) use ($log) {
    $userId = getAuthUserId($request);
    if (!$userId) {
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode("403 - authentication failed"));
        return $response;
    }
    $json = $request->getBody();
    $item = json_decode($json, TRUE); // true makes it return an associative array instead of an object
    // validate
    if ( ($result = validateTodo($item)) !== TRUE) {
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode("400 - " . $result));
        return $response;
    }
    $item['ownerId'] = $userId;
    DB::insert('todos', $item);
    $insertId = DB::insertId();
    $log->debug("Record todos added id=" . $insertId);
    $response = $response->withStatus(201);
    $response->getBody()->write(json_encode($insertId));
    return $response;
});

$app->map(['PUT', 'PATCH'], '/todos/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($log) {
    $userId = getAuthUserId($request);
    if (!$userId) {
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode("403 - authentication failed"));
        return $response;
    }
    $json = $request->getBody();
    $item = json_decode($json, TRUE); // true makes it return an associative array instead of an object
    // TODO: validate
    $method = $request->getMethod();
    if ( ($result = validateTodo($item, $method == 'PATCH')) !== TRUE) {
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode("400 - " . $result));
        return $response;
    }
    $origItem = DB::queryFirstRow("SELECT * FROM todos WHERE id=%i AND ownerId=%i", $args['id'], $userId);
    if (!$origItem) { // record not found
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode("404 - not found"));
        return $response;
    }
    DB::update('todos', $item, "id=%i", $args['id']);
    $response->getBody()->write(json_encode(true)); // JavaScript clients (web browsers) do not like empty responses
    return $response;
});

// delete one record
$app->delete('/todos/{id:[0-9]+}',  function (Request $request, Response $response, array $args) use ($log) {
    $userId = getAuthUserId($request);
    if (!$userId) {
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode("403 - authentication failed"));
        return $response;
    }
    DB::delete('todos', "id=%i AND ownerId=%i", $args['id'], $userId);
    $log->debug("Record todos deleted id=" . $args['id']);
    // code is always 200
    // return true if record actually deleted, false if it did not exist in the first place
    $count = DB::affectedRows();
    $json = json_encode($count != 0, JSON_PRETTY_PRINT); // true or false
    return $response->getBody()->write($json);
});

// returns TRUE if all is fine otherwise returns string describing the problem
function validateTodo($todo, $forPatch = false) {
    if ($todo === NULL) { // probably json_decode failed due to JSON syntax errors
        return "Invalid JSON data provided";
    }
    // - only allow the fields that must/can be present
    $expectedFields = ['task', 'dueDate', 'isDone'];
    $todoFields = array_keys($todo); // get names of fields as an array
    // check if there are any fields that should not be there
    if ($diff = array_diff($todoFields, $expectedFields)) {
        return "Invalid fields in Todo: [". implode(',', $diff). "]";
    }
    //
    if (!$forPatch) { // is it PUT or POST
        // - check if any fields are missing that must be there
        if ($diff = array_diff($expectedFields, $todoFields)) {
            return "Missing fields in Todo: [". implode(',', $diff). "]";
        }
    }
    // do not allow any fields to be null - database would not accept it
    $nullableFields = []; // put list of nullable fields here
    foreach($todo as $key => $value) {
        if (!in_array($key, $nullableFields)) {
            if (@is_null($value)) { // @ is to suppress a warning (which would be printed out)
                return "$key must not be null";
            }
        }
    }
    // - task 1-100 characters long
    if (isset($todo['task'])) {
        $task = $todo['task'];
        if (strlen($task) < 1 || strlen($task) > 100) {
            return "Task description must be 1-100 characters long";
        }
    }
    // - dueDate a valid date from 1900 to 2099 years
    if (isset($todo['dueDate'])) {
        if (!date_create_from_format('Y-m-d', $todo['dueDate'])) {
            return "DueDate has invalid format";
        }
        // valid dates are from 1900-01-01 to almost 2100-01-01
        $dueDate = strtotime($todo['dueDate']); // integer Unix time value
        if ($dueDate < strtotime('1900-01-01') || $dueDate >= strtotime('2100-01-01')) {
            return "DueDate must be within 1900 to 2099 years";
        }
    }
    // - isDone must be pending or done
    if (isset($todo['isDone'])) {
        if (!in_array($todo['isDone'], ['pending','done'])) {
            return "IsDone invalid: must be either pending or done";
        }
    }
    // if we passed all tests return TRUE
    return TRUE;
}

// returns FALSE if authentication failed or missing
// otherwise returns users.id value
function getAuthUserId($request) {
    global $log;
    if (!$request->hasHeader('X-auth-username') || !$request->hasHeader('X-auth-password')) {
        $log->debug("Authentication missing from IP=" . $_SERVER['REMOTE_ADDR']);
        return FALSE; // authentication missing
    }
    $username = $request->getHeader('X-auth-username')[0];
    $password = $request->getHeader('X-auth-password')[0];
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $username);
    $userId = FALSE;
    if ($user) {
        if ($user['password'] == $password) {
            $userId = $user['id'];
        }
    }
    if (!$userId) {
        $log->debug("Authentication failed for email=". $username . " from IP=" . $_SERVER['REMOTE_ADDR']);
    }
    return $userId;
}


// Run app - must be the last operation
// if you forget it all you'll see is a blank page
$app->run();

