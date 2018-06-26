<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->get('/aduan/user/:user_id/:token','getAduan2'); /* Aduan  */
$app->get('/getAduanAll','getAduanAll'); /* Aduan  */
$app->post('/login','login'); /* User login */
$app->post('/signup','signup'); /* User Signup  */
$app->post('/getAduan','getAduan'); /* Aduan  */
$app->post('/createAduan','createAduan'); /* Aduan  */
$app->post('/deleteAduan','deleteAduan'); /*  Aduan  */
$app->post('/updateAduan','updateAduan'); /* Aduan  */
$app->post('/getImages', 'getImages');

$app->run();

/************************* USER LOGIN *************************************/
/* ### User login ### */
function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    try {
        
        $db = getDB();
        $userData ='';
        $sql = "SELECT user_id, name, email, username, role FROM users WHERE (username=:username or email=:username) and password=:password ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username, PDO::PARAM_STR);
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount=$stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!empty($userData))
        {
            $user_id=$userData->user_id;
            $userData->token = apiToken($user_id);
        }
        
        $db = null;
         if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Bad request wrong username and password"}}';
            }

           
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/* ### User registration ### */
function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;
    $name=$data->name;
    $username=$data->username;
    $password=$data->password;
    $role="user";

    try {
        
        $username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
        
        echo $email_check.'<br/>'.$email;
        
        if (strlen(trim($username))>0 && strlen(trim($password))>0 && strlen(trim($email))>0 && $email_check>0 && $username_check>0 && $password_check>0)
        {
            echo 'here';
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM users WHERE username=:username or email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("username", $username,PDO::PARAM_STR);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                
                /*Inserting user values*/
                $sql1="INSERT INTO users(username,password,email,name,role)VALUES(:username,:password,:email,:name,:role)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("username", $username,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->bindParam("name", $name,PDO::PARAM_STR);
                $stmt1->bindParam("role", $role,PDO::PARAM_STR);
                $stmt1->execute();
                
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
         

            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }

           
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function email() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;

    try {
       
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
       
        if (strlen(trim($email))>0 && $email_check>0)
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM emailUsers WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                
                /*Inserting user values*/
                $sql1="INSERT INTO emailUsers(email)VALUES(:email)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->execute();
                
                
            }
            $userData=internalEmailDetails($email);
            $db = null;
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid dataaaa"}}';
            }
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/* ### internal Username Details ### */
function internalUserDetails($input) {
    
    try {
        $db = getDB();
        $sql = "SELECT user_id, name, email, username FROM users WHERE username=:input or email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}

function getAduan2($user_id, $token) {

    $request = \Slim\Slim::getInstance()->request();
    //$user_id = $args['user_id'];
    //$user_id = $app->request->get('user');
    // $token = $args['token'];
    //$token = $app->request->get('token');
    $lastCreated = null;
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            if($lastCreated){
                $sql = "SELECT * FROM aduan WHERE user_id_fk=:user_id AND created < :lastCreated ORDER BY aduan_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam("lastCreated", $lastCreated, PDO::PARAM_STR);
            }
            else{
                $sql = "SELECT * FROM aduan WHERE user_id_fk=:user_id ORDER BY aduan_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}

/* ### Get all aduan ### */
function getAduanAll(){
    try {
         
        if(1){
            $feedData = '';
            $db = getDB();
          
                $sql = "SELECT * FROM aduan ORDER BY aduan_id DESC LIMIT 15";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam("lastCreated", $lastCreated, PDO::PARAM_STR);
          
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}

function getAduan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $lastCreated = $data->lastCreated;
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            if($lastCreated){
                $sql = "SELECT * FROM aduan WHERE user_id_fk=:user_id AND created < :lastCreated ORDER BY aduan_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam("lastCreated", $lastCreated, PDO::PARAM_STR);
            }
            else{
                $sql = "SELECT * FROM aduan WHERE user_id_fk=:user_id ORDER BY aduan_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}

function createAduan(){

    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $title=$data->title;
    $kategori=$data->kategori;
    $masalah=$data->masalah;
    $token=$data->token;
    $status="pending";
    
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
         
            
            $feedData = '';
            $db = getDB();
            $sql = "INSERT INTO aduan ( title, kategori, masalah, status, created, user_id_fk) VALUES (:title,:kategori,:masalah,:status,:created,:user_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("title", $title, PDO::PARAM_STR);
            $stmt->bindParam("kategori", $kategori, PDO::PARAM_STR);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam("masalah", $masalah, PDO::PARAM_STR);
            $stmt->bindParam("status", $status, PDO::PARAM_STR);
            $created = time();
            $stmt->bindParam("created", $created, PDO::PARAM_INT);
            $stmt->execute();
            


            $sql1 = "SELECT * FROM aduan WHERE user_id_fk=:user_id ORDER BY aduan_id DESC LIMIT 1";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt1->execute();
            $feedData = $stmt1->fetch(PDO::FETCH_OBJ);


            $db = null;
            echo '{"feedData": ' . json_encode($feedData) . '}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}

function deleteAduan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $aduan_id=$data->aduan_id;
    
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            $sql = "Delete FROM aduan WHERE user_id_fk=:user_id AND aduan_id=:aduan_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam("aduan_id", $aduan_id, PDO::PARAM_INT);
            $stmt->execute();
            
           
            $db = null;
            echo '{"success":{"text":"Aduan deleted"}}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }   
    
}

function getUserRole($user_id){
    try {
        $db = getDB();
        $sql = "SELECT role FROM users WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id,PDO::PARAM_STR);
        $stmt->execute();
        $userDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $role = $userDetails->role;
        $db = null;
        return $role;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateAduan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $aduan_id=$data->aduan_id;
    $status=$data->status;
    
    $systemToken=apiToken($user_id);

    $role = getUserRole($user_id);

    if($role=="admin"){

        try {
         
            if($systemToken == $token){
                $feedData = '';
                $db = getDB();
                $sql = "UPDATE aduan SET status=:status WHERE aduan_id=:aduan_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("aduan_id", $aduan_id, PDO::PARAM_INT);
                $stmt->bindParam("status", $status, PDO::PARAM_STR);
                $stmt->execute();
               
                $db = null;
                echo '{"success":{"text":"Aduan updated"}}';
            } else{
                echo '{"error":{"text":"No access"}}';
            }
           
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }   

    } else {
        echo '{"error":{"text":"User is not authorized"}}';
    }
}

$app->post('/userImage','userImage'); /* User Details */
function userImage(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $imageB64=$data->imageB64;
    $systemToken=apiToken($user_id);
    try {
        if(1){
            $db = getDB();
            $sql = "INSERT INTO imagesData(b64,user_id_fk) VALUES(:b64,:user_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam("b64", $imageB64, PDO::PARAM_STR);
            $stmt->execute();
            $db = null;
            echo '{"success":{"status":"uploaded"}}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

$app->post('/getImages', 'getImages');
function getImages(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    
    $systemToken=apiToken($user_id);
    try {
        if(1){
            $db = getDB();
            $sql = "SELECT b64 FROM imagesData";
            $stmt = $db->prepare($sql);
           
            $stmt->execute();
            $imageData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"imageData": ' . json_encode($imageData) . '}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
?>