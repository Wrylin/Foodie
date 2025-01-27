<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
    die(json_encode(['error' => 'Admin not logged in']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_payment'])) {
        $order_id = $_POST['order_id'];
        $payment_status = $_POST['payment_status'];
        $sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
        $params = array($payment_status, $order_id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            die(json_encode(['error' => 'Update failed: ' . print_r(sqlsrv_errors(), true)]));
        }
        echo json_encode(['success' => 'Payment status updated!']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'fetch_orders') {
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        
        if ($status === 'all') {
            $sql = "SELECT * FROM orders";
            $params = array();
        } else {
            $sql = "SELECT * FROM orders WHERE payment_status = ?";
            $params = array($status);
        }
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
        }
        $orders = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $orders[] = $row;
        }
        echo json_encode($orders);
    } elseif (isset($_GET['delete'])) {
        $delete_id = $_GET['delete'];
        $sql = "DELETE FROM orders WHERE id = ?";
        $params = array($delete_id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            die(json_encode(['error' => 'Delete failed: ' . print_r(sqlsrv_errors(), true)]));
        }
        echo json_encode(['success' => 'Order deleted successfully']);
    }
}

sqlsrv_close($conn);
?>

