<?php
//add our database connection script
include_once 'resource/Database.php';
include_once 'resource/utilities.php';

//process the form
if(isset($_POST['messageBtn'])){
    //initialize an array to store any error message from the form
    $form_errors = array();

    //Form validation
    $required_fields = array('email', 'username', 'message');

    //call the function to check empty field and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    //Fields that requires checking for minimum length
    $fields_to_check_length = array('username' => 4, 'message' => 1);

    //call the function to check minimum required length and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_min_length($fields_to_check_length));

    //email validation / merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_email($_POST));

    //check if error array is empty, if yes process form data and insert record
    if(empty($form_errors)){
        //collect form data and store in variables
        $email = $_POST['email'];
        $username = $_POST['username'];
        $message = $_POST['message'];

        try{

            //create SQL insert statement
            $sqlInsert = "INSERT INTO feedback (sender_name, sender_email, message, send_date)
              VALUES (:sender_name, :sender_email, :message, now())";

            //use PDO prepared to sanitize data
            $statement = $db->prepare($sqlInsert);

            //add the data into the database
            $statement->execute(array(':sender_name' => $username, ':sender_email' => $email, ':message' => $message));

            //check if one new row was created
            if($statement->rowCount() == 1){
                $result = "<p style='padding:20px; border: 1px solid gray; color: green;'> Feedback sent!</p>";
            }
        }catch (PDOException $ex){
            $result = "<p style='padding:20px; border: 1px solid gray; color: red;'> An error occurred: ".$ex->getMessage()."</p>";
        }
    }
    else{
        if(count($form_errors) == 1){
            $result = "<p style='color: red;'> There was 1 error in the form<br>";
        }else{
            $result = "<p style='color: red;'> There were " .count($form_errors). " errors in the form <br>";
        }
    }

}

?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Feedback Page</title>
</head>
<body>
<h2>User Authentication System </h2><hr>

<h3>Feedback Form</h3>

 <div>
<?php if(isset($result)) echo $result; ?>
<?php if(!empty($form_errors)) echo show_errors($form_errors); ?>
</div>

<div class="clearfix"></div>

<form method="post" action="">
    <table>
        <tr><td>Email:</td> <td><input type="text" value="" name="email"></td></tr>
        <tr><td>Username:</td> <td><input type="text" value="" name="username"></td></tr>
        <tr><td>Message:</td> <td><textarea type="text" value="" name="message" rows="3" cols="30"></textarea></td></tr>
        <tr><td></td><td><input style="float: right;" type="submit" name="messageBtn" value="Send Message"></td></tr>
    </table>
</form>

<?php 
	try{

        //create SQL insert statement
        $sqlInsert = "Select * from feedback";

        //use PDO prepared to sanitize data
        $statement = $db->prepare($sqlInsert);

		$statement->execute();

        //check if one new row was created
        if($statement->rowCount() == 1){
            $result = "<p style='padding:20px; border: 1px solid gray; color: green;'> Feedback displayed!</p>";
        }
    }catch (PDOException $ex){
        $result = "<p style='padding:20px; border: 1px solid gray; color: red;'> An error occurred: ".$ex->getMessage()."</p>";
    }

    $valid_query = $statement->setFetchMode(PDO::FETCH_ASSOC); 

    $message_list = $statement->fetchAll();
    
    echo show_messages($message_list);

?>

<p><a href="index.php">Back</a> </p>
</body>
</html>