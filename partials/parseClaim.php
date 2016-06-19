<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if(isset($_POST['unwatchBtn'])){
    //array to hold errors
  $form_errors = array();

  if(empty($form_errors)){

    //collect form data
    $id = $_POST['id'];

    if(!isCurrentData($id,$_POST['status'],$db)){
      refreshPage();
    }   

    //update episodes set status = 0 where id = :episodeId;
    $sqlQuery = "update episodes set status = 0 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          callSweetAlert($id);
    }else{
      $result = flashMessage("Did not update");
    }

  }else{
    if(count($form_errors) == 1){
      $result = flashMessage("There was one error in the entry");
  }else{
    $result = flashMessage("There were " .count($form_errors). " errors in the entry <br>");
  }
}
}

if(isset($_POST['watchBtn'])){
    //array to hold errors
  $form_errors = array();

  if(empty($form_errors)){

        //collect form data
    $id = $_POST['id'];

    if(!isCurrentData($id,$_POST['status'],$db)){
      refreshPage();
    }

    //update episodes set status = 2 where id = :episodeId;
    $sqlQuery = "update episodes set status = 2 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          callSweetAlert($id);
    }else{
      $result = flashMessage("Did not update");
    }

  }else{
    if(count($form_errors) == 1){
      $result = flashMessage("There was one error in the entry");
  }else{
    $result = flashMessage("There were " .count($form_errors). " errors in the entry <br>");
  }
}
}

if(isset($_POST['claimBtn'])){
    //array to hold errors
  $form_errors = array();

  if(empty($form_errors)){

        //collect form data
    $id = $_POST['id'];

    if(isAuthorizedUser()){
      $name = $_SESSION['username'];
    }

    if(!isCurrentData($id,$_POST['status'],$db)){
      refreshPage();
    }

    //update episodes set status = 1 where id = :episodeId;
    $sqlQuery = "update episodes set status = 1, assigned_name = :userName where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':userName' => $name, ':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          callSweetAlert($id);
    }else{
      $result = flashMessage("Did not update");
    }

  }else{
    if(count($form_errors) == 1){
      $result = flashMessage("There was one error in the entry");
  }else{
    $result = flashMessage("There were " .count($form_errors). " errors in the entry <br>");
  }
}
}

if(isset($_POST['unclaimBtn'])){
    //array to hold errors
  $form_errors = array();

  if(empty($form_errors)){

        //collect form data
    $id = $_POST['id'];

    if(!isCurrentData($id,$_POST['status'],$db)){
      refreshPage();
    }

    //update episodes set status = 0 where id = :episodeId;
    $sqlQuery = "update episodes set status = 0 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          callSweetAlert($id);
    }else{
      $result = flashMessage("Did not update");
    }

  }else{
    if(count($form_errors) == 1){
      $result = flashMessage("There was one error in the entry");
  }else{
    $result = flashMessage("There were " .count($form_errors). " errors in the entry <br>");
  }
}
}


function callSweetAlert($message_id){
    
  $_SESSION['PHP_Redirect'] = "True";
  // Redirect to this page.
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit();
}

function refreshPage(){
  $_SESSION['PHP_Need_Refresh'] = "True";
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit();
}

function isCurrentData($message_id, $current_episode_status, $db){

  try{
    //create SQL select statement
    $sqlQuery = "SELECT * FROM episodes WHERE id = :messageId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':messageId' => $message_id));

  }catch (PDOException $ex){
    $result = flashMessage("An error occurred: ".$ex->getMessage());    
  }
  
  $valid_query = $statement->setFetchMode(PDO::FETCH_ASSOC); 
  $message_list = $statement->fetchAll();
        
  try{
    // var_dump("status: ".$message_list[0]['status']);
    // var_dump("$_SESSION[username]: ".$_SESSION['username']);
    if($message_list[0]['status'] == $current_episode_status){
      //if($message_list[0]['status'] == '' || $message_list[0]['status'] == $_SESSION['username']){
        return true;
      //}
    }
  }catch (Exception $ex){
    $result = flashMessage("An error occurred: ".$ex->getMessage());    
  }
  return false;    
}

if(isset($_SESSION['PHP_Redirect']) && $_SESSION['PHP_Redirect'] = "True"){
  echo $welcome = "<script type=\"text/javascript\"> 
    swal({   
      title: \"Updated!\",   
      text: \"Record has been updated.\", 
      type: 'success',    
      showConfirmButton: true,
    });</script>";

  unset($_SESSION['PHP_Redirect']);
}
if(isset($_SESSION['PHP_Need_Refresh']) && $_SESSION['PHP_Need_Refresh'] = "True"){
  echo $welcome = "<script type=\"text/javascript\"> 
    swal({   
      title: \"Out of Date\",   
      text: \"Refreshing now...\", 
      type: 'error',    
      timer: 4000,   
      showConfirmButton: false 
    });</script>";

  unset($_SESSION['PHP_Need_Refresh']);
}

?>



