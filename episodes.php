<?php
//add our database connection script
include_once 'resource/Database.php';
include_once 'resource/utilities.php';

?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Episodes list</title>
</head>
<body>

 <div>
<?php if(isset($result)) echo $result; ?>
</div>

<div class="clearfix"></div>

<?php 
	try{

        //create SQL insert statement
        $sqlInsert = "Select * from episodes";

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

</body>
</html>