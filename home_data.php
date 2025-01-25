<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Function to get cart items count
function getCartItemsCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS count FROM cart WHERE user_id = ?";
    $params = array($user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
    }
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row['count'];
}

// Function to get user profile
function getUserProfile($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $params = array($user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
    }
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

// Function to get latest items
function getLatestItems($conn) {
    $sql = "SELECT TOP 6 * FROM items ORDER BY id DESC";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(json_encode(['error' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
    }
    $items = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'cart_count':
        echo json_encode(['count' => getCartItemsCount($conn, $user_id)]);
        break;
    case 'user_profile':
        echo json_encode(getUserProfile($conn, $user_id));
        break;
    case 'latest_items':
        echo json_encode(getLatestItems($conn));
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

sqlsrv_close($conn);
?>

