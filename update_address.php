<?php
include 'components/connect.php';

session_start();

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

    // Validate all required fields are present
    $required_fields = ['unit', 'street', 'subdiv', 'city', 'prov', 'pin_code'];
    foreach($required_fields as $field) {
        if(!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }
    }

    $address = $data['unit'].', '.$data['street'].', '.$data['subdiv'] .', '. 
               $data['city'] .', '. $data['prov'] .', '. $data['pin_code'];
    
    $sql = "UPDATE users SET address = ? WHERE id = ?";
    $params = array($address, $user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt === false) {
        echo json_encode(['error' => 'Error updating address: ' . print_r(sqlsrv_errors(), true)]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Address updated successfully!']);
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
    echo json_encode(['success' => true, 'address' => $row['address']]);
}
?>