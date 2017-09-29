<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/register', function () use ($auth, $authFlow){
   $this->get('', function (Request $request, Response $response) {
        return $this->view->render($response, 'register.html');
    })->add($authFlow)->add($auth);

   $this->post('', function (Request $request, $response) {
        $body = $request->getParsedBody();
        $userV = new UserRegistrationValidation($body, $this->db);
        $message = new ErrorCodes($userV->registerUser(), getUserLang($this->session, $request));
        return $this->view->render($response, 'register.html', array('flash' => $message->getErrorMessage()));
    });

});

$app->group('/dashboard', function () use ($auth, $authFlow){
    $this->get('', function ( Request $request,  Response $response) {
        $fcm = (new FlashcardMapper($this->db))->getAllFlashcardsById($this->session->get('LOGINSERIAL'));
        if(is_array($fcm)){
            return $this->view->render($response, 'dashboard.html', array('flashcards' => $fcm));
        }else{
            $error = ErrorCodes::getErrorMessage($fcm, getUserLang($this->session, $request));
            return $this->view->render($response, 'dashboard.html', array('flash' => $error));
        }
        
    })->add($authFlow)->add($auth);

    $this->post('/new', function ( Request $request, Response $response) {
        $body = $request->getParsedBody();
        $word = filter_var($body['word'], FILTER_SANITIZE_STRING);
        $descr = filter_var($body['descr'], FILTER_SANITIZE_STRING);
        $private = (isset($body['private']) ? 1:0);
        $query = $this->db->prepare("insert into flashcards (userid, word, descr, private) values (:userid, :word, :descr, :private)");
        $serial = $this->session->get('LOGINSERIAL');
        $query->bindParam(':userid', $serial, PDO::PARAM_STR);
        $query->bindParam(':word', $word, PDO::PARAM_STR);
        $query->bindParam(':descr', $descr, PDO::PARAM_STR);
        $query->bindParam(':private', $private, PDO::PARAM_INT);
        $query->execute();
        if($query === false){
            return $this->view->render($response, 'new.html', array('flash' => "Ocorreu um erro"));
        }
        return $response->withStatus(302)->withHeader('Location', '/dashboard');
    });

    $this->get('/new', function ( Request $request,  Response $response) {
        return $this->view->render($response, 'new.html');
    })->add($authFlow)->add($auth);
});

$app->group('/login', function() use ($auth, $authFlow){
    $this->get('', function ( Request $request,  Response $response) {
        return $this->view->render($response, 'login.html');
    })->add($authFlow)->add($auth);

    $this->post('', function ( Request $request,  Response $response) {
        $body = $request->getParsedBody();
        $userV = new UserLoginValidation($body, $this->db, $this->session);
        $message = ErrorCodes::getErrorMessage($userV->loginUser(), getUserLang($this->session, $request));
        return $this->view->render($response, 'login.html', array('flash' => $message));
    });
});

$app->get('/', function ( Request $request,  Response $response) {
    return $this->view->render($response, 'main.html');
});


$app->get('/logout', function ( Request $request,  Response $response) {
    if($this->session->exists("LOGINSERIAL")){
        $this->session->delete("LOGINSERIAL");
    }
    return $response->withStatus(302)->withHeader('Location', '/login');
});