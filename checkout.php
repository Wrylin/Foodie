<?php
header('Content-Type: application/json');
// Establish connection to SQL Server
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   echo json_encode(['error' => 'User not logged in']);
   exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
   $data = json_decode(file_get_contents('php://input'), true);

   // Use a custom function to sanitize input
   function sanitize_input($input) {
       if (is_null($input)) {
           return '';
       }
       return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
   }

   // Check if required fields are present
   $required_fields = ['name', 'number', 'email', 'method', 'address', 'total_items', 'total_price'];
   $missing_fields = [];

   foreach ($required_fields as $field) {
       if (!isset($data[$field]) || empty($data[$field])) {
           $missing_fields[] = $field;
       }
   }

   if (!empty($missing_fields)) {
       echo json_encode(['error' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
       exit;
   }

   $name = sanitize_input($data['name']);
   $number = sanitize_input($data['number']);
   $email = sanitize_input($data['email']);
   $method = sanitize_input($data['method']);
   $address = sanitize_input($data['address']);
   $total_items = sanitize_input($data['total_items']);
   $total_price = sanitize_input($data['total_price']);
   $payment_status = 'pending'; // Update 1: Set payment status to pending

   $check_cart = sqlsrv_query($conn, "SELECT * FROM cart WHERE user_id = ?", array($user_id));

   if($check_cart === false) {
       echo json_encode(['error' => 'Database query failed: ' . print_r(sqlsrv_errors(), true)]);
       exit;
   }

   if(sqlsrv_has_rows($check_cart)){
      if($address == ''){
         echo json_encode(['error' => 'Please add your address!']);
         exit;
      }else{
         $insert_order = sqlsrv_query($conn, "INSERT INTO orders (user_id, name, number, email, method, address, total_items, total_price, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 
            array($user_id, $name, $number, $email, $method, $address, $total_items, $total_price, $payment_status)); // Update 2: Include payment_status in INSERT query

         if($insert_order){
            $delete_cart = sqlsrv_query($conn, "DELETE FROM cart WHERE user_id = ?", array($user_id));
            if($delete_cart){
               echo json_encode(['success' => 'Order placed successfully!']);
               exit;
            } else {
               echo json_encode(['error' => 'Failed to clear cart: ' . print_r(sqlsrv_errors(), true)]);
               exit;
            }
         } else {
            echo json_encode(['error' => 'Failed to insert order: ' . print_r(sqlsrv_errors(), true)]);
            exit;
         }
      }
   }else{
      echo json_encode(['error' => 'Your cart is empty']);
      exit;
   }
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
   $select_cart = sqlsrv_query($conn, "SELECT * FROM cart WHERE user_id = ?", array($user_id));
   if($select_cart === false) {
       echo json_encode(['error' => 'Failed to fetch cart: ' . print_r(sqlsrv_errors(), true)]);
       exit;
   }
   $cart_items = array();
   $grand_total = 0;

   while($fetch_cart = sqlsrv_fetch_array($select_cart, SQLSRV_FETCH_ASSOC)){
      $cart_items[] = $fetch_cart;
      $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
   }

   $select_profile = sqlsrv_query($conn, "SELECT * FROM users WHERE id = ?", array($user_id));
   if($select_profile === false) {
       echo json_encode(['error' => 'Failed to fetch user profile: ' . print_r(sqlsrv_errors(), true)]);
       exit;
   }
   $fetch_profile = sqlsrv_fetch_array($select_profile, SQLSRV_FETCH_ASSOC);

   echo json_encode([
      'cart_items' => $cart_items,
      'grand_total' => $grand_total,
      'user_info' => $fetch_profile
   ]);
   exit;
}
echo json_encode(['error' => 'Unknown error occurred']);
?>

