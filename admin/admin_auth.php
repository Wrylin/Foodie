<?php
include '../components/connect.php';

session_start();

$response = ['success' => false, 'message' => ''];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch($action) {
        case 'login':
            handleLogin($conn, $data);
            break;
        case 'register':
            handleRegister($conn, $data);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
    }
    exit;
}

function handleLogin($conn, $data) {
    $name = filter_var($data['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = htmlspecialchars(sha1($data['pass'])); // Hash the password

    // Debugging output
    error_log("Attempting login with name: $name and hashed password: $pass");

    $sql = "SELECT * FROM admin WHERE name = ? AND password = ?";
    $params = array($name, $pass);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $response['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
    } else {
        if (sqlsrv_has_rows($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $_SESSION['admin_id'] = $row['id'];
            $response['success'] = true;
            $response['message'] = 'Login successful';
        } else {
            $response['message'] = 'Incorrect username or password!';
        }
    }

    echo json_encode($response);
}

function handleRegister($conn, $data) {
    if(!isset($_SESSION['admin_id'])){
        echo json_encode(['error' => 'Not logged in']);
        return;
    }

    $name = filter_var($data['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = htmlspecialchars(sha1($data['pass']));
    $cpass = htmlspecialchars(sha1($data['cpass']));

    $sql = "SELECT * FROM admin WHERE name = ?";
    $params = array($name);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt === false) {
        $response['message'] = 'Query failed: ' . print_r(sqlsrv_errors(), true);
    } else {
        if(sqlsrv_has_rows($stmt)) {
            $response['message'] = 'Username already exists!';
        } else {
            if($pass != $cpass){
                $response['message'] = 'Confirm password not matched!';
            } else {
                $sql = "INSERT INTO admin (name, password) VALUES (?, ?)";
                $params = array($name, $cpass);
                $stmt = sqlsrv_query($conn, $sql, $params);
                
                if($stmt === false) {
                    $response['message'] = 'Insert failed: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $response['success'] = true;
                    $response['message'] = 'New admin registered!';
                }
            }
        }
    }

    echo json_encode($response);
}
?>