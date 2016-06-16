<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if(isset($_POST['unwatchBtn'])){
    //array to hold errors
  $form_errors = array();

  if(empty($form_errors)){

        //collect form data
    $id = $_POST['id'];

    //update episodes set status = 0 where id = :episodeId;
    $sqlQuery = "update episodes set status = 0 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          echo $welcome = "<script type=\"text/javascript\"> 
          swal({   
            title: \"Updated!\",   
            text: \"Record has been updated.\", 
            type: 'success',    
            showConfirmButton: true });
            </script>";
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

    //update episodes set status = 2 where id = :episodeId;
    $sqlQuery = "update episodes set status = 2 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          echo $welcome = "<script type=\"text/javascript\"> 
          swal({   
            title: \"Updated!\",   
            text: \"Record has been updated.\", 
            type: 'success',    
            showConfirmButton: true });
            </script>";
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

    //update episodes set status = 1 where id = :episodeId;
    $sqlQuery = "update episodes set status = 1, assigned_name = :userName where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':userName' => $name, ':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          echo $welcome = "<script type=\"text/javascript\"> 
          swal({   
            title: \"Updated!\",   
            text: \"Record has been updated.\", 
            type: 'success',    
            showConfirmButton: true });
            </script>";
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

    //update episodes set status = 0 where id = :episodeId;
    $sqlQuery = "update episodes set status = 0 where id = :episodeId";
    $statement = $db->prepare($sqlQuery);
    $statement->execute(array(':episodeId' => $id));

    if($statement->rowCount() > 0){
          //call sweet alert
          echo $welcome = "<script type=\"text/javascript\"> 
          swal({   
            title: \"Updated!\",   
            text: \"Record has been updated.\", 
            type: 'success',    
            showConfirmButton: true });
            </script>";
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
?>