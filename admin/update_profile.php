<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Handle POST request (form submission)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
    $name = htmlspecialchars($_POST['name']);
    $old_pass = sha1($_POST['old_pass']);
    $new_pass = sha1($_POST['new_pass']);
    $confirm_pass = sha1($_POST['confirm_pass']);

    $response = [];

    if(!empty($name)){
        $select_name = sqlsrv_query($conn, "SELECT * FROM [admin] WHERE name = ? AND id != ?", array($name, $admin_id));
        if($select_name && sqlsrv_has_rows($select_name)){
            $response['error'] = 'Username already taken!';
        }else{
            $update_name = sqlsrv_query($conn, "UPDATE [admin] SET name = ? WHERE id = ?", array($name, $admin_id));
            if($update_name){
                $response['message'] = 'Username updated successfully!';
            }else{
                $response['error'] = 'Failed to update username.';
            }
        }
    }

    if(!empty($_POST['old_pass']) || !empty($_POST['new_pass']) || !empty($_POST['confirm_pass'])){
        $select_old_pass = sqlsrv_query($conn, "SELECT password FROM [admin] WHERE id = ?", array($admin_id));
        $fetch_prev_pass = sqlsrv_fetch_array($select_old_pass, SQLSRV_FETCH_ASSOC);
        $prev_pass = $fetch_prev_pass['password'];

        if($old_pass != $prev_pass){
            $response['error'] = 'Old password not matched!';
        }elseif($new_pass != $confirm_pass){
            $response['error'] = 'Confirm password not matched!';
        }else{
            $update_pass = sqlsrv_query($conn, "UPDATE [admin] SET password = ? WHERE id = ?", array($confirm_pass, $admin_id));
            if($update_pass){
                $response['message'] = 'Password updated successfully!';
            }else{
                $response['error'] = 'Failed to update password.';
            }
        }
    }

    echo json_encode($response);
    exit;
}

// Handle GET request (fetching profile data)
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $select_profile = sqlsrv_query($conn, "SELECT * FROM [admin] WHERE id = ?", array($admin_id));
    if($select_profile){
        $fetch_profile = sqlsrv_fetch_array($select_profile, SQLSRV_FETCH_ASSOC);
        if($fetch_profile){
            echo json_encode(['name' => $fetch_profile['name']]);
        }else{
            echo json_encode(['error' => 'Failed to fetch profile data']);
        }
    }else{
        echo json_encode(['error' => 'Database query failed']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request method']);

