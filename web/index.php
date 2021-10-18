<?php

require_once 'vendor/autoload.php';

use Conn\Database;
use App\Users;
use App\Activate;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;


try {
    $routeUsers = new Route('/users');
    $routeUsersId = new Route('/users/{id}', [], ['id' => '\\d+']);
    $routeUsersActivation = new Route("/users/activation");

    $routes = new RouteCollection();
    $routes->add('getUsers', $routeUsers);
    $routes->add('getUser', $routeUsersId);
    $routes->add('userActivation', $routeUsersActivation);

    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match($context->getPathInfo());
} catch (Exception $e) {
    Users::jsonResponse('The request is incorrect', 404);
    return;
}

$db = Database::connection();

if ($parameters['_route'] === 'userActivation') {
    $token = $_GET['token'];
    if (!$token) {
        Users::jsonResponse('The request is incorrect', 404);
    }
    Activate::confirmEmail($token);
    return;
}

$data = file_get_contents('php://input');
$data = json_decode($data, true);

switch ($context->getMethod()) {
    case 'GET':
        if (!$parameters['id']) {
            Users::getUsers($db, $_GET);
        } else {
            Users::getUser($db, $parameters['id']);
        }
        break;
    case 'POST':
        if (!$parameters['id']) {
            Users::addUser($db, $data);
        } else {
            http_response_code(404);
        }
        break;
    case 'DELETE':
        if ($parameters['id']) {
            Users::deleteUser($db, $parameters['id']);
        } else {
            http_response_code(404);
        }
        break;
    case 'PUT':
        if ($parameters['id']) {
            $data['id'] = $parameters['id'];
            Users::updateUser($db, $data);
        } else {
            http_response_code(404);
        }
        break;
}