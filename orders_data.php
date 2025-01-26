<?php
header('Content-Type: application/json');
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   echo json_encode(['error' => 'not_logged_in']);
   exit;
}

try {
    if($user_id == ''){
        echo json_encode(['status' => 'error', 'message' => 'please login to see your orders']);
    } else {
        $select_orders = sqlsrv_query($conn, 
            "SELECT * FROM orders WHERE user_id = ?", 
            array($user_id)
        );

        if($select_orders === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        $orders = array();
        while($fetch_orders = sqlsrv_fetch_array($select_orders, SQLSRV_FETCH_ASSOC)) {
            $orders[] = $fetch_orders;
        }

        if(count($orders) > 0){
            echo json_encode(['status' => 'success', 'orders' => $orders]);
        } else {
            echo json_encode(['status' => 'empty', 'message' => 'no orders placed yet!']);
        }
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

