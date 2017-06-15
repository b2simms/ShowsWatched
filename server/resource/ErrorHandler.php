<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function error422($response, $message){
    $output = new stdClass;
    $output->message = $message;
    $response = $response->withStatus(422);
    $response->getBody()->write(json_encode($output));
    return $response;
}