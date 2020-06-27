<?php
	function dbStart($address, $login, $password) 
	{
		try 
		{
			$db = new PDO($address, $login, $password);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} 
		catch(PDOException $e) 
		{
	    	echo 'ERROR: ' . $e->getMessage();
		}	
		
		return $db;
	}

	function prepareDbStatement( $db,
								&$selectAllTicket,
								&$selectTicketviaId)

	{
		try 
		{	
			$selectAllTicket = $db->prepare("SELECT * FROM tickets");

			$selectTicketviaId = $db->prepare("SELECT * FROM tickets WHERE id = :id");

			$updateTicket = $db->prepare("	UPDATE 	tickets
											SET 	destfrom 	= :destfrom,
													destto 		= :destto,
													date 		= :date,
													max 		= :max,
													price 		= :price
											WHERE 	id = :id");

			$deleteTicket = $db->prepare("DELETE FROM tickets WHERE id = :id"); 

		} 
		catch(PDOException $e) 
		{
	    	echo 'ERROR: ' . $e->getMessage();	
		}	
	}

	$address = 'mysql:host=localhost;dbname=busticket;charset=utf8';
	$login = "root";
	$password = "";
	$db = null;
	$db = dbStart($address, 
	              $login, 
				  $password);
				  
	$selectAllTicket = null;
	$selectTicketviaId = null;
	$updateTicket = null;
	$deleteTicket = null;
	
					

	prepareDbStatement( $db,
						$selectAllTicket,
						$selectTicketviaId,
						$updateTicket,
						$deleteTicket);