<?php
class Booking
{
   var $id;
   var $ticketid;
   var $userid;
   var $status;
}

Class UsersV2
{
   var $id;
   var $username;
   var $password;
   var $email;
   var $role;
}

class Ticket {

   var $id;
   var $destfrom;
   var $destto;
   var $date;
   var $quantity;
   var $max;
   var $price;

}

class DbStatus
{
   var $status;
   var $error;
   var $lastinsertid;
}

function time_elapsed_string($datetime, $full = false)
{

   if ($datetime == '0000-00-00 00:00:00')
      return "none";

   if ($datetime == '0000-00-00')
      return "none";

   $now = new DateTime;
   $ago = new DateTime($datetime);
   $diff = $now->diff($ago);

   $diff->w = floor($diff->d / 7);
   $diff->d -= $diff->w * 7;

   $string = array(
      'y' => 'year',
      'm' => 'month',
      'w' => 'week',
      'd' => 'day',
      'h' => 'hour',
      'i' => 'minute',
      's' => 'second',
   );

   foreach ($string as $k => &$v) {
      if ($diff->$k) {
         $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
         unset($string[$k]);
      }
   }

   if (!$full) $string = array_slice($string, 0, 1);
   return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function hashPassword($password)
{

   $cost = 10;

   $options = [
      'cost' => $cost,
   ];

   $passwordhash =  password_hash($password, PASSWORD_BCRYPT, $options);
   return $passwordhash;
}

class Database
{
   protected $dbhost;
   protected $dbuser;
   protected $dbpass;
   protected $dbname;
   protected $db;

   function __construct($dbhost, $dbuser, $dbpass, $dbname)
   {
      $this->dbhost = $dbhost;
      $this->dbuser = $dbuser;
      $this->dbpass = $dbpass;
      $this->dbname = $dbname;

      $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
      $this->db = $db;
   }

   function beginTransaction()
   {
      try {
         $this->db->beginTransaction();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function commit()
   {
      try {
         $this->db->commit();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function rollback()
   {
      try {
         $this->db->rollback();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function close()
   {
      try {
         $this->db = null;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   // =============START EDIT FUNCTIONS HERE==============//

   function getAllBooking()
   {
      try {
         $sql = "SELECT * FROM bookings";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $booking = new Booking();
               $booking->id = $row['id'];
               $booking->ticketid = $row['ticketid'];
               $booking->userid = $row['userid'];
               $booking->status = $row['status'];

               array_push($data, $booking);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function updateBooking($id)
   {
      try {
         $sql = "UPDATE bookings SET status = not status WHERE id = :id";
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute();
         $sql = "SELECT * FROM bookings";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();

         // $dbs = new DbStatus();
         // $dbs->status = true;
         // $dbs->error = "none";
         // return $dbs;
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $booking = new Booking();
               $booking->id = $row['id'];
               $booking->ticketid = $row['ticketid'];
               $booking->userid = $row['userid'];
               $booking->status = $row['status'];

               array_push($data, $booking);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }

      } catch (PDOException $e) {
         // die('ERROR: ' . $e->getMessage());
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   
   function authenticateUser($email)
   {
      $sql = "SELECT * from users
                 WHERE email = :email";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("email", $email);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $user = null;

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new UsersV2();
            $user->id= $row['id'];
            $user->username = $row['username'];
            $user->password = $row['password'];
            $user->email = $row['email'];
            $user->role = $row['role'];
         }
      }

      return $user;
   }

   function updateCurrentToken($token,$email){

      $sql = "UPDATE users SET ownerlogin = :token WHERE email = :email";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("token", $token);
      $stmt->bindParam("email", $email);
      $stmt->execute();

   }

   function createBooking($ticket_id, $user_id){

      
      $sql = "SELECT * from tickets
                 WHERE id = :ticketid";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("ticketid", $ticket_id);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ticket = new Ticket();
            $ticket->quantity= $row['quantity'];
            $ticket->max = $row['max'];

         }

         $quantity=$ticket->quantity;
         $max=$ticket->max;
         if($quantity < $max){

            $sql = "INSERT INTO bookings (ticketid, userid)
            VALUES (:ticketid, :userid)";      
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("ticketid", $ticket_id);
            $stmt->bindParam("userid", $user_id);
            $stmt->execute();

            $new_quantity=$quantity + 1;

            $sql = "UPDATE tickets SET quantity = :quantity WHERE id = :ticketid";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("quantity", $new_quantity);
            $stmt->bindParam("ticketid", $ticket_id);
            $stmt->execute();

            $status="Success";

         }
         else{
            $status="Full";
         }
         

      }
      return $status;

   }

   function getAllTicket()
   {
      try {
         $sql = "SELECT * FROM tickets";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $ticket = new Ticket();
               $ticket->id = $row['id'];
               $ticket->destfrom = $row['destfrom'];
               $ticket->destto = $row['destto'];
               $ticket->date = $row['date'];
               $ticket->quantity = $row['quantity'];
               $ticket->max = $row['max'];
               $ticket->price = $row['price'];

               array_push($data, $ticket);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }














   // =============EDIT FUNCTION END HERE==============//

   function insertUser($login, $clearpassword)
   {

      //hash the password using one way md5 brcrypt hashing
      $passwordhash = hashPassword($clearpassword);
      try {

         $sql = "INSERT INTO users(login, password, addeddate) 
                    VALUES (:login, :password, NOW())";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("login", $login);
         $stmt->bindParam("password", $passwordhash);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";
         $dbs->lastinsertid = $this->db->lastInsertId();

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   function checkemail($email)
   {
      $sql = "SELECT *
                 FROM users
                 WHERE email = :email";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("email", $email);
      $stmt->execute();
      $row_count = $stmt->rowCount();
      return $row_count;
   }



   /////////////////////////////////////////////////////////////////////////////////// contacts

   // insert contact
   function insertContact($name, $email, $mobileno, $gender, $dob, $ownerlogin)
   {

      try {

         $sql = "INSERT INTO contacts(name, email, mobileno, gender, dob, ownerlogin, addeddate) 
                    VALUES (:name, :email, :mobileno, :gender, :dob, :ownerlogin, NOW())";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("name", $name);
         $stmt->bindParam("email", $email);
         $stmt->bindParam("mobileno", $mobileno);
         $stmt->bindParam("gender", $gender);
         $stmt->bindParam("dob", $dob);
         $stmt->bindParam("ownerlogin", $ownerlogin);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";
         $dbs->lastinsertid = $this->db->lastInsertId();

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   //get all contacts
   function getAllContactsViaLogin($ownerlogin)
   {
      $sql = "SELECT *
                 FROM contacts
                 WHERE ownerlogin = :ownerlogin";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("ownerlogin", $ownerlogin);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $data = array();

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact();
            $contact->id = $row['id'];
            $contact->name = $row['name'];
            $contact->email = $row['email'];
            $contact->mobileno = $row['mobileno'];
            $contact->gender = $row['gender'];
            $contact->photo = $row['photo'];

            $dob = $row['dob'];
            $frontenddob = date("d-m-Y", strtotime($dob));
            $contact->dob = $frontenddob;

            $addeddate = $row['addeddate'];
            $contact->addeddate = time_elapsed_string($addeddate);

            $contact->status = $row['status'];

            array_push($data, $contact);
         }
      }

      return $data;
   }

   //get single contact via id
   //ownerlogin for rolling no hacking (the id)
   function getContactViaId($id, $ownerlogin)
   {
      $sql = "SELECT *
                 FROM contacts
                 WHERE id = :id
                 AND ownerlogin = :ownerlogin";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("id", $id);
      $stmt->bindParam("ownerlogin", $ownerlogin);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contact = new Contact();

            $contact->id = $row['id'];

            $contact->name = $row['name'];
            $contact->email = $row['email'];
            $contact->mobileno = $row['mobileno'];
            $contact->gender = $row['gender'];
            $contact->photo = $row['photo'];

            $dob = $row['dob'];
            $frontenddob = date("d-m-Y", strtotime($dob));
            $contact->dob = $frontenddob;

            $addeddate = $row['addeddate'];
            $contact->addeddate = time_elapsed_string($addeddate);

            $contact->status = $row['status'];
         }
      } else {
         //return empty array
         $contact = array();
      }

      return $contact;
   }

   //update contact via id
   function updateContactViaId($id, $name, $email, $mobileno, $gender, $dob)
   {

      $sql = "UPDATE contacts
                 SET name = :name,
                     email = :email,
                     mobileno = :mobileno,
                     gender = :gender,
                     dob = :dob
                 WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->bindParam("name", $name);
         $stmt->bindParam("email", $email);
         $stmt->bindParam("mobileno", $mobileno);
         $stmt->bindParam("gender", $gender);
         $stmt->bindParam("dob", $dob);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   //update contact status via id
   function updateContactStatusViaId($id, $status)
   {

      $sql = "UPDATE contacts
                 SET status = :status
                 WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->bindParam("status", $status);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   //delete contact via id
   function deleteContactViaId($id)
   {

      $dbstatus = new DbStatus();

      $sql = "DELETE 
                 FROM contacts 
                 WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute();

         $dbstatus->status = true;
         $dbstatus->error = "none";
         return $dbstatus;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbstatus->status = false;
         $dbstatus->error = $errorMessage;
         return $dbstatus;
      }
   }

   //get single user via login
   function getUserViaLogin($login)
   {
      $sql = "SELECT *
                 FROM users
                 WHERE login = :login";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("login", $login);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $user = new User();

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user->id = $row['id'];
            $user->login = $row['login'];
            $user->name = $row['name'];
            $user->email = $row['email'];
            $user->mobileno = $row['mobileno'];
            $user->photo = $row['photo'];

            $addeddate = $row['addeddate'];
            $user->addeddate = time_elapsed_string($addeddate);
         }
      }

      return $user;
   }

   //update user via login
   function updateUserViaLogin($login, $name, $email, $mobileno)
   {

      $sql = "UPDATE users
                 SET name = :name,
                     email = :email,
                     mobileno = :mobileno
                 WHERE login = :login";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("login", $login);
         $stmt->bindParam("name", $name);
         $stmt->bindParam("email", $email);
         $stmt->bindParam("mobileno", $mobileno);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   function getUserPasswordViaLogin($login)
   {

      $sql = "SELECT password
                 FROM users
                 WHERE login = :login";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("login", $login);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $password = "";

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $password = $row['password'];
         }
      }

      return $password;
   }

   //update user password via login
   function updateUserPasswordViaLogin($login, $clearpassword)
   {

      //hash the new password using one way md5 brcrypt encrypted hashing
      $passwordhash = hashPassword($clearpassword);

      $sql = "UPDATE users
                 SET password = :password
                 WHERE login = :login";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("login", $login);
         $stmt->bindParam("password", $passwordhash);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   //update profile photo
   function updateProfilePhoto($photo, $id)
   {

      $sql = "UPDATE contacts
                 SET photo = :photo
                 WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->bindParam("photo", $photo);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }
}
