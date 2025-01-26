<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!$admin_id){
    echo "Error: Not logged in";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['pid'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Update item details
    $sql = "UPDATE items SET name = ?, category = ?, price = ? WHERE id = ?";
    $params = array($name, $category, $price, $pid);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo "Error: Failed to update item details" . print_r(sqlsrv_errors(), true);
        exit;
    }

    // Handle image update if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $old_image = $_POST['old_image'];
        $image = $_FILES['image']['name'];
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = '../uploaded_img/';

        // Create the upload directory if it doesn't exist
        if (!is_dir($image_folder)) {
            mkdir($image_folder, 0755, true);
        }

        $image_path = $image_folder . $image;

        if ($image_size > 2000000) {
            echo "Error: Image size is too large (max 2MB)";
            exit;
        }

        // Generate a unique filename to avoid overwriting
        $image_name = uniqid() . '_' . $image;
        $image_path = $image_folder . $image_name;

        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // Use CONVERT function to explicitly convert varchar to varbinary
            $sql = "UPDATE items SET image = CONVERT(varbinary(max), ?) WHERE id = ?";
            $params = array($image_name, $pid);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                $error = print_r(sqlsrv_errors(), true);
                echo "Error: Failed to update image in database. SQL Server Error: $error";
                unlink($image_path); // Remove the uploaded file
                exit;
            }

            if (!empty($old_image) && file_exists($image_folder . $old_image)) {
                unlink($image_folder . $old_image);
            }

            echo "Success: Item updated with new image";
        } else {
            echo "Error: Failed to upload new image. Details: " . error_get_last()['message'];
        }
    } else {
        echo "Success: Item updated";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $update_id = $_GET['update'] ?? null;

    if (!$update_id) {
        echo "Error: No item ID provided";
        exit;
    }

    $sql = "SELECT * FROM items WHERE id = ?";
    $params = array($update_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo "Error: Failed to fetch item details" . print_r(sqlsrv_errors(), true);
        exit;
    }

    $item = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$item) {
        echo "Error: Item not found";
        exit;
    }

    echo "ID: " . $item['id'] . "\n";
    echo "Name: " . $item['name'] . "\n";
    echo "Price: " . $item['price'] . "\n";
    echo "Category: " . $item['category'] . "\n";
    echo "Image: " . $item['image'] . "\n";
}

