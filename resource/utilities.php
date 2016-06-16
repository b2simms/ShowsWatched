<?php
/**
 * @param $user_id
 */
function rememberMe($user_id){
    $encryptCookieData = base64_encode("UaQteh5i4y3dntstemYODEC{$user_id}");
    // Cookie set to expire in about 30 days
    setcookie("rememberUserCookie",$encryptCookieData, time()+60*60*24*100, "/");
}

function isAdminUser(){
    if(isAuthorizedUser() && $_SESSION['username'] === "admin"){
        return true;
    }
    return false;
}

function isAuthorizedUser(){
    if(isset($_SESSION['username'])){
        return true;
    }
    return false;
}

/**
 * @param $required_fields_array, n array containing the list of all required fields
 * @return array, containing all errors
 */
function check_empty_fields($required_fields_array){
    //initialize an array to store error messages
    $form_errors = array();

    //loop through the required fields array snd popular the form error array
    foreach($required_fields_array as $name_of_field){
        if(!isset($_POST[$name_of_field]) || $_POST[$name_of_field] == NULL){
            $form_errors[] = $name_of_field . " is a required field";
        }
    }

    return $form_errors;
}

/**
 * @param $fields_to_check_length, an array containing the name of fields
 * for which we want to check min required length e.g array('username' => 4, 'email' => 12)
 * @return array, containing all errors
 */
function check_min_length($fields_to_check_length){
    //initialize an array to store error messages
    $form_errors = array();

    foreach($fields_to_check_length as $name_of_field => $minimum_length_required){
        if(strlen(trim($_POST[$name_of_field])) < $minimum_length_required && $_POST[$name_of_field] != NULL){
            $form_errors[] = $name_of_field . " is too short, must be {$minimum_length_required} characters long";
        }
    }
    return $form_errors;
}

/**
 * @param $data, store a key/value pair array where key is the name of the form control
 * in this case 'email' and value is the input entered by the user
 * @return array, containing email error
 */
function check_email($data){
    //initialize an array to store error messages
    $form_errors = array();
    $key = 'email';
    //check if the key email exist in data array
    if(array_key_exists($key, $data)){

        //check if the email field has a value
        if($_POST[$key] != null){

            // Remove all illegal characters from email
            $key = filter_var($key, FILTER_SANITIZE_EMAIL);

            //check if input is a valid email address
            if(filter_var($_POST[$key], FILTER_VALIDATE_EMAIL) === false){
                $form_errors[] = $key . " is not a valid email address";
            }
        }
    }
    return $form_errors;
}

/**
 * @param $form_errors_array, the array holding all
 * errors which we want to loop through
 * @return string, list containing all error messages
 */
function show_errors($form_errors_array){
    $errors = "<p><ul style='color: red;'>";

    //loop through error array and display all items in a list
    foreach($form_errors_array as $the_error){
        $errors .= "<li> {$the_error} </li>";
    }
    $errors .= "</ul></p>";
    return $errors;
}

function flashMessage($message, $passOrFail = "Fail"){
    if($passOrFail === "Pass"){
        $data = "<div class='alert alert-success'> {$message} </p>";
    }else{
         $data = "<div class='alert alert-danger'> {$message} </p>";
    }

    return $data;
}

function redirectTo($page){
    header("Location: {$page}.php");
}

function checkDuplicateEntries($table, $column_name, $value, $db){
    try{
        $sqlQuery = "SELECT * FROM {$table} where {$column_name}=:{$column_name}";
        $statement = $db->prepare($sqlQuery);
        $statement->execute(array(":{$column_name}" => $value));

        if($row = $statement->fetch()){
            return true;
        }
        return false;
    }catch(PDOException $ex){
        //handle exception
    }
   
}

/**
 * @param $form_episode_array, the array holding all
 * episodes which we want to loop through
 * @return string, list containing all episodes
 */
function show_episodes($form_episode_array){
    $episodes = '';

    $series = 0;

    //loop through error array and display all items in a list
    foreach($form_episode_array as $the_episode){

            //set series header
            if($series != $the_episode['season']){
                $series++;
                $episodes .= '<blockquote class="blockquote series-header" id="section'.$series.'"><p class="m-b-0">';
                $episodes .= 'Series '.$series.'</p></blockquote>';
            }

            if($the_episode['status'] === '2'){
                $episodes .= '<blockquote class="blockquote watched" id="episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
            }else{
                $episodes .= '<blockquote class="blockquote" id="episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
            }

            if($the_episode['status'] === '0'){
                $episodes .= '<p class="m-b-0 pill-label">';
            }else{
                $episodes .= '<p class="m-b-0">';
            }

            if($the_episode['status'] === '2'){
                $episodes .= '<span class="label label-success">Watched</span>';
            }else if($the_episode['status'] === '1'){
                $episodes .= '<span class="label label-warning">Claimed by ';
                $episodes .= "{$the_episode['assigned_name']}";
                $episodes .= '</span>';
            }else if($the_episode['status'] === '0'){
                $episodes .= '<span class="label label-info">To Watch</span>';
            }

            //set buttons
            if(isAuthorizedUser() && $the_episode['status'] === '0'){
                $episodes .= '<form action="index.php#episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '" method="post">';
                $episodes .= '<input type="hidden" name="id" value="';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
                $episodes .= '<button name="claimBtn" type="submit" class="btn btn-warning btn-sm">Claim Episode</button>';
                $episodes .= '</form>';
            }else if(isAuthorizedUser() && $the_episode['status'] === '1' && strcasecmp($the_episode['assigned_name'], $_SESSION['username']) == 0 ){
                $episodes .= '<form action="index.php#episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '" method="post">';
                $episodes .= '<input type="hidden" name="id" value="';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
                $episodes .= '<button name="unclaimBtn" type="submit" class="btn btn-danger btn-sm">Unclaim Episode</button>';
                $episodes .= '</form>';
            }
             if(isAdminUser() && $the_episode['status'] === '2'){
                $episodes .= '<form action="index.php#episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '" method="post">';$episodes .= '<form action="" method="post">';
                $episodes .= '<input type="hidden" name="id" value="';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
                $episodes .= '<button name="unwatchBtn" type="submit" class="btn btn-danger btn-sm">Unwatch</button>';
                $episodes .= '</form>';
            }else if(isAdminUser()){
                $episodes .= '<form action="index.php#episode';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '" method="post">';
                $episodes .= '<input type="hidden" name="id" value="';
                $episodes .= "{$the_episode['id']}";
                $episodes .= '">';
                $episodes .= '<button name="watchBtn" type="submit" class="btn btn-info btn-sm">Watch Episode</button>';
                $episodes .= '</form>';
            }

            $episodes .= '</p><footer class="blockquote-footer">';
            $episodes .= '<cite title="Source Title"> Episode ';
            $episodes .= "{$the_episode['episode']} </cite>";
            $episodes .= '</footer>';
            $episodes .= "{$the_episode['name']}";
            $episodes .= '</blockquote>';

        }
    
    return $episodes;
}
