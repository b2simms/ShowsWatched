<?php
//add our database connection script
include_once 'resource/Database.php';
include_once 'resource/utilities.php';

$page_title = "Doctor Who - Episodes"; 
include_once 'partials/headers.php'; 
?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Episodes list</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript">
        $(window).scroll(function () {
            if ($(window).scrollTop() > 226) {
                $(".header").addClass("fixed");
            } else {
                $(".header").removeClass("fixed");
            }
        });
    </script>

</head>
<body data-spy="scroll" data-target="#myScrollspy" data-offset="20">
<div class="container">
  <div class="flag">
      <h1>Brent's Doctor Who Watch List</h1>

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
        

        //echo show_messages($message_list);

    ?>

  <div class="container-fluid">
    <div class="row">
      <nav class="fixed" id="myScrollspy">
        <ul class="nav nav-pills">
          <li><a href="#section1">Series 1</a></li>
          <li><a href="#section2">Series 2</a></li>
          <li><a href="#section3">Series 3</a></li>
          <li><a href="#section4">Series 4</a></li>
          <li><a href="#section5">Series 5</a></li>
          <li><a href="#section6">Series 6</a></li>
          <li><a href="#section7">Series 7</a></li>
          <li><a href="#section8">Series 8</a></li>
          <li><a href="#section9">Series 9</a></li>
        </ul>
      </nav>

        <?php echo show_messages($message_list); ?>
    </div>
  </div>

</div>
</div><!-- /.container -->

<?php include_once 'partials/footers.php'; ?>

</body>
</html>
