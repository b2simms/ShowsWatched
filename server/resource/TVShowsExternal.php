<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once('../resource/TVShows.php');

function external_series_get(Request $request, Response $response, $name, $db) {

    $tempArr = curl_get_contents('http://api.tvmaze.com/search/shows?q='.$name);
    $tempArr = json_decode($tempArr);

    $outputArr = array();
    foreach ($tempArr as $item) {
        $obj = new stdClass();
        $show = $item->show;
        $obj->id = $show->id;
        $obj->name = $show->name;
        $obj->description = strip_tags($show->summary);
        $outputArr[] = $obj;
    }
    unset($item); // break the reference with the last element

    return $response->getBody()->write(json_encode($outputArr));
}

function external_episodes_refresh(Request $request, Response $response, $series_id, $db) {
    $body = $request->getBody();
    $input = json_decode($body);
    $preexisting_id = $input->series_is_preexisting;
    $preSeries = external_episodes_get($preexisting_id);
    $mergedSeries = external_episodes_merge($preSeries, $series_id, $db);

    $user_id = $request->getAttribute('user_id');

    echo json_encode($mergedSeries);

    return external_series_insert($response, $user_id, $series_id, $mergedSeries, $db);
}

function external_episodes_get($id){
    $tempArr = curl_get_contents('http://api.tvmaze.com/shows/'.$id.'/episodes');
    $tempArr = json_decode($tempArr);

    $outputArr = array();
    foreach ($tempArr as $item) {
        $obj = new stdClass();
        $obj->name = $item->name;
        $obj->description = strip_tags($item->summary);
        $obj->season = $item->season;
        $obj->episode = $item->number;
        $outputArr[] = $obj;
    }
    unset($item); // break the reference with the last element

    return $outputArr;
}

function external_episodes_merge($newSeries, $series_id, $db) {

    //compare and make insert statement
    $sqlQuery = "SELECT e.*, s.name as s_name, s.description as s_description, s.user_id as s_user_id, s.is_private as s_is_private 
        FROM episodes e LEFT JOIN series s ON e.series_id = s.id
        WHERE s.id = :series_id
        AND e.deleted_date = '0000-00-00 00:00:00' 
        AND s.deleted_date = '0000-00-00 00:00:00'
        ORDER BY series_id, season, episode";

    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':series_id' => $series_id));
    $series = $statement->fetchAll(PDO::FETCH_OBJ);

    //merge $newSeries into $series
    $newArray = combine($series, $newSeries);

    return $newArray;
}

function combine($oldS, $newS){
    $newArray = array();
    $mO = 0;
    $mN = 0;
    $isDone = false;
    $oldS_length = count($oldS);
    $newS_length = count($newS);

    $debug = false;

    for ($i = 0; !$isDone; $i++) {
        $pO = $i+$mO;
        $pN = $i+$mN;

        if( !($oldS_length > $pO)){
            $tempLen = $pN;
            while($newS_length > $tempLen){
                if($debug){
                    echo "N > old: Add episode ".$newS[$tempLen]->season .', '.$newS[$tempLen]->episode;
                }
                $newArray[] = $newS[$tempLen];
                $tempLen++;
            }
            $isDone = true;
        }else if( !($newS_length > $pN)){
            $isDone = true;
        }else{
            if($debug){
                echo 'info: '.$newS[$pN]->season.", ".$newS[$pN]->episode ."    " .$oldS[$pO]->season.', '.$oldS[$pO]->episode;
            }
        
            if ($newS[$pN]->season > $oldS[$pO]->season) {
            //if $oldS is done, add all $newS
            //jump to new season
            // move old season pointer
            if($debug){
                echo 'N > old: '.$newS[$pN]->season .' > '. $oldS[$pO]->season;
            }
            $mO++;
            $i--;
            } else if ($newS[$pN]->season == $oldS[$pO]->season) {
            //check episodes
            if ($newS[$pN]->episode == $oldS[$pO]->episode) {
                //skip to next episodes
            } else if ($newS[$pN]->episode > $oldS[$pO]->episode) {
                $mO++;
                $i--;
            }else if ($newS[$pN]->episode < $oldS[$pO]->episode) {
                //skip to next episodes
                $newArray[] = $newS[$pN];
                
                $mN++;
                $i--;
            }
            }else if ($newS[$pN]->season < $oldS[$pO]->season) {
                //jump $oldS season
                // add episodes
                $mN++;
                if($debug){
                    echo "add episode B";
                }
                $newArray[] = $newS[$pN];
                $i--;
            }
        }
    }
    return $newArray;
}

function external_series_insert(Response $response, $user_id, $series_id, $data, $db){

    try{
        $sqlQuery = "SELECT * FROM series
            WHERE id = :id 
            AND user_id = :user_id
            AND deleted_date = '0000-00-00 00:00:00'";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute(array(':id' => $series_id, ':user_id' => $user_id));
        if($stmt->rowCount() == 0){
            return error422($response,"Unauthorized operation - user may not own the series");
        }

        //enter new user into the database
        $query = "INSERT INTO episodes(season, episode, name, series_id, description)  
            VALUES "; //Prequery
        $qPart = array_fill(0, count($data), "(?, ?, ?, ?, ?)");
        $query .=  implode(",",$qPart);
        $stmt = $db->prepare($query); 
        $i = 1;
        foreach($data as $item) { //bind the values one by one
            $stmt->bindValue($i++, $item->season);
            $stmt->bindValue($i++, $item->episode);
            $stmt->bindValue($i++, $item->name);
            $stmt->bindValue($i++, $series_id);
            $stmt->bindValue($i++, $item->description);
        }
        $stmt -> execute(); //execute

        if(!$stmt->rowCount()){
            throw new PDOException('Episodes not created for series.');
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

function curl_get_contents($url){
    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    $result = curl_exec($cURL);
    curl_close($cURL);

    return $result;
}