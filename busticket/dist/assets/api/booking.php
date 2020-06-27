<?php
   ini_set("date.timezone", "Asia/Kuala_Lumpur");
   require_once('db.php');

   class Booking {
   	var $id;
   	var $ticketid;
   	var $userid;
   	var $status;
   }

   try {
  	$selectAllBooking->execute();

  	$row_count = $selectAllBooking->rowCount();

  	if ($row_count)
     {
        $data = array();

        while($row = $selectAllBooking->fetch(PDO::FETCH_ASSOC))
        {
           $booking = new Booking();
           $booking->id = $row['id'];
           $booking->ticketid = $row['ticketid'];
           $booking->userid = $row['userid'];
           $booking->status = $row['status'];

           array_push($data, $booking);
        }

        echo json_encode($data);
        exit;
     }
     else
     {
        $data = array();
        echo json_encode($data);
        exit;
     }
   }
   catch(PDOException $e) {
       die('ERROR: ' . $e->getMessage());
   }
