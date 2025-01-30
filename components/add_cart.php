<?php
include 'connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   echo json_encode(['error' => 'User not logged in']);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $qty = $_POST['qty'];

    // Check if item already exists in cart using pid and user_id
    $check_cart = "SELECT COUNT(*) as count FROM cart WHERE pid = ? AND user_id = ?";
    $params = array($pid, $user_id);
    $stmt = sqlsrv_query($conn, $check_cart, $params);

    if ($stmt === false) {
        echo json_encode(['error' => 'Database error: ' . print_r(sqlsrv_errors(), true)]);
        exit;
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if ($row['count'] > 0) {
        echo json_encode(['error' => 'Item already exists in cart!']);
        exit;
    }

    $insert_cart = "INSERT INTO cart (user_id, pid, name, price, quantity, image) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($user_id, $pid, $name, $price, $qty, $image);
    $stmt = sqlsrv_query($conn, $insert_cart, $params);

    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to add to cart: ' . print_r(sqlsrv_errors(), true)]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Item added to cart successfully!']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

sqlsrv_close($conn);
?>

