<?php
//add our database connection script
include_once 'resource/Database.php';
include_once 'resource/utilities.php';

//process the form
if(isset($_POST['signupBtn'])){
    //initialize an array to store any error message from the form
    $form_errors = array();

    //Form validation
    $required_fields = array('username', 'password');

    //call the function to check empty field and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    //Fields that requires checking for minimum length
    $fields_to_check_length = array('username' => 3, 'password' => 3);

    //call the function to check minimum required length and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_min_length($fields_to_check_length));

    //collect form data and store in variables
    $username = $_POST['username'];
    $password = $_POST['password'];

    if(checkDuplicateEntries("users","username",$username,$db)){
        $result = flashMessage("Username is already taken. Please choose another one.");
    }
    //check if error array is empty, if yes process form data and insert record
    else if(empty($form_errors)){
        
        //hashing the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try{

            //create SQL insert statement
            $sqlInsert = "INSERT INTO users (username, password, join_date)
              VALUES (:username, :password, now())";

            //use PDO prepared to sanitize data
            $statement = $db->prepare($sqlInsert);

            //add the data into the database
            $statement->execute(array(':username' => $username, ':password' => $hashed_password));

            //check if one new row was created
            if($statement->rowCount() == 1){
                //call sweet alert
               $result = "<script type=\"text/javascript\"> 
                  swal({   
                    title: \"Congratulations $username!\",   
                    text: \"Registration completed successfully!\", 
                    type: 'success',     
                    confirmButtonText: \"Thank You!\" });

               </script>";
            }
        }catch (PDOException $ex){
            $result = flashMessage("An error occured: ".$ex->getMessage());
        }
    }
    else{
        if(count($form_errors) == 1){
            $result = flashMessage("There was 1 error in the form");
        }else{
            $result = flashMessage("There were " .count($form_errors). " errors in the form");
        }
    }

}

?>