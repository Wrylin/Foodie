<?php
// Ensure that PHP errors don't get output to the response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set the content type to JSON
//header('Content-Type: application/json'); //Removed as the output format changed

// Start output buffering
ob_start();

require_once '../components/connect.php';

session_start();

try {
    $admin_id = $_SESSION['admin_id'] ?? null;

    if(!$admin_id){
        throw new Exception('Not logged in');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add_item':
                addItem($conn);
                break;
            default:
                throw new Exception('Invalid action');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_items':
                getItems($conn);
                break;
            case 'delete_item':
                deleteItem($conn);
                break;
            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    // Clean output buffer
    ob_clean();
    echo 'Error: ' . $e->getMessage(); //Updated error handling
}

// End output buffering and send the response
ob_end_flush();

function addItem($conn) {
    if (!isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['category'])) {
        throw new Exception('Missing required fields');
    }

    $name = htmlspecialchars($_POST['name']);
    $price = htmlspecialchars($_POST['price']);
    $category = htmlspecialchars($_POST['category']);

    if (!isset($_FILES['image'])) {
        throw new Exception('No image uploaded');
    }

    $image = htmlspecialchars($_FILES['image']['name']);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_img/' . $image;

    // Check for existing item
    $sql = "SELECT * FROM items WHERE name = ?";
    $params = array($name);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        throw new Exception('Database error: ' . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        throw new Exception('Item name already exists!');
    }

    // Handle image upload
    if ($image_size > 2000000) {
        throw new Exception('Image size is too large (max 2MB)');
    }

    if (!is_dir('../uploaded_img/')) {
        if (!mkdir('../uploaded_img/', 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    if (!move_uploaded_file($image_tmp_name, $image_folder)) {
        throw new Exception('Failed to upload image');
    }
    if (!file_exists($image_folder)) {
        throw new Exception('Image not found after upload');
    }

    // Update the SQL insert statement
    $sql = "INSERT INTO items (name, category, price, image) VALUES (?, ?, ?, CONVERT(varbinary(max),?))";
    $params = array($name, $category, $price, $image);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        // Clean up uploaded image if insert fails
        if (file_exists($image_folder)) {
            unlink($image_folder);
        }
        throw new Exception('Failed to add item: ' . print_r(sqlsrv_errors(), true));
    }

    echo 'Success: New item added successfully!';
}

function getItems($conn) {
    $sql = "SELECT * FROM items ORDER BY id DESC";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception('Failed to fetch items: ' . print_r(sqlsrv_errors(), true));
    }

    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }

    if (empty($items)) {
        echo 'No items found';
    } else {
        foreach ($items as $item) {
            echo "ID: {$item['id']}, Name: {$item['name']}, Category: {$item['category']}, Price: {$item['price']}, Image: {$item['image']}\n";
        }
    }
}

function deleteItem($conn) {
    if (!isset($_GET['delete'])) {
        throw new Exception('No item ID provided');
    }

    $delete_id = $_GET['delete'];

    // Get image filename before deleting
    $sql = "SELECT image FROM items WHERE id = ?";
    $params = array($delete_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        throw new Exception('Failed to fetch item: ' . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row) {
        $image_path = '../uploaded_img/'.$row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete item from database
    $sql = "DELETE FROM items WHERE id = ?";
    $params = array($delete_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        throw new Exception('Failed to delete item: ' . print_r(sqlsrv_errors(), true));
    }

    // Clean up cart entries
    $sql = "DELETE FROM cart WHERE pid = ?";
    $params = array($delete_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        throw new Exception('Failed to delete item from cart: ' . print_r(sqlsrv_errors(), true));
    }

    echo 'Success: Item deleted successfully'; //Updated success message
}

?>