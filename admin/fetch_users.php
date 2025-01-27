<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
    die(json_encode(['error' => 'Not authorized']));
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    
    $delete_users = sqlsrv_query($conn, "DELETE FROM users WHERE id = ?", array($delete_id));
    $delete_order = sqlsrv_query($conn, "DELETE FROM orders WHERE user_id = ?", array($delete_id));
    $delete_cart = sqlsrv_query($conn, "DELETE FROM cart WHERE user_id = ?", array($delete_id));
    
    if($delete_users === false || $delete_order === false || $delete_cart === false) {
        die(json_encode(['error' => 'Delete operation failed']));
    }
    
    echo json_encode(['success' => 'User deleted successfully']);
    exit;
}

$select_account = sqlsrv_query($conn, "SELECT * FROM users");

if($select_account === false) {
    die(json_encode(['error' => 'Query failed']));
}

$accounts = array();
while($row = sqlsrv_fetch_array($select_account, SQLSRV_FETCH_ASSOC)) {
    $accounts[] = $row;
}

echo json_encode($accounts);
?>