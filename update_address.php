<?php
session_start();

// Connect to SQL Server database
$serverName = "your_server_name";
$connectionInfo = array("Database"=>"food website", "UID"=>"your_username", "PWD"=>"your_password");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if(!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . print_r(sqlsrv_errors(), true)]));
}

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   echo json_encode(['error' => 'User not logged in']);
   exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);

    $address = $data['unit'].', '.$data['street'].', '.$data['subdiv'] .', '. $data['city'] .', '. $data['pin_code'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);

    $sql = "UPDATE users SET address = ? WHERE id = ?";
    $params = array($address, $user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt === false) {
        echo json_encode(['error' => 'Error updating address: ' . print_r(sqlsrv_errors(), true)]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Address saved!']);
    }
    exit;
}

// If it's a GET request, fetch the current address
$sql = "SELECT address FROM users WHERE id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt === false) {
    echo json_encode(['error' => 'Error fetching address: ' . print_r(sqlsrv_errors(), true)]);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    echo json_encode(['address' => $row['address']]);
}

