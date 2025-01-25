<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!$admin_id){
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$response = [];

// Fetch admin profile
$sql = "SELECT name FROM admin WHERE id = ?";
$params = array($admin_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt === false) {
    $response['error'] = 'Error fetching admin profile: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['admin_name'] = $row['name'];
}

echo json_encode($response);

?>