<?php
   /*
   POST contacts DONE
   GET contacts => get al contacts using ownerlogin in token payload DONE
   GET contacts/{id, ownerlogin} DONE => ownerlogin for rolling no hacking prevention
   PUT contacts/{id} DONE 
   PUT contacts/status/{id} DONE => update status
   DELETE contacts/{id} DONE

   GET users/{login} get profile DONE
   PUT users/{login} profile update DONE
   PUT users/password/{login} reset password DONE
   */

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

   include_once("database_class.php");

   require_once 'vendor/autoload.php';

   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;

   use Ramsey\Uuid\Uuid;
   use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

   //load environment variable - jwt secret key
   $dotenv = new Dotenv\Dotenv(__DIR__);
   $dotenv->load();

   //jwt secret key in case dotenv not working in apache
   //$jwtSecretKey = "jwt_secret_key";

   use Slim\App;
   use Slim\Middleware\TokenAuthentication;
   use Firebase\JWT\JWT;

   //functions /////////////////////////////////////////////start

   function generateToken($role, $username, $email) {      

      //create JWT token
      $date = date_create();
      $jwtIAT = date_timestamp_get($date);
      $jwtExp = $jwtIAT + (180 * 60); //expire after 3 hours

      $jwtToken = array(
         "iss" => "rahsialah", //client key
         "iat" => $jwtIAT, //issued at time
         "exp" => $jwtExp, //expire
         "role" => $role,
         "username" => $username,
         "email" => $email
      );
      $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));
      return $token;
   }

   function generateTokenV2($role, $username, $email,$id) {      

      //create JWT token
      $date = date_create();
      $jwtIAT = date_timestamp_get($date);
      $jwtExp = $jwtIAT + (180 * 60); //expire after 3 hours

      $jwtToken = array(
         "iss" => "busticket", //client key
         "iat" => $jwtIAT, //issued at time
         "exp" => $jwtExp, //expire
         "role" => $role,
         "username" => $username,
         "email" => $email,
         "id"=> $id
      );
      $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));
      return $token;
   }

   function getDatabase() {
      $dbhost="localhost";
      $dbuser="root";
      $dbpass="";
      $dbname="busticket";

      $db = new Database($dbhost, $dbuser, $dbpass, $dbname);
      return $db;
   }

   function getLoginFromTokenPayload($request, $response) {
      $token_array = $request->getHeader('HTTP_AUTHORIZATION');
      $token = substr($token_array[0], 7);

      //decode the token
      try
      {
         $tokenDecoded = JWT::decode(
            $token, 
            getenv('JWT_SECRET'), 
            array('HS256')
         );

         //in case dotenv not working
         /*
         $tokenDecoded = JWT::decode(
            $token, 
            $GLOBALS['jwtSecretKey'], 
            array('HS256')
         );
         */
      }
      catch(Exception $e)
      {
         $data = Array(
            "message" => "Token invalid"
         ); 

         return $response->withJson($data, 401)
                         ->withHeader('Content-tye', 'application/json');
      }

      // return $tokenDecoded->login;
      return $tokenDecoded;

   }
   //functions /////////////////////////////////////////////ends

   $config = [
      'settings' => [
         'displayErrorDetails' => true
      ]
   ];

   $app = new App($config);

   /**
     * Token authentication middleware logic
     */
   $authenticator = function($request, TokenAuthentication $tokenAuth){

      /**
         * Try find authorization token via header, parameters, cookie or attribute
         * If token not found, return response with status 401 (unauthorized)
      */
      $token = $tokenAuth->findToken($request); //from header

      try {
         $tokenDecoded = JWT::decode($token, getenv('JWT_SECRET'), array('HS256'));

         //in case dotenv not working
         //$tokenDecoded = JWT::decode($token, $GLOBALS['jwtSecretKey'], array('HS256'));
      }
      catch(Exception $e) {
         throw new \app\UnauthorizedException('Invalid Token');
      }
   };

   /**
     * Add and manage token authentication middleware => $authenticator
     * passthrough means, no token needed, a public/guest route
     */
   $app->add(new TokenAuthentication([
        'path' => '/', //secure route - need token
        'passthrough' => [ //public route, no token needed
            '/ping', 
            '/token',
            '/auth',
            '/hello',
            '/calc',
            '/registration',
            '/booking',
            '/ticket'
         ], 
        'authenticator' => $authenticator
   ]));

