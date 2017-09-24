<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['determineRouteBeforeAppMiddleware'] = true;

function generateRandomValue($length){
    $data = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxzy1234567890!@#$%&*()-+=";
    $datal = strlen($data);
    $generated = "";

    for($i = 0; $i < $length;$i++){
        $generated .= $data[rand(0,$datal-1)];
    }
    return $generated;
}

$config['db']['host']   = "localhost";
$config['db']['user']   = "postgres";
$config['db']['pass']   = "flash";
$config['db']['dbname'] = "flash";
$config['db']['port'] = "5432";

$app = new \Slim\App(["settings" => $config]);
$app->add(new \Slim\Middleware\Session([
  'name' => 'session',
  'autorefresh' => true,
  'lifetime' => '1 day'
]));

$container = $app->getContainer();

$container['session'] = function ($c) {
  return new \SlimSession\Helper;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("pgsql:host=" . $db['host'] . ";port=". $db['port'] .";dbname=" . $db['dbname'] . ";user=" . $db['user'] . ";password=" . $db['pass']);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = function($c){
    $view = new \Slim\Views\Twig('../views');
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

function checkQueryError($array, $returnCode = false){
    if($array[0] !== '00000'){
        if($returnCode)
            return $array[0];
        else
            return true;
    }else if(isset($array[1])){
        return true;
    }else if(isset($array[2])){
        return true;
    }
    return false;
}

function createSession($userid, $db, $session){
    $serial = generateRandomValue(15);
    $query = $db->prepare("insert into sessions (userid, sessionserial, creationdate, expirationdate) values (:userid , :serial, CURRENT_DATE, CURRENT_DATE + integer '1')");
    $query->bindParam(':userid', $userid, PDO::PARAM_INT);
    $query->bindParam(':serial', $serial, PDO::PARAM_STR);
    $query->bindParam(':serial', $serial, PDO::PARAM_STR);
    $query->execute();
    $error = $query->errorInfo();
    if($error[0] === '23505' && strpos($error[2], '(userid)') !== false){

        $query = $db->prepare("delete from sessions where userid = :userid");
        $query->bindParam(':userid', $userid, PDO::PARAM_INT);
        $query->execute();
        createSession($userid,$db, $session);
    }else{
        $x = 0;
        while($query->errorCode() === '23505' || $x < 3){
            $serial = generateRandomValue(15);
            $query->bindParam(':serial', $serial, PDO::PARAM_STR);
            $query->execute();
            $x++;
        }
        $session->set("LOGINSERIAL", $serial);
        echo $session->get("LOGINSERIAL") . ' AAAAAA';
    }
}

$auth = function ($request, $response, $next) {
    $authState;
    echo $this->session->get("LOGINSERIAL");
    if($this->session->exists("LOGINSERIAL")){
        echo "penis";
        $serial = $this->session->LOGINSERIAL;
        $query = $this->db->prepare("select userid, expirationdate from sessions where sessionserial = :serial and expirationdate < CURRENT_DATE");
        $query->bindParam(':serial', $serial, PDO::PARAM_STR);
        $query->execute();
        $error = checkQueryError($query->errorInfo(),true);
        $row = $query->fetch();
        echo $row;
        echo $error;
        if($row === false || $error === '02000'){
            echo "ola";
            $authState = false;
        }else{
            $authState = $row['userid'];
            echo "olasdasda";

        }
    }else{
        $authState = false;
    }
    if($authState !== false){
        $request = $request->withAttribute('auth', 'y');
        $request = $request->withAttribute('authid', $authState);
        return $response->withStatus(302)->withHeader('Location', '/register');
    }else{
        $request = $request->withAttribute('auth', 'n');
    }
    return $next($request, $response);
};

$app->get('/login', function (Request $request, Response $response) {
    echo $request->getAttribute('auth');
    return $this->view->render($response, 'login.html');

})->add($auth);

$app->post('/login', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $username = filter_var($body['username'], FILTER_SANITIZE_STRING);
    $pw = filter_var($body['password'], FILTER_SANITIZE_STRING);
    $query = $this->db->prepare("select userpw, userid from users where username = :username");
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $row = $query->fetch();
    if(checkQueryError($query->errorInfo()) === false){
        if($row !== false){
            if(isset($row['userpw']) && password_verify($pw, $row['userpw'])){
                createSession($row['userid'], $this->db, $this->session);
                $flash = "LOGADO COM SUCESSO";
            }else{
                $flash = "Usuario ou senhas invalidos.";
            }
        }else{
             $flash = "Usuario não existe";
        }
    }else{
        $flash = "Ocorreu um erro tente mais tarde";
    }
    return $this->view->render($response, 'login.html', array('flash' => $flash));
});

$app->get('/register', function (Request $request, Response $response) {
    return $this->view->render($response, 'register.html');
});

$app->post('/register', function (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $pw = filter_var($body['password'], FILTER_SANITIZE_STRING);
    $rpw = filter_var($body['rpassword'], FILTER_SANITIZE_STRING);
    if($pw !== $rpw){
        $flash = "As senhas não batem";
    }else{
        $username = filter_var($body['username'], FILTER_SANITIZE_STRING);
        $fname = filter_var($body['fname'], FILTER_SANITIZE_STRING);
        $query = $this->db->prepare("insert into users (username, fullname, userpw, creationdate) values (:username , :fullname , :userpw , CURRENT_DATE)");
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':fullname', $fname, PDO::PARAM_STR);
        $query->bindParam(':userpw', password_hash($pw, PASSWORD_BCRYPT), PDO::PARAM_STR);
        $query->execute();
        $error = checkQueryError($query->errorInfo(), true);
        if($error === '23505'){
            $flash = "Usuario já existe";
        }else if($error){
            $flash = "Houve um erro na criação da sua conta";
        }else{
            $flash = "Conta criado com sucesso";
        }
    }
    return $this->view->render($response, 'register.html', array('flash' => $flash));
});

$app->run();