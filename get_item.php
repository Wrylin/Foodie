<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['pid'])){
    $pid = $_GET['pid'];
    
    $sql = "SELECT * FROM items WHERE id = ?";
    $params = array($pid);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
    }

    $item = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($item) {
        echo json_encode($item);
    } else {
        echo json_encode(['error' => 'Item not found']);
    }
} else {
    echo json_encode(['error' => 'No item ID specified']);
}

sqlsrv_close($conn);
?>

