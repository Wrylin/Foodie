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

// Fetch user profile data
$select_profile = sqlsrv_query($conn, "SELECT * FROM users WHERE id = ?", array($user_id));
if($select_profile === false) {
    die(print_r(sqlsrv_errors(), true));
}

$fetch_profile = sqlsrv_fetch_array($select_profile, SQLSRV_FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'profile' => $fetch_profile
]);
?>