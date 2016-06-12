<?php
include_once 'resource/Database.php';
include_once 'resource/utilities.php';


if(isset($_POST['loginBtn'])){
    //array to hold errors
    $form_errors = array();

//validate
    $required_fields = array('username', 'password');
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    if(empty($form_errors)){

        //collect form data
        $user = $_POST['username'];
        $password = $_POST['password'];

        //check if user exist in the database
        $sqlQuery = "SELECT * FROM users WHERE username = :username";
        $statement = $db->prepare($sqlQuery);
        $statement->execute(array(':username' => $user));

       while($row = $statement->fetch()){
           $id = $row['id'];
           $hashed_password = $row['password'];
           $username = $row['username'];

           if(password_verify($password, $hashed_password)){
               $_SESSION['id'] = $id;
               $_SESSION['username'] = $username;


               //call sweet alert
               echo $welcome = "<script type=\"text/javascript\"> 
                  swal({   
                    title: \"Welcome back $username!\",   
                    text: \"You are being logged in.\", 
                    type: 'success',  
                    timer: 6000,   
                    showConfirmButton: false });


                    setTimeout(function(){     
                      window.location.href = 'index.php';  
                    }, 5000);


               </script>";
        
           }else{
               $result = flashMessage("Invalid username or password");
           }
       }

    }else{
        if(count($form_errors) == 1){
            $result = flashMessage("There was one error in the form");
        }else{
            $result = flashMessage("There were " .count($form_errors). " errors in the form <br>");
        }
    }
}
?>