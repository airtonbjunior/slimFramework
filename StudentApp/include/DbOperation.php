<?php
	
	class DbOperation 
	{
		private $con;

		function __construct () 
		{
			require_once dirname(__FILE__) . '/DbConnect.php';

			// Instance of DbConnect 
			$db = new DbConnect();

			// Call the connect() method in DbConnect
			$this->con = $db->connect();
		}


		public function createStudent ($name, $username, $pass) 
		{
			
			// Check if the student already exists
			if(!$this->isStudentExists($username)) {
				
				// get the user pass
				$password = md5($pass);

				// get the user apiKey
				$apiKey = $this->generateApiKey();

				// Create the statement
				$stmt = $this->con->prepare("INSERT INTO students (name, username, password, api_key) values (?, ?, ?, ?)");

				// 's' means string
				$stmt->bind_param("ssss", $name, $username, $password, $apiKey);

				$result = $stmt->execute();

				$stmt->close();

				if ($result) {
					// Success
					return 0;
				}
				else {
					// Fail
					return 1;
				}
			}
			else {
				// user already exists 
				return 2;
			}
		}

		public function studentLogin ($username, $pass) 
		{
			$password = md5($pass);

			// Prepare statement
			$stmt = $this->con->prepare("SELECT * FROM students WHERE username=? and password=?");
			// Pass the parameters ('s' means string)
			$stmt->bind_param("ss", $username, $password);

			// Execute the statement (query)
			$stmt->execute();

			// Store the result
			$stmt->store_result();

			$num_rows = $stmt->num_rows;

			$stmt->close();


			return $num_rows > 0;
		}

		public function getStudent ($username) 
		{
			$stmt = $this->con->prepare("SELECT * FROM students WHERE username=?");
			$stmt->bind_param("s", $username);
			$stmt->execute();

			$student = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			return $student;
		}

		private function isStudentExists ($username) 
		{
			$stmt = $this->con->prepare("SELECT * FROM students WHERE username = ?");
			$stmt->bind_param('s', $username);
			$stmt->execute();

			$stmt->store_result();

			$num_rows = $stmt->num_rows;
			$stmt->close();

			return $num_rows > 0;
		}

		private function getAssignments ($id) 
		{
	        $stmt = $this->con->prepare("SELECT * FFROM assignments WHERE students_id=?");
	        $stmt->bind_param("i",$id);
	        $stmt->execute();
	        $assignments = $stmt->get_result()->fetch_assoc();
	        return $assignments;
		}

		private function isValidStudent ($api_key) 
		{
			$stmt = $this->con->prepare("SELECT id from students WHERE api_key = ?");
			$stmt->bind_param("s", $api_key);
			$stmt->execute();
       		$stmt->store_result();

        	$num_rows = $stmt->num_rows;
        	$stmt->close();
        	
        	return $num_rows > 0;
		}

		private function generateApiKey () 
		{
			return md5(uniqid(rand(), true));
		}
	}


?>