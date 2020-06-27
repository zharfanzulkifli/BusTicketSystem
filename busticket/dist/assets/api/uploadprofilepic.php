<?php
   ini_set("date.timezone", "Asia/Kuala_Lumpur");

   header('Access-Control-Allow-Origin: *');   

   //*
   // Allow from any origin
   if (isset($_SERVER['HTTP_ORIGIN'])) {
      // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
      // you want to allow, and if so:
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
   }

   // Access-Control headers are received during OPTIONS requests
   if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
         header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");         

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
         header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
   }
   //*/

   require_once 'vendor/autoload.php';
   include_once("database_class.php");

   use Firebase\JWT\JWT;

   function getDatabase() {
      $dbhost="127.0.0.1";
      $dbuser="root";
      $dbpass="";
      $dbname="contacts";

      $db = new Database($dbhost, $dbuser, $dbpass, $dbname);
      return $db;
   }

   function getTokenFromHeader() {
      $headers = apache_request_headers();

      $token = "";
      foreach ($headers as $header => $value) { 

         if (strcmp($header, "Authorization") == 0) {       
            
            $bearertoken = $value;
            $token = substr($bearertoken, 7);
         }

      } 
      
      //token not exist
      if (strcmp($token, "") == 0) {
         header("HTTP/1.1 401 Unauthorized");
         header('Content-Type: application/json');
         echo json_encode(array(
            "message" => "Token unavailable"
         ));
         exit;
      }  

      return $token;     
   }

   //load environment variable - jwt secret key
   $dotenv = new Dotenv\Dotenv(__DIR__);
   $dotenv->load();

   //get token from header and validate
   $token = getTokenFromHeader();

   //token exist now validate
   try
   {
      $tokenDecoded = JWT::decode(
         $token, 
         getenv('JWT_SECRET'),  
         array('HS256')
      );
   }
   catch(Exception $e)
   {
      header("HTTP/1.1 401 Unauthorized");
      header('Content-Type: application/json');
      echo json_encode(array(
         "message" => "Token invalid"
      ));
      exit;
   } 

 	$id = $_POST["id"];

	$target_dir = "../img/";

	$uniqid = uniqid();
	$filename = $id . "_" . $uniqid . "_" . basename($_FILES["fileToUpload"]["name"]);
	$target_file = $target_dir . $filename;


	$uploadStatus = false;
   $dbUpdateStatus = false;

	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      $uploadStatus = true;

      //save the image name to db here
      try 
      {
         $db = getDatabase();
         $dbs = $db->updateProfilePhoto($filename, $id);
      }
      catch(PDOException $e) 
      {
         $errorMessage = $e->getMessage();
         $data = Array(
            "uploadstatus" => false,
            "errorMessage" => $errorMessage
         ); 
         echo json_encode($data);
         exit;
      }      

   } else {
      $uploadStatus = false;
   }   
	 
	if ($uploadStatus) {
		$info = Array(
			"uploadstatus" => $uploadStatus,
			"photofilename" => $filename,
		);
	} else {
		$info = Array(
			"uploadstatus" => $uploadStatus,
		);		
	}

	echo json_encode($info);