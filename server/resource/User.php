<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

function login(Request $request, Response $response, $db, $key) {

    $body = $request->getBody();
    $input = json_decode($body);
    $user = $input->username;
    $password = $input->password;

    $sqlQuery = "SELECT * FROM users WHERE username = :username AND deleted_date = '0000-00-00 00:00:00'";
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
    $input = json_decode($body);

    $ipi = true;
    $output = new stdClass();
    if(!isset($input->username)){
        $ipi = false;
    }
    if(!isset($input->password)){
        $ipi = false;
    }
    if(!isset($input->email)){
        $ipi = false;
    }
    if( ! $ipi){
        return error422($response,"Missing or invalid parameters");
    }
    $username = $input->username;
    $password = $input->password;
    $email = $input->email;

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
            return error422($response, "Username or email already exists; please choose a new one");
        } else {
            return error422($response, "Unsuccessful registration: ".$e->getMessage().$e->getCode());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful registration: ".$e->getMessage());
    }
    return $response;
}

function setRecoveredPassword(Request $request, Response $response, $db) {
    //get query params
    $body = $request->getBody();
    $input = json_decode($body);
    $requestID = $input->requestID;
    $password = $input->password;

    //get user from request id
    $user = getUser($requestID, $db);
    //if all pass, update password with the new password - hash it with: password_hash($password, PASSWORD_DEFAULT);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    return updatePassword($user->id, $hashed_password, $db, $response);
}
function getUser($requestID, $db){
    //check that date reset_hash and reset_date are both set
    //check 24 hours expiry on date
    $sqlQuery = "SELECT * FROM users 
        WHERE reset_hash = :requestID
        AND reset_date != '0000-00-00 00:00:00'
        AND deleted_date = '0000-00-00 00:00:00'";
    $params = array(':requestID' => $requestID);
    $userData = db_get($db, $sqlQuery, $params);

    if(count($userData) > 0){
        $tempObj = new stdClass();
        $tempObj->id = $userData[0]['id'];
        $tempObj->username = $userData[0]['username'];
        $tempObj->reset_hash = $userData[0]['reset_hash'];
        $tempObj->reset_date = $userData[0]['reset_date'];
        return  $tempObj;
    }
    return false;
}

function updatePassword($id, $hashed_password, $db, $response){
    //enter new user into the database
    $sqlInsert = "UPDATE users SET password = :hashed_password, reset_hash = '', reset_date = '0000-00-00 00:00:00'
        WHERE id = :id";
    $stmt = $db->prepare($sqlInsert);
    $stmt->execute(array(':hashed_password' => $hashed_password, ':id' => $id));
    if(!$stmt->rowCount()){
        return error422($response, "Password failed to update");
    }
    $response = $response->withStatus(201);
    return $response;
}