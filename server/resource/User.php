<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

function login(Request $request, Response $response, $db, $key) {

    $body = $request->getBody();
    $input = json_decode($body);
    $user = $input->username;
    $password = $input->password;

    $sqlQuery = "SELECT * FROM users WHERE username = :username AND is_deleted != 'T'";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':username' => $user));

    if($statement->rowCount() > 0){
    while($row = $statement->fetch()){
        $id = $row['id'];
        $hashed_password = $row['password'];
        $username = $row['username'];
        $role = $row['role'];

        $credentialsAreValid = false;
        if(password_verify($password, $hashed_password)){
            $credentialsAreValid = true;
        }

        if ($credentialsAreValid) {
            $token = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1496844000,
                "nbf" => 1496844000,
                "name" => $username,
                "user_id" => $id,
                "role" => $role
            );

            $jwt = JWT::encode($token, $key);
            $decoded = JWT::decode($jwt, $key, array('HS256'));

            $decoded_array = (array) $decoded;

            $output = new stdClass();
            $output->token = $jwt;
            $output->decoded = $decoded;
            $response = $response->withStatus(200);
        }
    }
    }
    if(empty($output)){
        $output = new stdClass();
        $output->message = "The username and/or password are not in the database.";
        $response = $response->withStatus(422);
    }

    $myJson = json_encode($output);    
    $response->getBody()->write($myJson);

    return $response;
}

function register(Request $request, Response $response, $db) {
    $body = $request->getBody();

    try{
        $input = json_decode($body);
        $username = $input->username;
        $password = $input->password;
        $email = $input->email;
    }catch(Exception $e){
        $output->message = "Missing or invalid parameters";
        $response = $response->withStatus(422);
        $myJson = json_encode($output);
        $response->getBody()->write($myJson);
        return $response;
    }

    $output = new stdClass();
    try{
        //enter new user into the database
        //hashing the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sqlInsert = "INSERT INTO users (username, password, email, join_date) 
            VALUES (:username, :password, :email, now() )";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':username' => $username, ':password' => $hashed_password, ':email' => $email));

    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            return error422($response, "Username already exists; please choose a new one");
        } else {
            return error422($response, "Unsuccessful registration: ".$e->getMessage().$e->getCode());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful registration: ".$e->getMessage());
    }
    return $response;
}
