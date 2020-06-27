<?php
   ini_set("date.timezone", "Asia/Kuala_Lumpur");
   require_once('db.php');

   class Ticket {

        var $id;
        var $destfrom;
        var $destto;
        var $date;
        var $quantity;
        var $max;
        var $price;

   }

   try 
    {
        $selectAllTicket->execute();

        $row_count = $selectAllTicket->rowCount();

        if ($row_count)
            {
                $data = array();
                    
                while($row = $selectAllTicket->fetch(PDO::FETCH_ASSOC)) 
                {  
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
            }

        else
            {
            $data = array();
            echo json_encode($data);
            exit;
            }
      
    }
   catch(PDOException $e) 
   {
       die('ERROR: ' . $e->getMessage());
   } 