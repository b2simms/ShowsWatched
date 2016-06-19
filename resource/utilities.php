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
                $episodes .= addBackgroundTintClass($the_episode, true);
            }else{
                $episodes .= addBackgroundTintClass($the_episode, false);
            }

            if($the_episode['status'] === '0'){
                $episodes .= movePillRight(true);
            }else{
                $episodes .= movePillRight(false);
            }

            if($the_episode['status'] === '2'){
                $episodes .= setPill('success">Watched');
            }else if($the_episode['status'] === '1'){
                $episodes .= setPill('warning">Claimed by '."{$the_episode['assigned_name']}");
            }else if($the_episode['status'] === '0'){
                $episodes .= setPill('info">To Watch');
            }

            //set buttons
            if(isAuthorizedUser() && $the_episode['status'] === '0'){
                $episodes .= addForm($the_episode,'warning" name="claimBtn" >Claim Episode');
            }else if(isAuthorizedUser() && $the_episode['status'] === '1' && strcasecmp($the_episode['assigned_name'], $_SESSION['username']) == 0 ){
                $episodes .= addForm($the_episode,'danger" name="unclaimBtn" >Unclaim Episode');
            }
             if(isAdminUser() && $the_episode['status'] === '2'){
                $episodes .= addForm($the_episode,'danger" name="unwatchBtn" >Unwatch');
            }else if(isAdminUser()){
                $episodes .= addForm($the_episode,'info" name="watchBtn" >Watch');
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

function addForm($the_episode, $customHTML){

    $number = $the_episode['id'];

    if($number != '1'){
        $number = $the_episode['id'] - 1;
    }

    $form = '<form action="index.php#episode';
    $form .= "{$number}".'" method="post">';
    $form .= '<input type="hidden" name="id" value="';
    $form .= "{$the_episode['id']}".'">';
    $form .= '<input type="hidden" name="status" value="';
    $form .= "{$the_episode['status']}".'">';
    $form .= '<button type="submit" class="btn btn-sm btn-'.$customHTML.'</button>';
    $form .= '</form>';

    return $form;
}
function addBackgroundTintClass($the_episode, $addClassBoolean){

    $block = '<blockquote class="blockquote '; 
    if($addClassBoolean){
        $block .= "watched";    }
    $block .= '" id="episode'."{$the_episode['id']}".'">';

    return $block;
}

function movePillRight($movePillBoolean){
    $pill = '<p class="m-b-0'; 
    if($movePillBoolean){
        $pill .= " pill-label";    }
    $pill .= '">';

    return $pill;
}

function setPill($customHTML){
    return '<span class="label label-'.$customHTML.'</span>';
}

