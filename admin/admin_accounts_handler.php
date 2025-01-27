<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
    die(json_encode(['error' => 'Not authorized']));
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    
    $delete_admin = sqlsrv_query($conn, "DELETE FROM [admin] WHERE id = ?", array($delete_id));
    
    if($delete_admin === false) {
        die(json_encode(['error' => 'Delete operation failed']));
    }
    
    echo json_encode(['success' => 'Admin deleted successfully']);
    exit;
}

$select_account = sqlsrv_query($conn, "SELECT * FROM [admin]");

if($select_account === false) {
    die(json_encode(['error' => 'Query failed']));
}

$accounts = array();
while($row = sqlsrv_fetch_array($select_account, SQLSRV_FETCH_ASSOC)) {
    $accounts[] = $row;
}

echo json_encode(['accounts' => $accounts, 'current_admin_id' => $admin_id]);
?>