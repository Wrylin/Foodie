<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   echo json_encode(['error' => 'User not logged in']);
   exit;
}

// update profile
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $number = $data['number'] ?? '';
    $old_pass = $data['old_pass'] ?? '';
    $new_pass = $data['new_pass'] ?? '';
    $confirm_pass = $data['confirm_pass'] ?? '';

    if(!empty($name)){
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $params = array($name, $user_id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if($stmt === false) {
            $response['message'] = 'Error updating name: ' . print_r(sqlsrv_errors(), true);
        }
    }

    if(!empty($email)){
        $sql = "SELECT * FROM users WHERE email = ?";
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if(sqlsrv_has_rows($stmt)){
            $response['message'] = 'Email already taken!';
        }else{
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $params = array($email, $user_id);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if($stmt === false) {
                $response['message'] = 'Error updating email: ' . print_r(sqlsrv_errors(), true);
            }
        }
    }

    if(!empty($number)){
        $sql = "SELECT * FROM users WHERE number = ?";
        $params = array($number);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if(sqlsrv_has_rows($stmt)){
            $response['message'] = 'Number already taken!';
        }else{
            $sql = "UPDATE users SET number = ? WHERE id = ?";
            $params = array($number, $user_id);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if($stmt === false) {
                $response['message'] = 'Error updating number: ' . print_r(sqlsrv_errors(), true);
            }
        }
    }

    if(!empty($old_pass) && !empty($new_pass) && !empty($confirm_pass)){
        $sql = "SELECT password FROM users WHERE id = ?";
        $params = array($user_id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if($stmt === false) {
            $response['message'] = 'Error fetching password: ' . print_r(sqlsrv_errors(), true);
        } else {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $prev_pass = $row['password'];
            
            if(sha1($old_pass) != $prev_pass){
                $response['message'] = 'Old password not matched!';
            }elseif($new_pass != $confirm_pass){
                $response['message'] = 'Confirm password not matched!';
            }else{
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $params = array(sha1($new_pass), $user_id);
                $stmt = sqlsrv_query($conn, $sql, $params);
                if($stmt === false) {
                    $response['message'] = 'Error updating password: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $response['message'] = 'Password updated successfully!';
                }
            }
        }
    }

    if(empty($response['message'])){
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    }

    echo json_encode($response);
    exit;
}

// Fetch user profile data
$sql = "SELECT name, email, number FROM users WHERE id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);
if($stmt === false) {
    echo json_encode(['error' => 'Error fetching user data: ' . print_r(sqlsrv_errors(), true)]);
    exit;
}

$user_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
echo json_encode($user_data);

