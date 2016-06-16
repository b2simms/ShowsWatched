<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/slim/method1', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/slim/method1' route");

    // Render index view
    return '{"user" : "admin"}';
});