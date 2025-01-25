<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   echo json_encode(['redirect' => 'home.html']);
   exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'delete':
            $cart_id = $_POST['cart_id'];
            $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
            $delete_cart_item->execute([$cart_id]);
            echo json_encode(['message' => 'cart item deleted!']);
            break;

        case 'delete_all':
            $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart_item->execute([$user_id]);
            echo json_encode(['message' => 'deleted all from cart!']);
            break;

        case 'update_qty':
            $cart_id = $_POST['cart_id'];
            $qty = filter_var($_POST['qty'], FILTER_SANITIZE_STRING);
            $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
            $update_qty->execute([$qty, $cart_id]);
            echo json_encode(['message' => 'cart quantity updated']);
            break;
    }
} else if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $select_cart->execute([$user_id]);
    $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cart_items);
}
?>
