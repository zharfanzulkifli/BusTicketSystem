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

	function prepareDbStatement($db,
								&$selectAllBooking
							)
	{
		try
		{
			$selectAllBooking = $db->prepare("SELECT * FROM bookings");
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

	$selectAllBooking = null;

	prepareDbStatement($db, $selectAllBooking);