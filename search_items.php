<?php
include 'components/connect.php';

header('Content-Type: application/json');

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Check if search term is provided
if (isset($_POST['search_box'])) {
    $search_term = $_POST['search_box'];
    
    // Prepare the SQL query
    $sql = "SELECT id, name, price, image, category FROM items WHERE name LIKE ? OR category LIKE ?";
    $params = array("%$search_term%", "%$search_term%");
    
    // Execute the query
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(json_encode(array("status" => "error", "message" => "Query failed: " . print_r(sqlsrv_errors(), true))));
    }
    
    $results = array();
    
    // Fetch the results
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    
    // Return the results as JSON
    if (count($results) > 0) {
        echo json_encode(array("status" => "success", "data" => $results));
    } else {
        echo json_encode(array("status" => "success", "data" => array(), "message" => "No items found"));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "No search term provided"));
}

// Close the connection
sqlsrv_close($conn);
?>