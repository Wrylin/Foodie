<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;
$response = array();

// Get cart items count
$sql = "SELECT COUNT(*) AS total_cart_items FROM cart WHERE user_id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);
if($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$response['total_cart_items'] = $row['total_cart_items'];

// Get user profile
if($user_id) {
    $sql = "SELECT name FROM users WHERE id = ?";
    $params = array($user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $response['user_name'] = $row['name'] ?? null;
}

echo json_encode($response);
?>