// ==============EDIT START HERE===============

   $app->get('/booking', function(){
      $db = getDatabase();
      $data = $db->getAllBooking();
      $db->close();
      return $data;
   });

   $app->put('/booking/flipstatus/[{id}]', function($request, $response, $args){
      $id = $args['id'];
      $db = getDatabase();
      $dbs = $db->updateBooking($id);
      $db->close();
      // $data = Array(
      //    "updateStatus" => $dbs->status,
      //    "errorMessage" => $dbs->error
      // );

      // return $response->withJson($data, 200)
      //                 ->withHeader('Content-type', 'application/json');
      return $dbs;
   });

   /**
     * Public route /auth for creds authentication / login process
     */
    $app->post('/auth', function($request, $response){
      
      //extract form data - email and password
      $json = json_decode($request->getBody());
      $email = $json->email;
      $clearpassword = $json->password;

      //do db authentication
      $db = getDatabase();
      $data = $db->authenticateUser($email);
      $db->close();

      //status -1 -> user not found
      //status 0 -> wrong password
      //status 1 -> login success

      $returndata = array(
      );

      //user not found
      if ($data === NULL) {
         $returndata = array(
            "loginStatus" => false,
            "errorMessage" => "Username/password is incorrect!"
         );           
      }      
      else { //user found

         if (password_verify($clearpassword, $data->passwordhash)) {

            //create JWT token
            $date = date_create();
            $jwtIAT = date_timestamp_get($date);
            $jwtExp = $jwtIAT + (60 * 60 * 12); //expire after 12 hours

            $jwtToken = array(
               "iss" => "mycontacts.net", //token issuer
               "iat" => $jwtIAT, //issued at time
               "exp" => $jwtExp, //expire
               "role" => "member",
               "email" => $data->email,
               "username" => $data->username
            );
            $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));

            $returndata = array(
               "loginStatus" => true, 
               "token" => $token
            );

         } else {

            $returndata = array(
               "loginStatus" => false,
               "errorMessage" => "Username/password is incorrect!"
            );

         }
      }  

      return $response->withJson($returndata, 200)
                      ->withHeader('Content-type', 'application/json');    
   }); 
   // $app->post('/auth', function($request, $response){
   //    //extract form data - email and password
   //    // $email = $request->getParsedBody()['email'];
   //    // $password = $request->getParsedBody()['password'];
   //    $json = json_decode($request->getBody());
   //    $email = $json->email;
   //    $clearpassword = $json->password;

   //    //do db authentication
   //    $db = getDatabase();
   //    $data = $db->authenticateUser($email);


   //    //status -1 -> user not found
   //    //status 0 -> wrong password
   //    //status 1 -> login success

   //    $returndata = array(
   //    );

   //    //user not found
   //    if ($data === NULL) {
   //       $returndata = array(
   //          "loginStatus" => false,
   //          "errorMessage" => "No user found"
   //       );           
   //    }      
   //    else { //user found

   //       // if ($data->password == $password) {

   //       //    $token=generateTokenV2($data->role, $data->username, $data->email,$data->id);
   //       //    $db->updateCurrentToken($token,$email);
   //       //    $db->close();

   //       //    $returndata = array(
   //       //       "loginStatus" => true, 
   //       //       "token" => $token
   //       //    );

   //       // } else {

   //       //    $returndata = array(
   //       //       "loginStatus" => false,
   //       //       "errorMessage" => "Username/password is incorrect!"
   //       //    );

   //       // }
   //       if (password_verify($clearpassword, $data->passwordhash)) {

   //          //create JWT token
   //          $date = date_create();
   //          $jwtIAT = date_timestamp_get($date);
   //          $jwtExp = $jwtIAT + (60 * 60 * 12); //expire after 12 hours

   //          $jwtToken = array(
   //             "iss" => "mycontacts.net", //token issuer
   //             "iat" => $jwtIAT, //issued at time
   //             "exp" => $jwtExp, //expire
   //             "role" => "member",
   //             "email" => $data->email,
   //             "username" => $data->username
   //          );
   //          $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));

   //          $returndata = array(
   //             "loginStatus" => true, 
   //             "token" => $token
   //          );

   //       } else {

   //          $returndata = array(
   //             "loginStatus" => false,
   //             "errorMessage" => "Username/password is incorrect!"
   //          );

   //       }
   //    }  

   //    return $response->withJson($returndata, 200)
   //                    ->withHeader('Content-type', 'application/json');    
   // }); 


   $app->post('/createBooking', function($request, $response){
      $ticket_id= $request->getParsedBody()['ticket_id'];
      $user=getLoginFromTokenPayload($request, $response);
      $user_id= $user->id;

      $db = getDatabase();
      $status = $db->createBooking($ticket_id,$user_id);

      $returndata = array(
         "bookingStatus" => $status,
      );

      return $response->withJson($returndata, 200)
      ->withHeader('Content-type', 'application/json');    


  
   });

   $app->get('/ticket', function(){
      $db = getDatabase();
      $data = $db->getAllTicket();
      $db->close();
      return $data;
   });




