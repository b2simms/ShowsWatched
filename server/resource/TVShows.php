<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function series_get(Request $request, Response $response, $db) {

    $user_id = $request->getAttribute('user_id');
    $sqlQuery = "SELECT s.*, COUNT(DISTINCT e.season) as seasons, u.username 
        FROM series s LEFT JOIN episodes e 
        ON e.series_id = s.id AND e.is_deleted != 'T' 
        JOIN users u ON s.user_id = u.id 
        WHERE (s.user_id = :user_id or s.is_private != 'T') 
        AND s.is_deleted != 'T' 
        GROUP BY s.id";
    $params = array(':user_id' => $user_id);
    $data = db_get($db, $sqlQuery, $params);

    $seriesArr = array();
    if(!empty($data)){
        foreach ($data as $row) {
            $obj = new stdClass();
            $obj->id = $row['id'];
            $obj->name = $row['name'];
            $obj->description = $row['description'];
            $obj->user_id = $row['user_id'];
            $obj->is_private = $row['is_private'];
            $obj->seasons = $row['seasons'];
            $obj->username = $row['username'];
            $seriesArr[] = $obj;
        }
        unset($row); // break the reference with the last element
    }
    return $response->getBody()->write(json_encode($seriesArr));
}

function series_all(Request $request, Response $response, $db) {

    $user_id = $request->getAttribute('user_id');

    $sqlQuery = "SELECT e.*, s.name as s_name, s.description as s_description, s.user_id as s_user_id, s.is_private as s_is_private 
        FROM episodes e LEFT JOIN series s ON e.series_id = s.id 
        WHERE (user_id = :user_id OR s.is_private != 'T')
        AND e.is_deleted != 'T' 
        AND s.is_deleted != 'T'
        ORDER BY series_id, season, episode";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':user_id' => $user_id));

    $series = array();

    if($statement->rowCount() > 0){
        $currentSeries = -1;
        $seriesPos = -1;
        $currentSeason = -1;
        $seasonPos = -1;
        while($row = $statement->fetch()){
            if($currentSeries !== $row['series_id']){
                $currentSeries = $row['series_id'];
                $seriesObj = new stdClass();
                $seriesObj->id = $row['series_id'];
                $seriesObj->name = $row['s_name'];
                $seriesObj->description = $row['s_description'];
                $seriesObj->user_id = $row['s_user_id'];
                $seriesObj->is_private = $row['s_is_private'];
                $seriesObj->seasons =array();
                $series[] = $seriesObj;
                $seriesPos++;
                $seasonPos = -1;
                $currentSeason = -1;
            }

            /* $row
            {   
                "id":"1",
                "season":"1",
                "episode":"1",
                "date":null,
                "name":"Rose",
                "status":"2",
                "assigned_name":"Emilynn",
                "series_id":"1",
                "description":"0",
                "is_deleted":"F",
                "is_watched":"F",
                "claimed_by_user":"0",
                "s_name":"Doctor Who",
                "s_description":"Doc Who description",
                "s_user_id":"8",
                "s_is_private":"F",
            }*/
            
            if($currentSeason !== $row['season']){
                $seasonObj = new stdClass();
                $currentSeason = $row['season'];
                $seasonObj->season = $row['season'];
                $seasonObj->episodes = array();
                $series[$seriesPos]->seasons[] = $seasonObj;
                $seasonPos++;
            }
            $episodeObj = new stdClass();
            $episodeObj->id = $row['id'];
            $episodeObj->episode = $row['episode'];
            $episodeObj->date = $row['date'];
            $episodeObj->name = $row['name'];
            $episodeObj->description = $row['description'];
            $episodeObj->is_deleted = $row['is_deleted'];
            $episodeObj->is_watched = $row['is_watched'];
            $episodeObj->claimed_by_user = $row['claimed_by_user'];
            $episodeObj->assigned_name = $row['assigned_name'];

            $series[$seriesPos]->seasons[$seasonPos]->episodes[] = $episodeObj;
        }
    }
    return $response->getBody()->write(json_encode($series));
}

