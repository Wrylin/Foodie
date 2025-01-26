<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   echo json_encode(['error' => 'User not logged in']);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch cart items
    $sql = "SELECT c.id, c.pid, c.quantity, i.name, i.price, i.image 
            FROM cart c
            JOIN items i ON c.pid = i.id
            WHERE c.user_id = ?";
    $params = array($user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]);
        exit;
    }

    $cartItems = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $cartItems[] = $row;
    }

    echo json_encode($cartItems);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests (delete, update_qty, delete_all)
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'delete':
            $cart_id = $_POST['cart_id'] ?? '';
            $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $params = array($cart_id, $user_id);
            break;
        case 'update_qty':
            $cart_id = $_POST['cart_id'] ?? '';
            $qty = $_POST['qty'] ?? '';
            $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $params = array($qty, $cart_id, $user_id);
            break;
        case 'delete_all':
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $params = array($user_id);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        echo json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]);
    } else {
        echo json_encode(['success' => true]);
    }
}

sqlsrv_close($conn);
?>

