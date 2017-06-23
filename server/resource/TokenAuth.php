<?php

use \Firebase\JWT\JWT;
 
class TokenAuth
{
    protected $key;

    public function __construct($key) {
        //Define the urls that you want to exclude from Authentication, aka public urls     
        $this->whiteList = array('\/auth\/login', '\/auth\/register','\/test');
        $this->key = $key;
    }

    public function __invoke($request, $response, $next)
    {
        //check if public
        $output = new stdClass();
        if($request->isOptions() || $this->isPublicUrl($request->getUri())){
            $response = $next($request, $response);
        }else{
            //is authenticated?
            try{
                $tokenArr = $request->getHeader('Authorization');
                if (!empty($tokenArr) && $this->authenticate(str_replace("Bearer ","", $tokenArr[0]))) {
                    $jwt = str_replace("Bearer ","", $tokenArr[0]);
                    //Get the user and make it available for the controller
                    $decoded = JWT::decode($jwt, $this->key, array('HS256'));

                    //get name, user_id, role
                    $nr1 = $request->withAttribute('name',$decoded->name);
                    $nr2 = $nr1->withAttribute('user_id',$decoded->user_id);
                    $newrequest3 = $nr2->withAttribute('role',$decoded->role);

                    $response = $next($newrequest3, $response);
                }else{
                    $output->message = "Unauthorized";
                    $response = $response->withStatus(401);
                    $myJson = json_encode($output);
                    $response->getBody()->write($myJson);
                }
            }catch(Exception $e){
                echo 'Message: ' .$e->getMessage();
                $output->message = "Unauthorized";
                $response = $response->withStatus(401);
                $myJson = json_encode($output);
                $response->getBody()->write($myJson);
            }
        }
        return $response;
    }
 
    /**
     * Check against the DB if the token is valid
     * 
     * @param string $token
     * @return bool
     */
    public function authenticate($jwt) {
        try{
            JWT::decode($jwt, $this->key, array('HS256'));
        }catch(Exception $e){
            return false;
        }
        return true;
    }
 
    /**
     * This function will compare the provided url against the whitelist and
     * return wether the $url is public or not
     * 
     * @param string $url
     * @return bool
     */
    public function isPublicUrl($url) {
        $patterns_flattened = implode('|', $this->whiteList);
        $matches = null;
        preg_match('/' . $patterns_flattened . '/', $url, $matches);
        return (count($matches) > 0);
    }
 
}

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
           $headers = []; 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
}