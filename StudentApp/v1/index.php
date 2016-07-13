<?php
	
	require_once '../include/DbOperation.php';
	require 'vendor/autoload.php';


	//\Slim\Slim::registerAutoloader();

	$app = new Slim\App();

	function echoRespose ($status_code, $response) 
	{
		// Get app instance
		$app = \Slim\Slim::getInstance();

		// setting status code
		$app->status($status_code);

		// setting response type
		$app->contentType('application/json');

		// display the response in json
		echo json_encode($response);
	}

	function verifyRequiredParams ($required_fields) 
	{
		$error = false;

		$error_fields = "";

    	//Getting the request parameters
		$request_params = $_REQUEST;

		if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
			
			$app = \Slim\Slim::getInstance();

			//Getting put parameters in request params variable
			parse_str($app->request()->getBody(), $request_params);
		}

		// Loop through all the parameters
		foreach ($required_fields as $field) {

			// If any parameter is missing
			if(!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
				$error = true;

				// Concatenate the fields that contain errors
				$error_fields .= $field . ', ';
			}
		}

		if ($error) {
			$response = array();
			$app = \Slim\Slim::getInstance();

        	//Adding values to response array
			$response["error"] = true;
			$response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';

			echoRespose(400, $response);

			$app->stop();
		}
	}

	function authenticateStudent (\Slim\Route $route) 
	{
		$headers = apache_request_headers();
		$response = array();

		$app = \Slim\Slim::getInstance();

		// Verifying the headers
		if (isset($headers['Authorization'])) {

			// DatabaseOperation objetct
			$db = new DbOperation();	

			// get api key from header 
			$api_key = $headers['Authorization'];

			if (!$db->isValidStudent($api_key)) {
				$response['error'] = true;
				$response['message'] = "Access Denied. Invalid Api key";
				echoRespose(401, $response); //echo response converts to json using json_enconde

				$app->stop();
			}
		}
		else {
			// api key is missing in header
			$response["error"] = true;
        	$response["message"] = "Api key is misssing";
        	echoResponse(400, $response);
        	$app->stop();
		}
	}

	// Run the app
	$app->run();


	/* 
	[HTTP POST] 
	APP/VERSION/CREATESTUDENT
	METHOD: POST
	PARAMETERS: name, username, password
	*/
	$app->post('/createstudent', function () use ($app)
	{
		verifyRequiredParams(array('name', 'username', 'password'));

		$response = array();

		$name = $app->request->post('name');
		$username = $app->request->post('username');
		$password = $app->request->post('password');

		$db = new DbOperation();

		// Call createstudent method
		$res = $db->createStudent($name, $username, $password);

		if ($res == 0) {
			//Making the response error false
        	$response["error"] = false;
	        //Adding a success message
	        $response["message"] = "You are successfully registered";
	        //Displaying response
	        echoResponse(201, $response);
		}
		else if ($res == 1) {
			$response["error"] = true;
        	$response["message"] = "Oops! An error occurred while registereing";
        	echoResponse(200, $response);
		} 
		else if ($res == 2) {
			$response["error"] = true;
        	$response["message"] = "Sorry, this email already existed";
        	echoResponse(200, $response);
		}
	})
?>