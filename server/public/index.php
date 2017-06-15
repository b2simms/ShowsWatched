<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

require '../vendor/autoload.php';
include_once '../resource/Database.php';
include_once '../resource/ErrorHandler.php';
require_once('../resource/TokenAuth.php');
include_once('../resource/User.php');
include_once('../resource/TVShows.php');

header("Access-Control-Allow-Origin: http://localhost");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description, Authorization');
header('Content-type:application/json;charset=utf-8');

$key = "example_key";

$app = new \Slim\App();
$app->add(new TokenAuth($key));

$app->post('/auth/login', function (Request $request, Response $response) use ($db, $key) {
    return login($request, $response, $db, $key);
});
$app->post('/auth/register', function (Request $request, Response $response) use ($db) {
    return register($request, $response, $db);
});
$app->get('/series', function (Request $request, Response $response) use ($db) {
    return series_get($request, $response, $db);
});
$app->get('/series/list', function (Request $request, Response $response) use ($db) {
    return series_all($request, $response, $db);
});
$app->post('/series', function (Request $request, Response $response) use ($db) {
    return series_create($request, $response, $db);
});
$app->put('/series/{id}', function (Request $request, Response $response, $args) use ($db) {
    return series_update($request, $response, $args['id'], $db);
});
$app->delete('/series/{id}', function (Request $request, Response $response, $args) use ($db) {
    return series_delete($request, $response, $args['id'], $db);
});
$app->post('/series/{id}/episodes', function (Request $request, Response $response, $args) use ($db) {
    return episodes_create($request, $response, $args['id'], $db);
});
$app->put('/episodes/{id}', function (Request $request, Response $response, $args) use ($db) {
    return episodes_update($request, $response, $args['id'], $db);
});
$app->delete('/episodes/{id}', function (Request $request, Response $response, $args) use ($db) {
    return episodes_delete($request, $response, $args['id'], $db);
});
$app->put('/episodes/{id}/watch', function (Request $request, Response $response, $args) use ($db) {
    return episodes_watch($request, $response, $args['id'], $db, 'T');
});
$app->delete('/episodes/{id}/watch', function (Request $request, Response $response, $args) use ($db) {
    return episodes_watch($request, $response, $args['id'], $db, 'F');
});
$app->put('/episodes/{id}/claim', function (Request $request, Response $response, $args) use ($db) {
    return episodes_claim($request, $response, $args['id'], $db, true);
});
$app->delete('/episodes/{id}/claim', function (Request $request, Response $response, $args) use ($db) {
    return episodes_claim($request, $response, $args['id'], $db, false);
});

$app->get('/test', function (Request $request, Response $response) {

    $output = new stdClass();    
    $output->user_id = $request->getAttribute('user_id');
    $output->time = time();
    $myJson = json_encode($output);
    $response->getBody()->write($myJson);

    return $response;
});

$app->run();