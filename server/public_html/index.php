<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

require '../vendor/autoload.php';
include_once '../resource/Database.php';
include_once '../resource/DatabaseObj.php';
include_once '../resource/ErrorHandler.php';
require_once('../resource/TokenAuth.php');
include_once('../resource/User.php');
include_once('../resource/TVShows.php');
include_once('../resource/TVShowsExternal.php');
include_once('../resource/Mailer.php');

header("Access-Control-Allow-Origin: *");
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
$app->post('/auth/forgotpassword', function (Request $request, Response $response, $args) use ($db) {
    return sendRecoveryEmail($request, $response, $db);
});
$app->post('/auth/recoverpassword', function (Request $request, Response $response, $args) use ($db) {
    return setRecoveredPassword($request, $response, $db);
});
$app->get('/series', function (Request $request, Response $response) use ($db) {
    return series_get($request, $response, $db);
});
$app->get('/series/list', function (Request $request, Response $response) use ($db) {
    return series_all($request, $response, $db);
});
$app->post('/series', function (Request $request, Response $response) use ($db) {
    return series_create($request, $response, $db, null);
});
$app->post('/series/{id}', function (Request $request, Response $response, $args) use ($db) {
    $responseOnCreate = series_create($request, $response, $db, $args['id']);
    if($responseOnCreate->getStatusCode() != 200){
        return $isCreated;
    }
    $series_id = json_decode($responseOnCreate->getBody())->last_id;
    
    $newSeries = external_episodes_get($args['id']);
    $mergedSeries = external_episodes_merge($newSeries, $series_id, $db);
    $user_id = $request->getAttribute('user_id');

    return external_series_insert($response, $user_id, $series_id, $mergedSeries, $db);

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
$app->put('/seasons/{id}/watch', function (Request $request, Response $response, $args) use ($db) {
    return seasons_watch($request, $response, $args['id'], $db, 'T');
});
// $app->delete('/seasons/{id}/watch', function (Request $request, Response $response, $args) use ($db) {
//     return seasons_watch($request, $response, $args['id'], $db, 'F');
// });
$app->put('/episodes/{id}/claim', function (Request $request, Response $response, $args) use ($db) {
    return episodes_claim($request, $response, $args['id'], $db, true);
});
$app->delete('/episodes/{id}/claim', function (Request $request, Response $response, $args) use ($db) {
    return episodes_claim($request, $response, $args['id'], $db, false);
});
$app->get('/external/search/{name}', function (Request $request, Response $response, $args) use ($db) {
    return external_series_get($request, $response, $args['name'], $db);
});
$app->put('/external/episodes/{id}/refresh', function (Request $request, Response $response, $args) use ($db) {
    return external_episodes_refresh($request, $response, $args['id'], $db);
});
$app->put('/user', function (Request $request, Response $response, $args) use ($db) {
    return updateUser($request, $response, $db);
});

$app->run();