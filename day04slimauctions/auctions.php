<?php

require_once 'vendor/autoload.php';

require_once 'init.php';

// STATE 1: first display of the form
$app->get('/', function ($request, $response, $args) {
    $auctionList = DB::query("SELECT * FROM auctions ORDER BY id DESC");
    return $this->view->render($response, 'index.html.twig', ['list' => $auctionList]);
});

// STATE 1: first display of the form
$app->get('/newauction', function ($request, $response, $args) {
    return $this->view->render($response, 'newauction.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/newauction', function ($request, $response, $args) use ($log) {
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
        $log->debug(sprintf("New auction created with Id=%s", DB::insertId()));
        return $this->view->render($response, 'newauction_success.html.twig');
    }
});

// FIXME: handle the case when newBidPrice is an empty string, now it causes 404
// FIXME: handle the case when newBidPrice is not numerical at all, now it causes 404
$app->get('/isbidtoolow/{auctionId:[0-9]+}/{newBidPrice:[0-9\.]+}', function ($request, $response, $args) {
    $oldBidPrice = DB::queryFirstField("SELECT lastBidPrice from auctions WHERE id=%d", $args['auctionId']);
    if ($oldBidPrice == null) {
        echo "Auction not found";
        return;
    }
    $newBidPrice = $args['newBidPrice'];
    if (!is_numeric($newBidPrice) || $newBidPrice < 0 || $newBidPrice > 99999999.99) {
        echo "Bid price must be a number between 0 and 99,999,999.99";
        return;
    }
    if ($newBidPrice <= $oldBidPrice) {
        echo "New bid must be greater than the current bid";
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
$app->post('/placebid/{id:[0-9]+}', function ($request, $response, $args) use ($log) {
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
        $log->debug(sprintf("Auction with Id=%s updated", $args['id']));
        // FLASH MESSAGE INSTEAD of success page
        setFlashMessage("Bid placed successfully");
        // return $this->view->render($response, 'placebid_success.html.twig');
        return $response->withStatus(302)->withHeader('Location', '/');
    }
});

