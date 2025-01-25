<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

try {
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'];

    if($action === 'login'){
        $email = htmlspecialchars($_POST['email']);
        $pass = htmlspecialchars(sha1($_POST['pass']));

        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $params = array($email, $pass);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(json_encode(['success' => false, 'message' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if($row){
            $_SESSION['user_id'] = $row['id'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        }else{
            echo json_encode(['success' => false, 'message' => 'Incorrect username or password!']);
        }
    }
    elseif($action === 'register'){
        $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = htmlspecialchars($_POST['email']);
        $number = filter_var($_POST['number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pass = htmlspecialchars(sha1($_POST['pass']));
        $cpass = htmlspecialchars(sha1($_POST['cpass']));

        $sql = "SELECT * FROM users WHERE email = ? OR number = ?";
        $params = array($email, $number);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(json_encode(['success' => false, 'message' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if($row){
            echo json_encode(['success' => false, 'message' => 'Email or number already exists!']);
        }else{
            if($pass != $cpass){
                echo json_encode(['success' => false, 'message' => 'Confirm password not matched!']);
            }else{
                $sql = "INSERT INTO users (name, email, number, password) VALUES (?, ?, ?, ?)";
                $params = array($name, $email, $number, $cpass);
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    die(json_encode(['success' => false, 'message' => 'Registration failed: ' . print_r(sqlsrv_errors(), true)]));
                }

                $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
                $params = array($email, $pass);
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    die(json_encode(['success' => false, 'message' => 'Query failed: ' . print_r(sqlsrv_errors(), true)]));
                }

                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                if($row){
                    $_SESSION['user_id'] = $row['id'];
                    echo json_encode(['success' => true, 'message' => 'Registration successful']);
                }
            }
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

sqlsrv_close($conn);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>