function series_create(Request $request, Response $response, $db) {
    $body = $request->getBody();
    $input = json_decode($body);
    $user_id = $request->getAttribute('user_id');

    $ipi = true;
    $output = new stdClass();
    if(!isset($input->is_private)){
        $ipi = false;
    }
    if(!isset($input->description)){
        $ipi = false;
    }
    if(!isset($input->name)){
        $ipi = false;
    }
    if( ! $ipi){
        return error422($response,"Missing or invalid parameters");
    }
    $is_private = $input->is_private;
    $description = $input->description;
    $name = $input->name;

    try{
        //enter new user into the database
        $sqlInsert = "INSERT INTO series (id, user_id, is_private, description, name, is_deleted) 
            VALUES (NULL, :user_id, :is_private, :description, :name, 'F')";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':user_id' => $user_id, ':is_private' => $is_private, ':description' => $description, ':name'=> $name));
        if(!$stmt->rowCount()){
            throw new PDOException('Series not created.');
        }
    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            return error422($response, "You have already created a series with that name; please choose a new one");
        } else {
            return error422($response, "Unsuccessful creation of series: ".$e->getMessage());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful creation of series: ".$e->getMessage());
    }
    return $response;
}

function series_update(Request $request, Response $response, $id, $db) {
    $body = $request->getBody();
    $input = json_decode($body);
    $user_id = $request->getAttribute('user_id');

    $ipi = true;
    $output = new stdClass();
    if(!isset($input->is_private)){
        $ipi = false;
    }
    if(!isset($input->description)){
        $ipi = false;
    }
    if(!isset($input->name)){
        $ipi = false;
    }
    if( ! $ipi){
        return error422($response,"Missing or invalid parameters");
    }
    $is_private = $input->is_private;
    $description = $input->description;
    $name = $input->name;

    try{
        //enter new user into the database
        $sqlInsert = "UPDATE series SET is_private = :is_private, description = :description, name = :name
            WHERE id = :id AND user_id = :user_id AND is_deleted != 'T'";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':is_private' => $is_private, ':description' => $description, ':name' => $name, ':id' => $id, ':user_id' => $user_id));
        if(!$stmt->rowCount()){
            throw new PDOException('User does not own this series');
        }
    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            return error422($response, "You have already created a series with that name; please choose a new one");
        } else {
            return error422($response, "Unauthorized operation: ".$e->getMessage());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful update of series: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}

function series_delete(Request $request, Response $response, $id, $db) {
    $user_id = $request->getAttribute('user_id');

    $output = new stdClass();
    try{
        $sqlQuery = "SELECT * FROM series 
            WHERE id = :id
            AND user_id = :user_id 
            AND is_deleted != 'T'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $id, ':user_id' => $user_id));

        if($stmt->rowCount() == 0){
             return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $sqlInsert = "UPDATE series SET is_deleted = 'T'
            WHERE id = :id";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':id' => $id));
        if(!$stmt->rowCount()){
            throw new PDOException('Series not deleted.');
        }
     }catch (PDOException $e) {
        return error422($response, "Unsuccessful deletion of series: ".$e->getMessage());
    }catch(Exception $e){
        return error422($response, "Unsuccessful deletion of series: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}

function episodes_create(Request $request, Response $response, $series_id, $db) {
    $body = $request->getBody();
    $input = json_decode($body);
    $user_id = $request->getAttribute('user_id');

    $ipi = true;
    $output = new stdClass();
    
    if(!isset($input->episode)){
        $ipi = false;
    }
    if(!isset($input->name)){
        $ipi = false;
    }
    if(!isset($input->description)){
        $ipi = false;
    }
    if(!isset($input->season)){
        $ipi = false;
    }
    if( ! $ipi){
        return error422($response,"Missing or invalid parameters");
    }
    $season = $input->season;
    $episode = $input->episode;
    $name = $input->name;
    $description = $input->description;

    try{
        $sqlQuery = "SELECT * FROM series
            WHERE id = :id 
            AND user_id = :user_id
            AND is_deleted != 'T'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $series_id, ':user_id' => $user_id));
        if($stmt->rowCount() == 0){
            return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $sqlInsert = "INSERT INTO episodes(id, season, episode, name, series_id, description)  
            VALUES (NULL, :season, :episode, :name, :series_id, :description)";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':season' => $season, ':episode' => $episode, ':description' => $description, ':name'=> $name, ':series_id' => $series_id));
        if(!$stmt->rowCount()){
            throw new PDOException('Episode not created.');
        }
    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            return error422($response, "You have already created an episode with that name; please choose a new one");
        } else {
            return error422($response, "Unsuccessful creation of episode: ".$e->getMessage());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful creation of episode: ".$e->getMessage());
    }
    return $response;
}

function episodes_update(Request $request, Response $response, $id, $db) {
    $body = $request->getBody();
    $input = json_decode($body);
    $user_id = $request->getAttribute('user_id');

    $ipi = true;
    $output = new stdClass();
    
    if(!isset($input->episode)){
        $ipi = false;
    }
    if(!isset($input->name)){
        $ipi = false;
    }
    if(!isset($input->description)){
        $ipi = false;
    }
    if(!isset($input->season)){
        $ipi = false;
    }
    if( ! $ipi){
        return error422($response,"Missing or invalid parameters");
    }
    $episode = $input->episode;
    $name = $input->name;
    $description = $input->description;
    $season = $input->season;

    try{
         $sqlQuery = "SELECT * FROM episodes e JOIN series s on s.id = e.series_id 
            WHERE e.id = :id 
            AND s.user_id = :user_id
            AND e.is_deleted != 'T' 
            AND s.is_deleted != 'T'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $id, ':user_id' => $user_id));
        if($stmt->rowCount() == 0){
            return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $sqlInsert = "UPDATE episodes SET name = :name, episode = :episode, season = :season, description = :description
            WHERE id = :id";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':name' => $name, ':episode' => $episode, ':season' => $season, ':description' => $description, ':id' => $id));
        if(!$stmt->rowCount()){
            throw new PDOException('Episode not updated.');
        }
     }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            return error422($response, "You have already created an episode with that name; please choose a new one");
        } else {
            return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
        }
    }catch(Exception $e){
        return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}

