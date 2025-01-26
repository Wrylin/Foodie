<?php

include 'components/connect.php';

session_start();


header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $pid = $_POST['pid'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
        $qty = $_POST['qty'];

        $check_cart_numbers = sqlsrv_query($conn, "SELECT * FROM cart WHERE name = ? AND user_id = ?", array($name, $user_id));

        if (sqlsrv_has_rows($check_cart_numbers)) {
            $message[] = 'Already added to cart!';
        } else {
            $insert_cart = sqlsrv_query($conn, "INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)", 
                array($user_id, $pid, $name, $price, $qty, $image));
            $message[] = 'Added to cart!';
        }

        echo json_encode(['message' => $message]);
        exit;
    }
}

$select_items = sqlsrv_query($conn, "SELECT * FROM items");

if ($select_items === false) {
    die(print_r(sqlsrv_errors(), true));
}

$items = array();
while ($row = sqlsrv_fetch_array($select_items, SQLSRV_FETCH_ASSOC)) {
    $items[] = $row;
}

echo json_encode($items);

?>