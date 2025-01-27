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
$sql = "SELECT name FROM [admin] WHERE id = ?";
$params = array($admin_id);
$stmt = sqlsrv_query($conn, $sql, $params);
if($stmt === false) {
    $response['error'] = 'Error fetching admin profile: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['admin_name'] = $row['name'];
}

// Fetch total pendings
$sql = "SELECT SUM(total_price) AS total_pendings FROM orders WHERE payment_status = 'pending'";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching total pendings: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['total_pendings'] = $row['total_pendings'] ?? 0;
}

// Fetch total completes
$sql = "SELECT SUM(total_price) AS total_completes FROM orders WHERE payment_status = 'completed'";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching total completes: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['total_completes'] = $row['total_completes'] ?? 0;
}

// Fetch number of orders
$sql = "SELECT COUNT(*) AS numbers_of_orders FROM orders";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching number of orders: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['numbers_of_orders'] = $row['numbers_of_orders'];
}

// Fetch number of items
$sql = "SELECT COUNT(*) AS numbers_of_items FROM items";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching number of items: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['numbers_of_items'] = $row['numbers_of_items'];
}

// Fetch number of users
$sql = "SELECT COUNT(*) AS numbers_of_users FROM users";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching number of users: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['numbers_of_users'] = $row['numbers_of_users'];
}

// Fetch number of admins
$sql = "SELECT COUNT(*) AS numbers_of_admins FROM admin";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
    $response['error'] = 'Error fetching number of admins: ' . print_r(sqlsrv_errors(), true);
} else {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['numbers_of_admins'] = $row['numbers_of_admins'];
}

echo json_encode($response);

