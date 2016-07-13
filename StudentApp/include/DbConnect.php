<?php 
	
	class DbConnect 
	{
		private $con;

		function __construct() 
		{

		}

		function connect() 
		{
			include_once dirname(__FILE__) . '/Constants.php';

			$this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME); // All in constants

			// Check possible error on connection
			if (mysqli.connect.errno()) {
				echo "Failed to connect to MySQL: " . mysqli.connect.error();
			}

			// Return the connection link
			return $this-con; 
		}
	}

?>