// ==============EDIT END HERE===============

   /**
     * Public route example
     */
    $app->get('/ping', function($request, $response){
      $output = ['msg' => 'RESTful API works, active and online!'];
      return $response->withJson($output, 200, JSON_PRETTY_PRINT);
   });


   /**
     * Public route /registration for member registration
     */
   $app->post('/registration', function($request, $response){

      $json = json_decode($request->getBody());
      $email = $json->email;
      $clearpassword = $json->password;
      $username = $json->username;

      //insert user
      $db = getDatabase();
      $dbs = $db->insertUser($email, $clearpassword,$username);
      $db->close();

      $data = array(
         "registrationStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      ); 

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   });


   //restricted route
   //refresh token
   //if current token valid, extend token for another 15 minutes
   $app->post('/refreshtoken', function($request, $response) {

      $token_array = $request->getHeader('HTTP_AUTHORIZATION');
      $token = substr($token_array[0], 7);
      
      $decodedToken = new stdClass();
      $isValidToken = false;

      //we need to validate the token, for decoding it, no choice
      //double validation here
      //this is restricted route, so token validation happen in middleware
      try
      {
         $decodedToken = JWT::decode($token, getenv('JWT_SECRET'), array('HS256'));
      }
      catch(Exception $e)
      {
         $data = array(
            "message" => "Invalid Token"
         ); 

         return $response->withJson($data, 401)
                         ->withHeader('Content-type', 'application/json');
      }

      $role = $decodedToken->role;
      $login = $decodedToken->login;
      $name = $decodedToken->name;

      $token = generateToken($role, $login, $name);

      $data = array(
         "token" => $token,
         "isValidToken" => true
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-tye', 'application/json');

   });

   //contacts CRUD  ///////////////////////////////////////////////////////////////////  strart
   //
   //restricted route
   //POST - INSERT CONTACT - secure route - need token
   $app->post('/contacts', function($request, $response){

      $ownerlogin = getLoginFromTokenPayload($request, $response);

      //form data
      $json = json_decode($request->getBody());
      $name = $json->name;
      $email = $json->email;
      $mobileno = $json->mobileno;
      $gender = $json->gender;
      $dob = $json->dob;

      $db = getDatabase();
      $dbs = $db->insertContact($name, $email, $mobileno, $gender, $dob, $ownerlogin);
      $db->close();

      $data = array(
         "insertStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );


      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   });   

   //restricted route
   //- secure route - need token
   //GET - ALL CONTACTS using login in token payload as ownerlogin
   $app->get('/contacts', function($request, $response){

      $ownerlogin = getLoginFromTokenPayload($request, $response);

      $db = getDatabase();
      $data = $db->getAllContactsViaLogin($ownerlogin);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //restricted route
   //- secure route - need token
   //GET - SINGLE CONTACT VIA ID
   $app->get('/contacts/[{id}]', function($request, $response, $args){

      //get owner login - to prevent rolling no hacking, bcoz of insecure get method
      $ownerlogin = getLoginFromTokenPayload($request, $response);  
      
      $id = $args['id'];

      $db = getDatabase();
      $data = $db->getContactViaId($id, $ownerlogin);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   }); 

   //restricted route
   //- secure route - need token
   //PUT - UPDATE SINGLE CONTACT VIA ID
   $app->put('/contacts/[{id}]', function($request, $response, $args){
     
      //from url
      //rolling no hack not possible as extracting the data for update
      //is using ownerlogin
      $id = $args['id'];

      //form data using json structure
      $json = json_decode($request->getBody());
      $name = $json->name;
      $email = $json->email;
      $mobileno = $json->mobileno;
      $gender = $json->gender;
      $dob = $json->dob;

      $db = getDatabase();
      $dbs = $db->updateContactViaId($id, $name, $email, $mobileno, $gender, $dob);
      $db->close();

      $data = Array(
         "updateStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //restricted route
   //- secure route - need token
   //PUT - UPDATE CONTACT STATUS VIA ID
   $app->put('/contacts/status/[{id}]', function($request, $response, $args){
     
      //from url
      $id = $args['id'];

      //form data, from json data
      $json = json_decode($request->getBody());
      $status = $json->status;

      $db = getDatabase();

      if ($status)
         $status = 0;
      else
         $status = 1;

      $dbs = $db->updateContactStatusViaId($id, $status);
      $db->close();

      $data = Array(
         "updateStatus" => $dbs->status,
         "errorMessage" => $dbs->error,
         "status" => $status
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //restricted route
   //- secure route - need token
   //DELETE - SINGLE CONTACT VIA ID
   $app->delete('/contacts/[{id}]', function($request, $response, $args){

      $id = $args['id'];

      $db = getDatabase();
      $dbs = $db->deleteContactViaId($id);
      $db->close();

      $data = Array(
         "deleteStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');     
   });
   //
   //contacts CRUD  /////////////////////////////////////////////////////////////////////  ends


   //users CRUD  //////////////////////////////////////////////////////////////////////  starts

   //restricted route
   //- secure route - need token
   //GET - single user using login in token payload
   $app->get('/users', function($request, $response){

      $login = getLoginFromTokenPayload($request, $response);

      $db = getDatabase();
      $data = $db->getUserViaLogin($login);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //restricted route
   //- secure route - need token
   //PUT - UPDATE SINGLE user VIA login from token payload
   $app->put('/users', function($request, $response, $args){
     
      $login = getLoginFromTokenPayload($request, $response);

      //form data using json structure
      $json = json_decode($request->getBody());
      $name = $json->name;
      $email = $json->email;
      $mobileno = $json->mobileno;

      $db = getDatabase();
      $dbs = $db->updateUserViaLogin($login, $name, $email, $mobileno);
      $db->close();

      $data = Array(
         "updateStatus" => $dbs->status,
         "errorMessage" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //restricted route
   //- secure route - need token
   //PUT - UPDATE SINGLE user VIA login from token payload
   $app->put('/users/resetpassword', function($request, $response, $args){
     
      $login = getLoginFromTokenPayload($request, $response);

      //form data using json structure
      $json = json_decode($request->getBody());
      $oldpassword = $json->oldpassword;
      $clearpassword = $json->newpassword;

      $db = getDatabase();

      $passwordhash = $db->getUserPasswordViaLogin($login);

      //check the hash against the oldpassword
      if (password_verify($oldpassword, $passwordhash)) {

         //same, proceed for password reset/update         

         //update users table for new password
         $dbs = $db->updateUserPasswordViaLogin($login, $clearpassword);

         if ($dbs->status)
            $data = Array(
               "updateStatus" => true
            ); 
         else
            $data = Array(
               "updateStatus" => false,
               "errorMessage" => $dbs->error
            );      
         
         $db->close();

         return $response->withJson($data, 200)
                          ->withHeader('Content-type', 'application/json');   

      } else { //oldpassword not the same as the one in db

         $data = Array(
            "updateStatus" => false,
            "errorMessage" => "Old password is incorrect!"
         );
         
         $db->close();

         return $response->withJson($data, 200)
                          ->withHeader('Content-type', 'application/json'); 
      } 
   });
   //users CRUD  ////////////////////////////////////////////////////////////////////////  ends

   $app->run();