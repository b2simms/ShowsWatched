<?php

try{
    //create an instance of the PDO class with the required parameters
    $db = new PDO($dsn, $username, $password);

    //set pdo error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //display success message
    //echo "Connected to the register database";

}catch (PDOException $ex){
    //display error message
    print_r("Connection failed ".$ex->getMessage());
}

function db_get($db, $sqlQuery, $params){
    $statement = $db->prepare($sqlQuery);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}