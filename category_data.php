<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['category'])){
    $category = $_GET['category'];
    
    $sql = "SELECT * FROM items WHERE category = ?";
    $params = array($category);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
    }

    $items = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }

    echo json_encode($items);
} else {
    echo json_encode(['error' => 'No category specified']);
}

sqlsrv_close($conn);
?>