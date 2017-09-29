<?php

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

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

$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        // permanently redirect paths with a trailing slash
        // to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});

$container = $app->getContainer();

$container['session'] = function ($c) {
  return new \SlimSession\Helper;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$supportedLangs = array('en', 'pt');

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

function getUserLang($session, $request){
    if($session->exists('LANG'))
        return $session->get('LANG');
    else{
        $lang = substr($request->getHeaderLine('Accept-Language'), 0, 2);
        if(in_array($lang, $supportedLangs))
            $session->set('LANG', $lang);
        else{
            $lang = 'en';
            $session->set('LANG', $lang);
        }
        return $lang;
    }
}

$authFlow = function ($request, $response, $next){
    $path = $request->getUri()->getPath();
    $authState = $request->getAttribute('auth');
    if($authState === false){
        if($path === '/dashboard'){
            return $response->withStatus(302)->withHeader('Location', '/login');
        }else if($path === '/dashboard/new'){
            return $response->withStatus(302)->withHeader('Location', '/login');
        }else if($path === '/logout'){
            return $response->withStatus(302)->withHeader('Location', '/login');
        }else{
            return $next($request, $response);
        }
    }else{
        if($path === '/login'){
            return $response->withStatus(302)->withHeader('Location', '/dashboard');
        }else if($path === '/register'){
            return $response->withStatus(302)->withHeader('Location', '/dashboard');
        }else{
            return $next($request, $response);
        }
    }
};

$auth = function ($request, $response, $next) {
    $authState = false;
    if($this->session->exists('LOGINSERIAL')){
        $authenticate = new SessionAuthentication($this->db, $this->session->get('LOGINSERIAL'));
        $authState = $authenticate->authenticateSerial();
        if($authState === false){
            $this->session->delete('LOGINSERIAL');
        }
    }
    $request = $request->withAttribute('auth', $authState);
    return $next($request, $response);
};

require '../routes/routes.php';

$app->run();