function episodes_watch(Request $request, Response $response, $id, $db, $is_watched) {
    $user_id = $request->getAttribute('user_id');

    $output = new stdClass();
    try{
        $sqlQuery = "SELECT * FROM episodes e JOIN series s on s.id = e.series_id 
            WHERE e.id = :id 
            AND s.user_id = :user_id 
            AND e.is_deleted != 'T' 
            AND s.is_deleted != 'T'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $id, ':user_id' => $user_id));

        if($stmt->rowCount() == 0){
             return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $sqlInsert = "UPDATE episodes SET is_watched = :is_watched
            WHERE id = :id";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':id' => $id, ':is_watched' => $is_watched));
        if(!$stmt->rowCount()){
            throw new PDOException('Episode not updated.');
        }
     }catch (PDOException $e) {
        return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
    }catch(Exception $e){
        return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}

function episodes_claim(Request $request, Response $response, $id, $db, $claim) {
    $user_id = $request->getAttribute('user_id');
    $name = $request->getAttribute('name');

    $output = new stdClass();
    try{
        $sqlQuery = "SELECT * FROM series s LEFT JOIN episodes e on s.id = e.series_id 
            WHERE e.id = :id
            AND e.is_watched != 'T'
            AND s.is_private != 'T' 
            AND e.is_deleted != 'T' 
            AND s.is_deleted != 'T'";
        if($claim){
            $sqlQuery .=" AND e.claimed_by_user <> '0'";
        }else{
            $sqlQuery .=" AND e.claimed_by_user = '0'";
        }
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $id));
        if($stmt->rowCount() > 0){
            //the episode has been claimed or watched or the series is not public"
            return error422($response,"The episode has been claimed or watched or the series is not public");
        }

        //build call based on claim episode condition
        if($claim){
            $sqlInsert = "UPDATE episodes SET claimed_by_user = :user_id, assigned_name = :name
                WHERE id = :id";
            $stmt = $db->prepare($sqlInsert);
            $stmt->execute(array(':user_id' => $user_id, ':name' => $name, ':id' => $id));
        }else{
            $sqlInsert = "UPDATE episodes SET claimed_by_user = '0', assigned_name = null
                WHERE id = :id";
            $stmt = $db->prepare($sqlInsert);
            $stmt->execute(array(':id' => $id));
        }
        //check if updated
        if(!$stmt->rowCount()){
            throw new PDOException('Episode not updated -> could not watch');
        }
     }catch (PDOException $e) {
        return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
    }catch(Exception $e){
        return error422($response, "Unsuccessful update of episode: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}

function episodes_delete(Request $request, Response $response, $id, $db) {
    $user_id = $request->getAttribute('user_id');

    $output = new stdClass();
    try{
        $sqlQuery = "SELECT * FROM episodes e JOIN series s on s.id = e.series_id 
            WHERE e.id = :id 
            AND s.user_id = :user_id 
            AND e.is_deleted != 'T' 
            AND s.is_deleted != 'T'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $id, ':user_id' => $user_id));

        if($stmt->rowCount() == 0){
             return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $sqlInsert = "UPDATE episodes SET is_deleted = 'T'
            WHERE id = :id";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':id' => $id));
        if(!$stmt->rowCount()){
            throw new PDOException('Episode not deleted.');
        }
     }catch (PDOException $e) {
        return error422($response, "Unsuccessful deletion of episode: ".$e->getMessage());
    }catch(Exception $e){
        return error422($response, "Unsuccessful deletion of episode: ".$e->getMessage());
    }
    $response = $response->withStatus(201);
    return $response;
}